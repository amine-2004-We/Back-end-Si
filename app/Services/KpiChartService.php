<?php

namespace App\Services;

use App\Models\Presence;
use App\Models\Group;
use App\Models\TrainingSession;
use App\Models\Evaluation;
use App\Models\EvaluationOperation;
use App\Models\Site;
use App\Models\Beneficiary;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class KpiChartService
{
    /**
     * Get all KPI chart data
     * @param array $options Configuration options (year, startDate, endDate, siteId, etc.)
     * @return array
     */
    public function getAllKpiCharts(array $options = []): array
    {
        // Use provided dates or default to current year
        if (isset($options['startDate']) && isset($options['endDate'])) {
            $startDate = $options['startDate'];
            $endDate = $options['endDate'];
        } else {
            // Default: last 12 months from today
            $endDate = now()->endOfDay();
            $startDate = now()->subMonths(12)->startOfDay();
        }

        return [
            'attendance_rate' => $this->getAttendanceRateChart($startDate, $endDate),
            'unjustified_absence_rate' => $this->getUnjustifiedAbsenceRateChart($startDate, $endDate),
            'average_sessions_per_group' => $this->getAverageSessionsPerGroupChart($startDate, $endDate),
            'average_supervision_visits' => $this->getAverageSupervisonVisitsChart($startDate, $endDate),
            'evaluation_completion_rate' => $this->getEvaluationCompletionRateChart($startDate, $endDate),
            'average_data_entry_delay' => $this->getAverageDataEntryDelayChart($startDate, $endDate),
            'parent_participation_rate' => $this->getParentParticipationRateChart($startDate, $endDate),
            'group_occupancy_rate' => $this->getGroupOccupancyRateChart($startDate, $endDate),
            'cr_data_entry_within_48h_rate' => $this->getCrDataEntry48hRateChart($startDate, $endDate),
            'beneficiaries_evaluated_according_to_cycle' => $this->getBeneficiariesEvaluatedByCycleChart($startDate, $endDate),
        ];
    }

    /**
     * 1. Taux de présence des bénéficiaires
     * Percentage average of presence at sessions by group
     * Type: Line Chart (Evolution curve)
     */
    public function getAttendanceRateChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        $months = [];
        $rates = [];

        // Get all presence sheets in the date range
        $sheets = \App\Models\PresenceSheet::whereBetween('event_date', [$startDate, $endDate])->get();

        // Group by month
        $dataByMonth = [];
        foreach ($sheets as $sheet) {
            $month = $sheet->event_date->format('Y-m');
            if (!isset($dataByMonth[$month])) {
                $dataByMonth[$month] = [];
            }
            $dataByMonth[$month][] = $sheet;
        }

        // Calculate rate for each month
        foreach ($dataByMonth as $month => $monthSheets) {
            $totalParticipants = 0;
            $presentParticipants = 0;

            foreach ($monthSheets as $sheet) {
                $participants = is_array($sheet->participants) ? $sheet->participants : [];
                $totalParticipants += count($participants);
                
                // Check attendance status (status values: 'Présent', 'Absent', 'En retard', 'Excusé')
                foreach ($participants as $participant) {
                    if (is_array($participant) && isset($participant['status'])) {
                        // Count 'Présent' and 'En retard' as present (late but present)
                        if (in_array($participant['status'], ['Présent', 'En retard'])) {
                            $presentParticipants++;
                        }
                    }
                }
            }

            $rate = $totalParticipants > 0 ? round(($presentParticipants / $totalParticipants) * 100, 2) : 0;
            $months[] = Carbon::createFromFormat('Y-m', $month)->format('M');
            $rates[] = $rate;
        }

        return [
            'kpi' => 'Taux de présence des bénéficiaires',
            'type' => 'line',
            'subtype' => 'curve',
            'unit' => '%',
            'objective' => '≥ 85%',
            'frequency' => 'Séances planifiées × 100 / Hebdo / Mensuel',
            'labels' => $months,
            'data' => $rates,
            'current_value' => end($rates) ?? 0,
            'status' => $this->getStatusIndicator(end($rates) ?? 0, 85),
        ];
    }

    /**
     * 2. Taux d'absentéisme non justifié
     * Percentage of unjustified absences
     * Type: Bar Chart (Filled bars)
     */
    public function getUnjustifiedAbsenceRateChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        $months = [];
        $rates = [];

        // Get all presence sheets in the date range
        $sheets = \App\Models\PresenceSheet::whereBetween('event_date', [$startDate, $endDate])->get();

        // Group by month
        $dataByMonth = [];
        foreach ($sheets as $sheet) {
            $month = $sheet->event_date->format('Y-m');
            if (!isset($dataByMonth[$month])) {
                $dataByMonth[$month] = [];
            }
            $dataByMonth[$month][] = $sheet;
        }

        // Calculate rate for each month
        foreach ($dataByMonth as $month => $monthSheets) {
            $totalAbsences = 0;
            $unjustifiedAbsences = 0;

            foreach ($monthSheets as $sheet) {
                $participants = is_array($sheet->participants) ? $sheet->participants : [];
                
                foreach ($participants as $participant) {
                    if (is_array($participant) && isset($participant['status'])) {
                        // Count 'Absent' statuses
                        if ($participant['status'] === 'Absent') {
                            $totalAbsences++;
                            // Check if justified (has justification field with value)
                            if (empty($participant['justification'])) {
                                $unjustifiedAbsences++;
                            }
                        }
                    }
                }
            }

            $rate = $totalAbsences > 0 ? round(($unjustifiedAbsences / $totalAbsences) * 100, 2) : 0;
            $months[] = Carbon::createFromFormat('Y-m', $month)->format('M');
            $rates[] = $rate;
        }

        return [
            'kpi' => 'Taux d\'absentéisme non justifié',
            'type' => 'bar',
            'unit' => '%',
            'objective' => '≤ 15%',
            'frequency' => 'Abs. non justifié / Total absences) × 100',
            'labels' => $months,
            'data' => $rates,
            'current_value' => end($rates) ?? 0,
            'status' => $this->getStatusIndicator(end($rates) ?? 0, 15, true),
        ];
    }

    /**
     * 3. Nombre moyen de séances par groupe
     * Average number of pedagogical sessions per group
     * Type: Comparative Histogram
     */
    public function getAverageSessionsPerGroupChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        $query = TrainingSession::whereBetween('session_date', [$startDate, $endDate]);

        if ($siteId) {
            $query = $query->whereHas('trainingGroup', fn ($q) => 
                $q->whereHas('training', fn ($tq) => $tq->where('site_id', $siteId))
            );
        }

        $sessions = $query->with('trainingGroup')->get();

        // Group sessions by training group
        $groupSessionCounts = [];
        foreach ($sessions as $session) {
            $groupName = $session->trainingGroup?->title ?? 'Group ' . $session->training_group_id;
            if (!isset($groupSessionCounts[$groupName])) {
                $groupSessionCounts[$groupName] = 0;
            }
            $groupSessionCounts[$groupName]++;
        }

        $groupNames = array_keys($groupSessionCounts);
        $sessionCounts = array_values($groupSessionCounts);

        $averageSessions = count($sessionCounts) > 0 ? round(array_sum($sessionCounts) / count($sessionCounts), 2) : 0;

        return [
            'kpi' => 'Nombre moyen de séances par groupe',
            'type' => 'histogram',
            'subtype' => 'comparative',
            'unit' => 'nombre',
            'objective' => '≥ 6 / mois',
            'frequency' => 'Nb total séances / Nb groupes – Mensuel',
            'labels' => $groupNames,
            'data' => $sessionCounts,
            'average' => $averageSessions,
            'status' => $this->getStatusIndicator($averageSessions, 6),
        ];
    }

    /**
     * 4. Nombre moyen de visites d'accompagnement par site
     * Average number of supervision/support visits per site
     * Type: Horizontal Bar Chart
     */
    public function getAverageSupervisonVisitsChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        // Query visits (tasks with type 'Visite')
        $query = \App\Models\Task::where('type', 'Visite')
            ->whereBetween('expected_start_date', [$startDate, $endDate]);

        if ($siteId) {
            $query = $query->where('location_site_id', $siteId);
        }

        $visits = $query->get();

        // Group visits by site
        $siteVisitCounts = [];
        foreach ($visits as $visit) {
            $siteId = $visit->location_site_id;
            if (!isset($siteVisitCounts[$siteId])) {
                $siteVisitCounts[$siteId] = 0;
            }
            $siteVisitCounts[$siteId]++;
        }

        // Get all sites to display
        $siteQuery = Site::query();
        if (!empty($siteId)) {
            $siteQuery = $siteQuery->where('id', $siteId);
        }
        $sites = $siteQuery->get();

        $siteNames = [];
        $visitCounts = [];

        foreach ($sites as $site) {
            $siteNames[] = $site->name ?? 'Site ' . $site->id;
            $visitCounts[] = $siteVisitCounts[$site->id] ?? 0;
        }

        $averageVisits = count($visitCounts) > 0 ? round(array_sum($visitCounts) / count($visitCounts), 2) : 0;

        return [
            'kpi' => 'Nombre moyen de visites d\'accompagnement par site',
            'type' => 'bar',
            'subtype' => 'horizontal',
            'unit' => 'nombre',
            'objective' => '≥ 1 / mois / site',
            'frequency' => 'Suivi du rythme de supervision terrain / Nb sites – Trimestriel',
            'labels' => $siteNames,
            'data' => $visitCounts,
            'average' => $averageVisits,
            'status' => $this->getStatusIndicator($averageVisits, 1),
        ];
    }

    /**
     * 5. Taux des évaluations réalisé
     * Percentage of evaluations completed
     * Type: Circular Gauge
     */
    public function getEvaluationCompletionRateChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        $query = Beneficiary::query();

        if ($siteId) {
            $query = $query->whereHas('group', fn ($q) => $q->where('site_id', $siteId));
        }

        $totalBeneficiaries = $query->count();

        // Get beneficiaries that have evaluations in the date range
        $evaluatedBeneficiaries = $query->whereHas('group', function ($q) use ($startDate, $endDate) {
            // Check if beneficiary has at least one evaluation in the date range
            $q->whereHas('beneficiaries', fn ($bq) =>
                $bq->whereHas('evaluationOperations', fn ($eq) =>
                    $eq->whereBetween('created_at', [$startDate, $endDate])
                )
            );
        })->count();

        // Alternative approach: Direct query
        if ($totalBeneficiaries > 0) {
            $beneficiaryIds = $query->pluck('id');
            $evaluatedBeneficiaries = EvaluationOperation::whereIn('beneficiary_id', $beneficiaryIds)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->distinct('beneficiary_id')
                ->count();
        }

        $rate = $totalBeneficiaries > 0 ? round(($evaluatedBeneficiaries / $totalBeneficiaries) * 100, 2) : 0;

        return [
            'kpi' => 'Taux des évaluations réalisé',
            'type' => 'gauge',
            'subtype' => 'circular',
            'unit' => '%',
            'objective' => '100%',
            'frequency' => 'Eval. saisies / Eval. prévues) × 100',
            'current_value' => $rate,
            'total_beneficiaries' => $totalBeneficiaries,
            'evaluated_count' => $evaluatedBeneficiaries,
            'status' => $this->getStatusIndicator($rate, 100),
        ];
    }

    /**
     * 6. Délai moyen de saisie des présences
     * Average delay for data entry of presences
     * Type: Box Plot / Attendance Curve
     */
    public function getAverageDataEntryDelayChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        $query = \App\Models\PresenceSheet::whereBetween('event_date', [$startDate, $endDate]);
        $sheets = $query->get();

        $delays = [];
        foreach ($sheets as $sheet) {
            if ($sheet->event_date && $sheet->created_at) {
                $delay = $sheet->created_at->diffInHours($sheet->event_date);
                $delays[] = abs($delay);
            }
        }

        $averageDelay = count($delays) > 0 ? round(array_sum($delays) / count($delays), 2) : 0;
        $maxDelay = count($delays) > 0 ? max($delays) : 0;
        $minDelay = count($delays) > 0 ? min($delays) : 0;

        return [
            'kpi' => 'Délai moyen de saisie des présences',
            'type' => 'box-plot',
            'subtype' => 'attendance_curve',
            'unit' => 'heures',
            'objective' => '≤ 2 jours',
            'frequency' => 'Temps entre date de séance et saisie effective - Hebdomadaire',
            'average_delay' => $averageDelay,
            'min_delay' => $minDelay,
            'max_delay' => $maxDelay,
            'objective_hours' => 48,
            'status' => $this->getStatusIndicator($averageDelay, 48, true),
            'data' => [
                'min' => $minDelay,
                'average' => $averageDelay,
                'max' => $maxDelay,
                'q1' => $this->calculatePercentile($delays, 25),
                'q3' => $this->calculatePercentile($delays, 75),
            ],
        ];
    }

    /**
     * 7. Taux de participation aux réunions de parents
     * Percentage of parent participation in meetings
     * Type: Bar Diagram
     */
    public function getParentParticipationRateChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        $months = [];
        $rates = [];

        // Get all parent meetings (tasks with type 'Réunion') in the date range
        $query = \App\Models\Task::where('type', 'Réunion')
            ->whereBetween('expected_start_date', [$startDate, $endDate])
            ->with('meeting.parents');

        if ($siteId) {
            $query = $query->where('location_site_id', $siteId);
        }

        $meetings = $query->get();

        // Group by month
        $dataByMonth = [];
        foreach ($meetings as $task) {
            $month = $task->expected_start_date->format('Y-m');
            if (!isset($dataByMonth[$month])) {
                $dataByMonth[$month] = [];
            }
            $dataByMonth[$month][] = $task;
        }

        // Calculate participation rate for each month
        foreach ($dataByMonth as $month => $monthMeetings) {
            $totalParents = 0;
            $presentParents = 0;

            foreach ($monthMeetings as $task) {
                $taskMeeting = $task->meeting;
                if ($taskMeeting) {
                    // Get expected participants count
                    $expectedCount = $taskMeeting->expected_participants_count ?? 0;
                    $totalParents += $expectedCount;

                    // Get actual participants (parents that attended)
                    $actualCount = $taskMeeting->parents()->count();
                    $presentParents += $actualCount;
                }
            }

            $rate = $totalParents > 0 ? round(($presentParents / $totalParents) * 100, 2) : 0;
            $months[] = Carbon::createFromFormat('Y-m', $month)->format('M');
            $rates[] = $rate;
        }

        return [
            'kpi' => 'Taux de participation aux réunions de parents',
            'type' => 'bar',
            'subtype' => 'diagram',
            'unit' => '%',
            'objective' => '≥ 60%',
            'frequency' => '(Parents présents / Parents inscrits) × 100',
            'labels' => $months,
            'data' => $rates,
            'current_value' => end($rates) ?? 0,
            'status' => $this->getStatusIndicator(end($rates) ?? 0, 60),
        ];
    }

    /**
     * 8. Taux d'occupation des groupes
     * Ratio of actual capacity utilization
     * Type: Simple Histogram
     */
    public function getGroupOccupancyRateChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        $query = Group::query();

        // Note: Group model doesn't have site_id column, so siteId parameter is not used
        // If site filtering is needed in the future, it would need to be implemented through the class relationship

        $groups = $query->get();

        $groupNames = [];
        $occupancyRates = [];

        foreach ($groups as $group) {
            $capacity = $group->target_capacity ?? 1;
            $current_headcount =  $group->current_headcount ?? 0;
            $occupancyRate = ($current_headcount / $capacity) * 100;

            $groupNames[] = $group->name ?? 'Group ' . $group->id;
            $occupancyRates[] = round($occupancyRate, 2);
        }

        $averageOccupancy = count($occupancyRates) > 0 ? round(array_sum($occupancyRates) / count($occupancyRates), 2) : 0;

        return [
            'kpi' => 'Taux d\'occupation des groupes',
            'type' => 'histogram',
            'subtype' => 'simple',
            'unit' => '%',
            'objective' => '≥ 90%',
            'frequency' => 'Effectif actuel / Capacité cible × 100',
            'labels' => $groupNames,
            'data' => $occupancyRates,
            'average' => $averageOccupancy,
            'status' => $this->getStatusIndicator($averageOccupancy, 90),
        ];
    }

    /**
     * 9. Taux de CR saisies dans les 48h après l'événement
     * Percentage of meeting reports (CR) entered within 48h
     * Type: Linear Gauge / Circular Gauge
     */
    public function getCrDataEntry48hRateChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        // Note: CR submission tracking column doesn't exist in training_sessions table
        // This is a placeholder implementation that returns 0% until proper tracking is implemented
        $query = TrainingSession::whereBetween('session_date', [$startDate, $endDate]);

        if ($siteId) {
            $query = $query->whereHas('trainingGroup', fn ($q) => 
                $q->whereHas('training', fn ($tq) => $tq->where('site_id', $siteId))
            );
        }

        $totalSessions = $query->count();
        
        // Placeholder: Set to 0 until CR submission tracking is implemented
        $sessionsWithCrWithin48h = 0;
        $rate = 0;

        return [
            'kpi' => 'Taux de CR saisies dans les 48h après l\'événement',
            'type' => 'gauge',
            'subtype' => 'linear',
            'unit' => '%',
            'objective' => '≥ 90%',
            'frequency' => 'CR saisies < 48h / CR totaux × 100',
            'current_value' => $rate,
            'total_sessions' => $totalSessions,
            'cr_within_48h' => $sessionsWithCrWithin48h,
            'status' => $this->getStatusIndicator($rate, 90),
            'note' => 'CR submission tracking is not yet implemented'
        ];
    }

    /**
     * 10. Taux de bénéficiaires évalués selon le cycle prévu
     * Percentage of beneficiaries evaluated according to planned cycle
     * Type: Progress Curve / Line Chart with reference
     */
    public function getBeneficiariesEvaluatedByCycleChart(Carbon $startDate, Carbon $endDate, ?int $siteId = null): array
    {
        $months = [];
        $evaluationRates = [];

        // Get total beneficiaries in system
        $query = Beneficiary::query();
        if ($siteId) {
            $query = $query->whereHas('group', fn ($q) => $q->where('site_id', $siteId));
        }
        $totalBeneficiaries = $query->count();

        // Get all evaluation tasks in the date range
        $query = \App\Models\Task::where('type', 'Évaluation')
            ->whereBetween('expected_start_date', [$startDate, $endDate])
            ->with('evaluation');

        if ($siteId) {
            $query = $query->where('location_site_id', $siteId);
        }

        $evaluationTasks = $query->get();

        // Group evaluations by month
        $dataByMonth = [];
        foreach ($evaluationTasks as $task) {
            $month = $task->expected_start_date->format('Y-m');
            if (!isset($dataByMonth[$month])) {
                $dataByMonth[$month] = [];
            }
            $dataByMonth[$month][] = $task;
        }

        // Calculate evaluation rate for each month
        foreach ($dataByMonth as $month => $monthTasks) {
            $evaluatedBeneficiaries = collect();

            // Collect unique beneficiaries that were evaluated in this month
            foreach ($monthTasks as $task) {
                $taskEvaluation = $task->evaluation;
                if ($taskEvaluation && $taskEvaluation->evaluated_beneficiary_id) {
                    $evaluatedBeneficiaries->push($taskEvaluation->evaluated_beneficiary_id);
                }
            }

            $uniqueEvaluatedCount = $evaluatedBeneficiaries->unique()->count();
            $rate = $totalBeneficiaries > 0 ? round(($uniqueEvaluatedCount / $totalBeneficiaries) * 100, 2) : 0;

            $months[] = Carbon::createFromFormat('Y-m', $month)->format('M');
            $evaluationRates[] = $rate;
        }

        return [
            'kpi' => 'Taux de bénéficiaires évalués selon le cycle prévu',
            'type' => 'line',
            'subtype' => 'progress_curve',
            'unit' => '%',
            'objective' => '100%',
            'frequency' => 'Respect du calendrier pédagogique',
            'labels' => $months,
            'data' => $evaluationRates,
            'current_value' => end($evaluationRates) ?? 0,
            'status' => $this->getStatusIndicator(end($evaluationRates) ?? 0, 100),
        ];
    }

    // ============ HELPER METHODS ============

    /**
     * Get status indicator based on current value and objective
     * Returns: 'excellent', 'good', 'warning', 'critical'
     */
    private function getStatusIndicator($currentValue, $objective, $isInverse = false): string
    {
        if ($isInverse) {
            // For metrics where lower is better (like absence rate)
            if ($currentValue <= ($objective * 0.5)) {
                return 'excellent';
            } elseif ($currentValue <= $objective) {
                return 'good';
            } elseif ($currentValue <= ($objective * 1.5)) {
                return 'warning';
            }
            return 'critical';
        } else {
            // For metrics where higher is better (like presence rate)
            if ($currentValue >= $objective) {
                return 'excellent';
            } elseif ($currentValue >= ($objective * 0.9)) {
                return 'good';
            } elseif ($currentValue >= ($objective * 0.75)) {
                return 'warning';
            }
            return 'critical';
        }
    }

    /**
     * Calculate percentile value from array
     */
    private function calculatePercentile(array $values, float $percentile): float
    {
        if (empty($values)) {
            return 0;
        }

        sort($values);
        $index = (int) (($percentile / 100) * (count($values) - 1));
        return (float) $values[$index];
    }

    /**
     * Format chart data for frontend consumption
     */
    public function formatChartForFrontend(array $chartData, string $chartLibrary = 'chart.js'): array
    {
        // This method can be extended to format data for different charting libraries
        // (Chart.js, ApexCharts, ECharts, etc.)

        return match ($chartLibrary) {
            'chart.js' => $this->formatForChartJs($chartData),
            'apex' => $this->formatForApexCharts($chartData),
            'echarts' => $this->formatForECharts($chartData),
            default => $chartData,
        };
    }

    private function formatForChartJs(array $chartData): array
    {
        // Convert to Chart.js format
        return [
            'type' => $chartData['type'] ?? 'line',
            'data' => [
                'labels' => $chartData['labels'] ?? [],
                'datasets' => [
                    [
                        'label' => $chartData['kpi'] ?? '',
                        'data' => $chartData['data'] ?? [],
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.4,
                        'fill' => true,
                    ],
                ],
            ],
            'options' => [
                'responsive' => true,
                'plugins' => [
                    'legend' => ['display' => true],
                    'title' => ['display' => true, 'text' => $chartData['kpi'] ?? ''],
                ],
            ],
        ];
    }

    private function formatForApexCharts(array $chartData): array
    {
        // Convert to ApexCharts format
        return [
            'chart' => ['type' => $chartData['type'] ?? 'line'],
            'series' => [
                [
                    'name' => $chartData['kpi'] ?? '',
                    'data' => $chartData['data'] ?? [],
                ],
            ],
            'xaxis' => ['categories' => $chartData['labels'] ?? []],
        ];
    }

    private function formatForECharts(array $chartData): array
    {
        // Convert to ECharts format
        return [
            'title' => ['text' => $chartData['kpi'] ?? ''],
            'xAxis' => ['type' => 'category', 'data' => $chartData['labels'] ?? []],
            'yAxis' => ['type' => 'value'],
            'series' => [
                [
                    'data' => $chartData['data'] ?? [],
                    'type' => $chartData['type'] ?? 'line',
                    'smooth' => true,
                ],
            ],
        ];
    }
}
