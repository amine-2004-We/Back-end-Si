<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\KpiChartService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class KpiChartController extends Controller
{
    protected $kpiChartService;

    public function __construct(KpiChartService $kpiChartService)
    {
        $this->kpiChartService = $kpiChartService;
    }

    /**
     * Get all KPI charts
     * @param Request $request
     * @return JsonResponse
     */
    public function getAllKpis(Request $request): JsonResponse
    {
        try {
            $year = $request->query('year', now()->year);
            $startDate = $request->query('startDate')
                ? Carbon::parse($request->query('startDate'))
                : Carbon::createFromDate($year, 1, 1);
            $endDate = $request->query('endDate')
                ? Carbon::parse($request->query('endDate'))
                : Carbon::createFromDate($year, 12, 31)->endOfDay();
            $siteId = $request->query('siteId');
            $chartLibrary = $request->query('format', 'raw');

            $options = [
                'year' => $year,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'siteId' => $siteId,
            ];

            $charts = $this->kpiChartService->getAllKpiCharts($options);

            // Format charts if requested
            if ($chartLibrary !== 'raw') {
                $formattedCharts = [];
                foreach ($charts as $key => $chart) {
                    $formattedCharts[$key] = $this->kpiChartService->formatChartForFrontend($chart, $chartLibrary);
                }
                $charts = $formattedCharts;
            }

            return response()->json([
                'success' => true,
                'data' => $charts,
                'filters' => [
                    'year' => $year,
                    'startDate' => $startDate->toDateString(),
                    'endDate' => $endDate->toDateString(),
                    'siteId' => $siteId,
                    'format' => $chartLibrary,
                ],
            ]);

        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération des KPI charts : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération des KPI charts.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific KPI chart
     * @param Request $request
     * @param string $kpi KPI identifier
     * @return JsonResponse
     */
    public function getKpiChart(Request $request, string $kpi): JsonResponse
    {
        try {
            $year = $request->query('year', now()->year);
            $startDate = $request->query('startDate')
                ? Carbon::parse($request->query('startDate'))
                : Carbon::createFromDate($year, 1, 1);
            $endDate = $request->query('endDate')
                ? Carbon::parse($request->query('endDate'))
                : Carbon::createFromDate($year, 12, 31)->endOfDay();
            $siteId = $request->query('siteId');
            $chartLibrary = $request->query('format', 'raw');

            $options = [
                'year' => $year,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'siteId' => $siteId,
            ];

            $methodName = 'get' . str_replace('_', '', ucwords($kpi, '_')) . 'Chart';

            if (!method_exists($this->kpiChartService, $methodName)) {
                return response()->json([
                    'success' => false,
                    'message' => "KPI '{$kpi}' not found.",
                    'available_kpis' => $this->getAvailableKpis(),
                ], 404);
            }

            $chart = $this->kpiChartService->$methodName($startDate, $endDate, $siteId);

            // Format if requested
            if ($chartLibrary !== 'raw') {
                $chart = $this->kpiChartService->formatChartForFrontend($chart, $chartLibrary);
            }

            return response()->json([
                'success' => true,
                'data' => $chart,
                'kpi' => $kpi,
                'filters' => [
                    'year' => $year,
                    'startDate' => $startDate->toDateString(),
                    'endDate' => $endDate->toDateString(),
                    'siteId' => $siteId,
                    'format' => $chartLibrary,
                ],
            ]);

        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération du KPI chart ({$kpi}) : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Échec de la récupération du KPI '{$kpi}'.",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get available KPIs list
     * @return JsonResponse
     */
    public function getAvailableKpis(): JsonResponse
    {
        $kpis = [
            'attendance_rate' => 'Taux de présence des bénéficiaires',
            'unjustified_absence_rate' => 'Taux d\'absentéisme non justifié',
            'average_sessions_per_group' => 'Nombre moyen de séances par groupe',
            'average_supervision_visits' => 'Nombre moyen de visites d\'accompagnement par site',
            'evaluation_completion_rate' => 'Taux des évaluations réalisé',
            'average_data_entry_delay' => 'Délai moyen de saisie des présences',
            'parent_participation_rate' => 'Taux de participation aux réunions de parents',
            'group_occupancy_rate' => 'Taux d\'occupation des groupes',
            'cr_data_entry_within_48h_rate' => 'Taux de CR saisies dans les 48h après l\'événement',
            'beneficiaries_evaluated_according_to_cycle' => 'Taux de bénéficiaires évalués selon le cycle prévu',
        ];

        return response()->json([
            'success' => true,
            'available_kpis' => $kpis,
            'usage' => [
                'get_all' => 'GET /api/kpi-charts?year=2025&format=raw',
                'get_single' => 'GET /api/kpi-charts/{kpi}?year=2025&siteId=1&format=raw',
                'with_date_range' => 'GET /api/kpi-charts?startDate=2025-01-01&endDate=2025-03-31&format=chart.js',
            ],
            'supported_formats' => ['raw', 'chart.js', 'apex', 'echarts'],
            'filters' => [
                'year' => 'Filter by year (default: current year)',
                'startDate' => 'Start date in YYYY-MM-DD format',
                'endDate' => 'End date in YYYY-MM-DD format',
                'siteId' => 'Filter by site ID',
                'format' => 'Output format: raw, chart.js, apex, or echarts',
            ],
        ]);
    }

    /**
     * Get KPI statistics summary
     * @param Request $request
     * @return JsonResponse
     */
    public function getKpiSummary(Request $request): JsonResponse
    {
        try {
            $year = $request->query('year', now()->year);
            $startDate = $request->query('startDate')
                ? Carbon::parse($request->query('startDate'))
                : Carbon::createFromDate($year, 1, 1);
            $endDate = $request->query('endDate')
                ? Carbon::parse($request->query('endDate'))
                : Carbon::createFromDate($year, 12, 31)->endOfDay();
            $siteId = $request->query('siteId');

            $options = [
                'year' => $year,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'siteId' => $siteId,
            ];

            $charts = $this->kpiChartService->getAllKpiCharts($options);

            // Extract summary metrics
            $summary = [];
            foreach ($charts as $key => $chart) {
                $summary[$key] = [
                    'kpi' => $chart['kpi'] ?? '',
                    'type' => $chart['type'] ?? '',
                    'current_value' => $chart['current_value'] ?? null,
                    'objective' => $chart['objective'] ?? null,
                    'status' => $chart['status'] ?? 'unknown',
                    'unit' => $chart['unit'] ?? '',
                ];
            }

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'filters' => [
                    'year' => $year,
                    'startDate' => $startDate->toDateString(),
                    'endDate' => $endDate->toDateString(),
                    'siteId' => $siteId,
                ],
            ]);

        } catch (Exception $e) {
            Log::error("Erreur lors de la récupération du résumé KPI : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Échec de la récupération du résumé KPI.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
