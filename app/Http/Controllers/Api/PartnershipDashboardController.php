<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PartnershipDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class PartnershipDashboardController extends Controller
{
    protected PartnershipDashboardService $dashboardService;

    public function __construct(PartnershipDashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get all KPIs at once
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllKpis(Request $request): JsonResponse
    {
        try {
            $options = [
                'year' => (int) $request->query('year', now()->year),
            ];

            $data = $this->dashboardService->getAllKpis($options);

            return response()->json([
                'success' => true,
                'data' => $data,
                'year' => $options['year'],
            ]);
        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération de tous les KPIs partenariat : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des KPIs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of available KPIs
     * @return JsonResponse
     */
    public function getAvailableKpis(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'kpis' => $this->dashboardService->getAvailableKpis(),
            'usage' => [
                'all' => 'GET /api/partnership-dashboard/all?year=2026',
                'individual' => 'GET /api/partnership-dashboard/{kpi-name}',
            ],
        ]);
    }

    /**
     * 1. Nombre total de projets actifs
     */
    public function getActiveProjects(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getActiveProjectsCount($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI projets actifs : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des projets actifs.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 2. Taux de conventions en cours
     */
    public function getConventionRate(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getConventionRate($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI taux conventions : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération du taux de conventions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3. Montant total des subventions engagées
     */
    public function getTotalGrants(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getTotalCommittedGrants($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI subventions : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des subventions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 4. Taux de réception des subventions
     */
    public function getGrantReceptionRate(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getGrantReceptionRate($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI taux réception : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération du taux de réception.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 5. Nombre de partenaires par type
     */
    public function getPartnersByType(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getPartnersByType($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI partenaires par type : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des partenaires par type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 6. Projets par zone géographique
     */
    public function getProjectsByZone(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getProjectsByZone($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI projets par zone : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des projets par zone.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 7. Projets par phase (cycle de vie)
     */
    public function getProjectsByPhase(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getProjectsByPhase($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI projets par phase : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des projets par phase.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 8. Délai moyen de signature des conventions
     */
    public function getAverageSigningDelay(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getAverageSigningDelay($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI délai signature : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération du délai moyen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 9. Taux de retard des tâches
     */
    public function getTaskDelayRate(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getTaskDelayRate($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI taux retard tâches : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération du taux de retard.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 10. Nombre d'évaluations réalisées
     */
    public function getCompletedEvaluations(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getCompletedEvaluationsCount($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI évaluations : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des évaluations.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 11. Score moyen des partenaires évalués
     */
    public function getAveragePartnerScore(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getAveragePartnerScore($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI score partenaires : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération du score moyen.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 12. Taux de contacts désabonnés
     */
    public function getUnsubscribeRate(Request $request): JsonResponse
    {
        try {
            $options = ['year' => (int) $request->query('year', now()->year)];
            $data = $this->dashboardService->getUnsubscribeRate($options);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            Log::error("Erreur KPI désabonnements : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération du taux de désabonnement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
