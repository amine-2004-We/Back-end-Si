<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePayrollRequest;
use App\Http\Requests\UpdatePayrollRequest;
use App\Models\Collaborator;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

/**
 * class PayrollController
 */
class PayrollController extends Controller
{
    /**
     * GET /api/payrolls
     * Optional query params:
     *  - page, perPage
     *  - search (matches payroll_code)
     *  - collaborator_id
     *  - status
     *  - period (YYYY-MM or YYYY-MM-01 etc.; month-precision friendly)
     *  - withTrashed=1 (include deleted), onlyTrashed=1
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->integer('perPage', 10);

        $query = Payroll::query()
            ->with(['collaborator']);


        if($request->boolean('is_active') == true ){
            $query->withoutTrashed();
        }else{
            $query->onlyTrashed();
        }

        // Free text (payroll_code)
        if ($search = $request->string('search')->toString()) {
            $query->where('payroll_code', 'like', "%{$search}%");
        }

        // Filter: collaborator
        if ($collabId = $request->integer('collaborator_id')) {
            $query->where('collaborator_id', $collabId);
        }

        // Filter: status
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        // Filter: period (accepts 'YYYY-MM' or full date)
        if ($period = $request->string('period')->toString()) {
            // Normalize to first day of month if only YYYY-MM provided
            if (preg_match('/^\d{4}-\d{2}$/', $period)) {
                $query->whereDate('period', '>=', "{$period}-01")
                      ->whereDate('period', '<', date('Y-m-01', strtotime("{$period}-01 +1 month")));
            } else {
                // Compare by date (exact)
                $query->whereDate('period', date('Y-m-d', strtotime($period)));
            }
        }

        $query->orderByDesc('period')->orderByDesc('id');

        $paginated = $query->paginate($perPage)->appends($request->query());

        return response()->json($paginated);
    }

    /**
     * GET /api/payrolls/{payroll}
     */
    public function show(Payroll $payroll)
    {
        $payroll->load(['collaborator']);

        return response()->json([
            'payroll' => $payroll,
        ]);
    }

    /**
     * POST /api/payrolls
     */
    public function store(StorePayrollRequest $request)
    {
        $data = $request->validated();

        $payroll = Payroll::create($data); // Observer will generate payroll_code
        
        // Auto-populate HR data if requested
        if ($request->boolean('populate_from_hr', false)) {
            $payroll->populateFromHrData();
            $payroll->save();
        }
        
        $payroll->load(['collaborator']);

        return response()->json([
            'message' => 'Payroll created successfully.',
            'payroll' => $payroll,
        ], Response::HTTP_CREATED);
    }

    /**
     * GET /api/payrolls/{payroll}/hr-data
     * Get HR data for a payroll (absences from PresenceSheet, leaves, contract dates)
     */
    public function getHrData(Payroll $payroll)
    {
        $payroll->load(['collaborator']);

        return response()->json([
            'hr_data' => [
                // Contract dates from collaborator
                'contract_start_date' => $payroll->contract_start_date_from_hr?->format('Y-m-d'),
                'contract_end_date' => $payroll->contract_end_date_from_hr?->format('Y-m-d'),
                'exit_date' => $payroll->exit_date_from_hr?->format('Y-m-d'),
                
                // Absences from PresenceSheet table
                'unjustified_absences_days' => $payroll->unjustified_absences_days_from_hr,
                'justified_absences_days' => $payroll->justified_absences_days_from_hr,
                
                // Leave data
                'maternity_days' => $payroll->maternity_days_from_hr,
                'leaves_summary' => $payroll->leaves_summary,
                
                // Raw data for reference
                'presences' => $payroll->getCollaboratorPresencesForPeriod(),
                'leaves' => $payroll->getLeavesForPeriod(),
            ],
        ]);
    }

    /**
     * POST /api/payrolls/hr-data-preview
     * Preview HR data before creating a payroll (no payroll ID needed)
     * Body: { "collaborator_id": 1, "period": "2026-01-01" }
     */
    public function getHrDataPreview(Request $request)
    {
        $request->validate([
            'collaborator_id' => 'required|exists:collaborators,id',
            'period' => 'required|date',
        ]);

        // Create a temporary payroll instance (not saved) to use the HR data methods
        $payroll = new Payroll([
            'collaborator_id' => $request->collaborator_id,
            'period' => $request->period,
        ]);

        // Load the collaborator relationship
        $payroll->setRelation('collaborator', Collaborator::find($request->collaborator_id));

        return response()->json([
            'hr_data' => [
                // Contract dates from collaborator
                'contract_start_date' => $payroll->contract_start_date_from_hr?->format('Y-m-d'),
                'contract_end_date' => $payroll->contract_end_date_from_hr?->format('Y-m-d'),
                'exit_date' => $payroll->exit_date_from_hr?->format('Y-m-d'),
                
                // Absences from PresenceSheet table
                'unjustified_absences_days' => $payroll->unjustified_absences_days_from_hr,
                'justified_absences_days' => $payroll->justified_absences_days_from_hr,
                
                // Leave data
                'maternity_days' => $payroll->maternity_days_from_hr,
                'leaves_summary' => $payroll->leaves_summary,
                
                // Raw data for reference
                'presences' => $payroll->getCollaboratorPresencesForPeriod(),
                'leaves' => $payroll->getLeavesForPeriod(),
            ],
        ]);
    }

    /**
     * POST /api/payrolls/{payroll}/populate-hr
     * Auto-populate payroll with HR data
     */
    public function populateFromHr(Payroll $payroll)
    {
        $payroll->populateFromHrData();
        $payroll->save();
        $payroll->load(['collaborator']);

        return response()->json([
            'message' => 'Payroll populated with HR data successfully.',
            'payroll' => $payroll,
        ]);
    }

    /**
     * PUT/PATCH /api/payrolls/{payroll}
     */
    public function update(UpdatePayrollRequest $request, Payroll $payroll)
    {
        $data = $request->validated();

        // Avoid changing payroll_code unless explicitly allowed in UpdateRequest (we excluded it there)
        $payroll->update($data);
        $payroll->load(['collaborator']);

        return response()->json([
            'message' => 'Payroll updated successfully.',
            'payroll' => $payroll,
        ]);
    }

    /**
     * DELETE /api/payrolls/{payroll}
     * Soft delete.
     */
    public function destroy(Payroll $payroll)
    {
        $payroll->delete();

        return response()->json([
            'message' => 'Payroll deleted successfully.',
        ]);
    }

    /**
     * POST /api/payrolls/{id}/restore
     */
    public function restore($id)
    {
        $payroll = Payroll::onlyTrashed()->findOrFail($id);
        $payroll->restore();

        $payroll->load(['collaborator']);

        return response()->json([
            'message' => 'Payroll restored successfully.',
            'payroll' => $payroll,
        ]);
    }

    /**
     * POST /api/payrolls/bulk-delete
     * body: { "ids": [1,2,3] }
     */
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'message' => 'No IDs provided.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Payroll::query()->whereIn('id', $ids)->delete();

        return response()->json([
            'message' => 'Payrolls deleted successfully.',
        ]);
    }

    public function options()
    {
        try{
            $collaborators = Collaborator::withoutTrashed()->select('id','collaborator_code','net_salary','first_name','last_name')->get();
            return response()->json([
                'collaborators' => $collaborators
            ]);
        }catch(Exception $e){
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
