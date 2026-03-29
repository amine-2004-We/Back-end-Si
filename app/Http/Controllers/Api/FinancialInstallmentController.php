<?php

namespace App\Http\Controllers\Api;

use App\Constants\Role;
use App\Enums\InstallmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\FinancialInstallmentRequest;
use App\Http\Resources\FinancialInstallmentResource;
use App\Models\Collaborator;
use App\Models\FinancialInstallment;
use App\Services\FinancialInstallmentService;
use App\Services\Notification\MailService;
use App\Services\NotificationService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FinancialInstallmentController extends Controller
{
    protected FinancialInstallmentService $service;

    public function __construct(FinancialInstallmentService $service)
    {
        $this->service = $service;
    }

    /**
     * Return options for FinancialInstallment form fields (enums, select lists, etc.)
     */
    public function options()
    {
        return response()->json([
            'conventions' => \App\Models\Convention::all(['id', 'title']),
            'status' => InstallmentStatus::cases(),
            'devise' => \App\Enums\CurrencyEnum::cases(),
            'reception_mode' => [
                ['value' => 'cash', 'label' => 'Cash'],
                ['value' => 'bank_transfer', 'label' => 'Bank Transfer'],
                ['value' => 'cheque', 'label' => 'Cheque'],
            ],
        ]);
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', FinancialInstallment::class);
        $data = $this->service->list($request->all());
        return FinancialInstallmentResource::collection($data);
    }

    public function store(FinancialInstallmentRequest $request)
    {
        $this->authorize('create', FinancialInstallment::class);
        $installment = $this->service->create($request->validated());
        return new FinancialInstallmentResource($installment);
    }

    /**
     * Display the specified resource.
     * Implicit model binding injects the model instance.
     */
    public function show(FinancialInstallment $financialInstallment)
    {
        $this->authorize('view', $financialInstallment);
        $installment = $this->service->find($financialInstallment->id);
        return new FinancialInstallmentResource($installment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FinancialInstallmentRequest $request, FinancialInstallment $financialInstallment)
    {
        $this->authorize('update', $financialInstallment);

        $installment = $this->service->update($financialInstallment->id, $request->validated());

        return new FinancialInstallmentResource($installment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FinancialInstallment $financialInstallment)
    {
        $this->authorize('delete', $financialInstallment);

        // Pass the ID to the service
        $this->service->delete($financialInstallment->id);

        return response()->json(null, 204);
    }

    /**
     * Validate an installment and manage status transitions
     */
    public function validateInstallment(Request $request, FinancialInstallment $financialInstallment): JsonResponse
    {
        try {
            $this->authorize('validate', $financialInstallment);

            $user = $request->user();
            $previousStatus = $financialInstallment->status;

             $financialInstallment->load('convention.responsible', 'convention.project');

            switch ($financialInstallment->status) {
                case InstallmentStatus::Planned:
                    if (!$user->hasRole(Role::DIRECTION_GENERALE)) {
                        return response()->json([
                            'error' => 'Only Direction Générale (DG) can validate this installment at this stage.'
                        ], Response::HTTP_FORBIDDEN);
                    }

                    $financialInstallment->status = InstallmentStatus::Pending;
                    $financialInstallment->save();
                    $this->sendStatusChangeNotifications($financialInstallment, $previousStatus, $user);
                    break;

                case InstallmentStatus::Pending:
                     if (!$user->hasAnyRole([Role::CHEF_DE_PROJET, Role::FINANCE, Role::DIRECTION_FINANCIERE])) {
                        return response()->json([
                            'error' => 'Only Responsable Projet or Finance can record the reception at this stage.'
                        ], Response::HTTP_FORBIDDEN);
                    }

                    $financialInstallment->status = InstallmentStatus::Received;
                    $financialInstallment->save();

                    //  $this->sendStatusChangeNotifications($financialInstallment, $previousStatus, $user);
                    break;

                default:
                    return response()->json([
                        'error' => 'This installment cannot be validated in its current status.'
                    ], Response::HTTP_FORBIDDEN);
            }

            return response()->json([
                'message' => "Installment status changed from {$previousStatus->value} to {$financialInstallment->status->value}",
                'installment' => new FinancialInstallmentResource($financialInstallment->fresh('convention', 'receptions'))
            ]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Server error: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send notifications to relevant stakeholders when status changes
     */
    private function sendStatusChangeNotifications(
        FinancialInstallment $installment,
        InstallmentStatus $previousStatus,
        $validator
    ): void {
        $convention = $installment->convention;

         $recipientIds = [];

         if ($convention->responsible) {
            $recipientIds[] = $convention->responsible->id;
        }

         $dgCollaborators = Collaborator::whereHas('position', function ($query) {
            $query->where('title', Role::DIRECTION_GENERALE);
        })->pluck('id')->toArray();
        $recipientIds = array_merge($recipientIds, $dgCollaborators);

         $partenariatCollaborators = Collaborator::whereHas('position', function ($query) {
            $query->whereIn('title', [
                Role::RESPONSABLE_PARTENARIAT,
                Role::RESPONSABLE_PARTENARIAT_ET_DEVELOPPEMENT,
                Role::CHARGE_DE_PARTENARIAT,
            ]);
        })->pluck('id')->toArray();
        $recipientIds = array_merge($recipientIds, $partenariatCollaborators);

         $dafCollaborators = Collaborator::whereHas('position', function ($query) {
            $query->where('title', Role::DAF);
        })->pluck('id')->toArray();
        $recipientIds = array_merge($recipientIds, $dafCollaborators);

         $recipientIds = array_unique($recipientIds);

         $senderId = auth()->user()->collaborator?->id ?? $convention->responsible?->id ?? 1;

        NotificationService::save(
            "Financial Installment Status Updated",
            "Installment #{$installment->installment_number} status changed from {$previousStatus->value} to {$installment->status->value}",
            $senderId,
            [
                'type' => 'open_modal',
                'name' => 'view_financial_installment',
                'id' => $installment->id
            ],
            $recipientIds
        );

         $emails = [];
        foreach ($recipientIds as $recipientId) {
            $collaborator = Collaborator::find($recipientId);
            if ($collaborator && $collaborator->email) {
                $emails[] = $collaborator->email;
            }
        }

        if (!empty($emails)) {
            MailService::sendMail(
                $emails,
                "Financial Installment Status Updated",
                'emails.training_alert',
                [
                    'item' => $installment,
                    'validator' => $validator,
                    'previousStatus' => $previousStatus->value,
                    'newStatus' => $installment->status->value,
                ]
            );
        }
    }
    /**
     * Download the proof document for an installment.
     */
    public function downloadDocument(int $id)
    {
        $installment = FinancialInstallment::findOrFail($id);
        $this->authorize('view', $installment);

        try {
            $fileInfo = $this->service->downloadDocument($id);
            return Storage::disk('private')->download($fileInfo['path'], $fileInfo['name']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }
}
