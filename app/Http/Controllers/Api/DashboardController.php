<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get dashboard data for a specific model type
     * @param Request $request
     * @param string $type Model type (convention, collaborator, site, project, financial)
     * @return JsonResponse
     */
    public function getDashboard(Request $request, string $type): JsonResponse
    {
        try {
            // Authorize for models that have policies
            $modelClassMap = [
                'convention' => 'Convention',
                'collaborator' => 'Collaborator',
                'site' => 'Site',
                'project' => 'Project',
            ];

            if (isset($modelClassMap[$type])) {
                $this->authorize('viewAny', 'App\\Models\\' . $modelClassMap[$type]);
            }

            $year = $request->query('year', now()->year);
            $options = [
                'year' => (int) $year,
            ];

            $data = $this->dashboardService->getDashboardData($type, $options);

            if (isset($data['error'])) {
                return response()->json($data, 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);

        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération du tableau de bord ({$type}) : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Échec de la récupération du tableau de bord pour {$type}.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get multiple dashboards at once
     * @param Request $request
     * @return JsonResponse
     */
    public function getMultipleDashboards(Request $request): JsonResponse
    {
        try {
            $types = $request->query('types');

            if (!$types) {
                return response()->json([
                    'success' => false,
                    'message' => 'Query parameter "types" is required. Example: ?types=convention,collaborator,site',
                ], 400);
            }

            // Parse comma-separated types
            $typeArray = array_filter(array_map('trim', explode(',', $types)));

            if (empty($typeArray)) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one dashboard type is required.',
                ], 400);
            }

            $year = $request->query('year', now()->year);
            $options = ['year' => (int) $year];

            $dashboards = $this->dashboardService->getMultipleDashboards($typeArray, $options);

            return response()->json([
                'success' => true,
                'data' => $dashboards,
                'types_requested' => $typeArray,
            ]);

        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération de plusieurs tableaux de bord : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des tableaux de bord.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available dashboard types
     * @return JsonResponse
     */
    public function getAvailableTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'available_types' => [
                'convention' => 'Convention metrics (rate, subventions, projects)',
                'collaborator' => 'Collaborator metrics (by department, status, growth)',
                'site' => 'Site metrics (by region, type, beneficiaries)',
                'project' => 'Project metrics (by status, timeline, budget)',
                'financial' => 'Financial metrics (by type, monthly flow, total)',
            ],
            'usage' => [
                'single' => 'GET /api/dashboard/{type}?year=2025',
                'multiple' => 'GET /api/dashboard/multiple?types=convention,collaborator,site&year=2025',
            ],
        ]);
    }
}
