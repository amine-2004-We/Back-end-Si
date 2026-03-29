<?php

namespace App\Services;

use App\Enums\ConventionStatus;
use App\Enums\ContactStatusEnum;
use App\Enums\EvaluationStatusEnum;
use App\Models\ContactPerson;
use App\Models\Convention;
use App\Models\Evaluation;
use App\Models\Grant;
use App\Models\Partner;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PartnershipDashboardService
{
    /**
     * Get all KPI data for the partnership dashboard
     * @param array $options Configuration options (year, etc.)
     * @return array
     */
    public function getAllKpis(array $options = []): array
    {
        $year = $options['year'] ?? now()->year;

        return [
            'active_projects' => $this->getActiveProjectsCount($options),
            'convention_rate' => $this->getConventionRate($options),
            'total_grants' => $this->getTotalCommittedGrants($options),
            'grant_reception_rate' => $this->getGrantReceptionRate($options),
            'partners_by_type' => $this->getPartnersByType($options),
            'projects_by_zone' => $this->getProjectsByZone($options),
            'projects_by_phase' => $this->getProjectsByPhase($options),
            'average_signing_delay' => $this->getAverageSigningDelay($options),
            'task_delay_rate' => $this->getTaskDelayRate($options),
            'completed_evaluations' => $this->getCompletedEvaluationsCount($options),
            'average_partner_score' => $this->getAveragePartnerScore($options),
            'unsubscribe_rate' => $this->getUnsubscribeRate($options),
        ];
    }

    /**
     * 1. Nombre total de projets actifs
     * Count of projects with status "En cours"
     * Frequency: Mensuelle
     * Chart: Score card / Simple counter
     */
    public function getActiveProjectsCount(array $options = []): array
    {
        $inProgressStatusIds = ProjectStatus::where('name', 'LIKE', '%En cours%')
            ->orWhere('name', 'LIKE', '%In progress%')
            ->pluck('id');

        $totalActive = Project::when($inProgressStatusIds->isNotEmpty(), function ($query) use ($inProgressStatusIds) {
            $query->whereIn('project_status_id', $inProgressStatusIds);
        })->count();

        // Get distribution by status for additional insights
        $projectsByStatus = Project::with('projectStatus')
            ->get()
            ->groupBy(fn($p) => $p->projectStatus?->name ?? 'Non défini')
            ->map(fn($group) => $group->count());

        return [
            'kpi' => 'Nombre total de projets actifs',
            'type' => 'score_card',
            'frequency' => 'Mensuelle',
            'total_count' => $totalActive,
            'projects_by_status' => $projectsByStatus->toArray(),
        ];
    }

    /**
     * 2. Taux de conventions en cours
     * (Nb conventions signées) ÷ (total conventions) × 100
     * Frequency: Mensuelle
     * Chart: Circular gauge / Donut
     */
    public function getConventionRate(array $options = []): array
    {
        $totalConventions = Convention::count();
        $signedConventions = Convention::where('status', ConventionStatus::Signed)->count();
        
        $rate = $totalConventions > 0 
            ? round(($signedConventions / $totalConventions) * 100, 2) 
            : 0;

        return [
            'kpi' => 'Taux de conventions en cours',
            'type' => 'gauge',
            'subtype' => 'donut',
            'unit' => '%',
            'frequency' => 'Mensuelle',
            'rate' => $rate,
            'signed' => $signedConventions,
            'total' => $totalConventions,
        ];
    }

    /**
     * 3. Montant total des subventions engagées
     * Sum of committed amounts for validated grants
     * Frequency: Trimestrielle
     * Chart: Evolution curve / Histogram
     */
    public function getTotalCommittedGrants(array $options = []): array
    {
        $year = $options['year'] ?? now()->year;

        $totalAmount = Grant::where('status', 'validée')
            ->orWhere('status', 'Validée')
            ->sum('committed_amount');

        // Quarterly breakdown
        $quarters = [];
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;

            $startDate = Carbon::createFromDate($year, $startMonth, 1);
            $endDate = Carbon::createFromDate($year, $endMonth, 1)->endOfMonth();

            $quarterAmount = Grant::whereBetween('created_at', [$startDate, $endDate])
                ->where(function($q) {
                    $q->where('status', 'validée')->orWhere('status', 'Validée');
                })
                ->sum('committed_amount');

            $quarters["Q{$quarter}"] = round((float)$quarterAmount, 2);
        }

        return [
            'kpi' => 'Montant total des subventions engagées',
            'type' => 'histogram',
            'unit' => 'DH',
            'frequency' => 'Trimestrielle',
            'total_amount' => round((float)$totalAmount, 2),
            'quarterly_data' => $quarters,
            'labels' => array_keys($quarters),
            'data' => array_values($quarters),
        ];
    }

    /**
     * 4. Taux de réception des subventions
     * Montant reçu ÷ Montant engagé × 100
     * Frequency: Trimestrielle
     * Chart: Stacked bar
     */
    public function getGrantReceptionRate(array $options = []): array
    {
        $committedAmount = Grant::sum('committed_amount');
        $receivedAmount = Grant::sum('received_amount');

        $rate = $committedAmount > 0 
            ? round(($receivedAmount / $committedAmount) * 100, 2) 
            : 0;

        return [
            'kpi' => 'Taux de réception des subventions',
            'type' => 'bar',
            'subtype' => 'stacked',
            'unit' => '%',
            'frequency' => 'Trimestrielle',
            'rate' => $rate,
            'received' => round((float)$receivedAmount, 2),
            'committed' => round((float)$committedAmount, 2),
            'remaining' => round((float)($committedAmount - $receivedAmount), 2),
        ];
    }

    /**
     * 5. Nombre de partenaires par type
     * Distribution of partners by type (Institution, ONG, etc.)
     * Frequency: Trimestrielle
     * Chart: Pie chart
     */
    public function getPartnersByType(array $options = []): array
    {
        $partnersByType = Partner::get()
            ->groupBy('partner_type')
            ->map(fn($group) => $group->count());

        // Also group by nature if available
        $partnersByNature = Partner::with('naturePartner')
            ->get()
            ->groupBy(fn($p) => $p->naturePartner?->name ?? 'Non défini')
            ->map(fn($group) => $group->count());

        return [
            'kpi' => 'Nombre de partenaires par type',
            'type' => 'pie',
            'frequency' => 'Trimestrielle',
            'by_type' => [
                'labels' => $partnersByType->keys()->toArray(),
                'data' => $partnersByType->values()->toArray(),
            ],
            'by_nature' => [
                'labels' => $partnersByNature->keys()->toArray(),
                'data' => $partnersByNature->values()->toArray(),
            ],
            'total' => Partner::count(),
        ];
    }

    /**
     * 6. Projets par zone géographique
     * Number of projects grouped by region
     * Frequency: Trimestrielle
     * Chart: Geographic map / Bars
     */
    public function getProjectsByZone(array $options = []): array
    {
        $projectsByRegion = Project::with('region')
            ->get()
            ->groupBy(fn($p) => $p->region?->name ?? 'Non défini')
            ->map(fn($group) => $group->count())
            ->sortDesc();

        return [
            'kpi' => 'Projets par zone géographique',
            'type' => 'bar',
            'subtype' => 'geographic',
            'frequency' => 'Trimestrielle',
            'labels' => $projectsByRegion->keys()->toArray(),
            'data' => $projectsByRegion->values()->toArray(),
            'total' => Project::count(),
        ];
    }

    /**
     * 7. Projets par phase (cycle de vie)
     * Number of projects by status (En conception, En cours, Terminé, etc.)
     * Frequency: Mensuelle
     * Chart: Horizontal bars
     */
    public function getProjectsByPhase(array $options = []): array
    {
        $projectsByPhase = Project::get()
            ->groupBy('current_phase')
            ->map(fn($group) => $group->count());

        $projectsByStatus = Project::with('projectStatus')
            ->get()
            ->groupBy(fn($p) => $p->projectStatus?->name ?? 'Non défini')
            ->map(fn($group) => $group->count());

        return [
            'kpi' => 'Projets par phase (cycle de vie)',
            'type' => 'bar',
            'subtype' => 'horizontal',
            'frequency' => 'Mensuelle',
            'by_phase' => [
                'labels' => $projectsByPhase->keys()->toArray(),
                'data' => $projectsByPhase->values()->toArray(),
            ],
            'by_status' => [
                'labels' => $projectsByStatus->keys()->toArray(),
                'data' => $projectsByStatus->values()->toArray(),
            ],
        ];
    }

    /**
     * 8. Délai moyen de signature des conventions
     * Average of (Date signature – Date création)
     * Frequency: Trimestrielle
     * Chart: Curve / Boxplot
     */
    public function getAverageSigningDelay(array $options = []): array
    {
        $conventions = Convention::whereNotNull('signed_at')->get();

        $delays = $conventions->map(function ($convention) {
            return $convention->created_at->diffInDays($convention->signed_at);
        })->filter()->values();

        $averageDays = $delays->count() > 0 ? round($delays->avg(), 2) : 0;
        $minDays = $delays->count() > 0 ? $delays->min() : 0;
        $maxDays = $delays->count() > 0 ? $delays->max() : 0;

        return [
            'kpi' => 'Délai moyen de signature des conventions',
            'type' => 'boxplot',
            'unit' => 'jours',
            'frequency' => 'Trimestrielle',
            'average_days' => $averageDays,
            'min_days' => $minDays,
            'max_days' => $maxDays,
            'total_signed' => $delays->count(),
        ];
    }

    /**
     * 9. Taux de retard des tâches
     * (Nb tâches en retard ÷ Nb total de tâches) × 100
     * Frequency: Mensuelle
     * Chart: Circular gauge / KPI red/green
     */
    public function getTaskDelayRate(array $options = []): array
    {
        $totalTasks = Task::count();
        
        // Tasks are late if actual_end_date > expected_end_date or if expected_end_date is past and task not completed
        $lateTasks = Task::where(function ($query) {
            $query->whereColumn('actual_end_date', '>', 'expected_end_date')
                  ->orWhere(function ($q) {
                      $q->where('expected_end_date', '<', now())
                        ->whereNull('actual_end_date')
                        ->where('status', '!=', 'Terminé');
                  });
        })->count();

        // Also count by status
        $tasksByStatus = Task::get()
            ->groupBy('status')
            ->map(fn($group) => $group->count());

        $rate = $totalTasks > 0 
            ? round(($lateTasks / $totalTasks) * 100, 2) 
            : 0;

        return [
            'kpi' => 'Taux de retard des tâches',
            'type' => 'gauge',
            'subtype' => 'circular',
            'unit' => '%',
            'frequency' => 'Mensuelle',
            'rate' => $rate,
            'delayed' => $lateTasks,
            'total' => $totalTasks,
            'by_status' => $tasksByStatus->toArray(),
            'status' => $rate <= 15 ? 'good' : ($rate <= 30 ? 'warning' : 'critical'),
        ];
    }

    /**
     * 10. Nombre d'évaluations réalisées
     * Total number of validated evaluations (partners or projects)
     * Frequency: Semestrielle
     * Chart: Histogram
     */
    public function getCompletedEvaluationsCount(array $options = []): array
    {
        $year = $options['year'] ?? now()->year;

        $validatedEvaluations = Evaluation::where('evaluation_status', EvaluationStatusEnum::ACCEPTED)->count();
        
        $totalEvaluations = Evaluation::count();

        // By type
        $evaluationsByType = Evaluation::where('evaluation_status', EvaluationStatusEnum::ACCEPTED)
            ->get()
            ->groupBy(fn($e) => $e->evaluation_type?->value ?? 'Non défini')
            ->map(fn($group) => $group->count());

        // Bi-annual data
        $semesters = [
            'S1' => Evaluation::where('evaluation_status', EvaluationStatusEnum::ACCEPTED)
                ->whereBetween('created_at', [
                    Carbon::createFromDate($year, 1, 1),
                    Carbon::createFromDate($year, 6, 30)->endOfDay()
                ])->count(),
            'S2' => Evaluation::where('evaluation_status', EvaluationStatusEnum::ACCEPTED)
                ->whereBetween('created_at', [
                    Carbon::createFromDate($year, 7, 1),
                    Carbon::createFromDate($year, 12, 31)->endOfDay()
                ])->count(),
        ];

        return [
            'kpi' => 'Nombre d\'évaluations réalisées',
            'type' => 'histogram',
            'frequency' => 'Semestrielle',
            'count' => $validatedEvaluations,
            'total' => $totalEvaluations,
            'by_type' => $evaluationsByType->toArray(),
            'by_semester' => $semesters,
            'labels' => array_keys($semesters),
            'data' => array_values($semesters),
        ];
    }

    /**
     * 11. Score moyen des partenaires évalués
     * Weighted average of global scores on partner evaluations
     * Frequency: Semestrielle
     * Chart: Trend curve / Moving average
     */
    public function getAveragePartnerScore(array $options = []): array
    {
        // Get evaluations that have partner evaluations
        $partnerEvaluations = Evaluation::whereNotNull('object_partner')
            ->where('evaluation_status', EvaluationStatusEnum::ACCEPTED)
            ->get();

        $scores = [];
        foreach ($partnerEvaluations as $evaluation) {
            // Try to get global score from criteria_scores JSON field
            $criteriaScores = $evaluation->criteria_scores;
            if (is_array($criteriaScores) && isset($criteriaScores['global_score'])) {
                $scores[] = $criteriaScores['global_score'];
            } elseif (is_string($criteriaScores)) {
                $decoded = json_decode($criteriaScores, true);
                if (isset($decoded['global_score'])) {
                    $scores[] = $decoded['global_score'];
                }
            }
        }

        $averageScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;

        return [
            'kpi' => 'Score moyen des partenaires évalués',
            'type' => 'line',
            'subtype' => 'trend',
            'frequency' => 'Semestrielle',
            'average_score' => $averageScore,
            'evaluations_count' => $partnerEvaluations->count(),
            'scores_available' => count($scores),
        ];
    }

    /**
     * 12. Taux de contacts désabonnés (newsletter)
     * Nb désabonnements ÷ Nb total de contacts × 100
     * Frequency: Mensuelle
     * Chart: Curve / KPI alone
     */
    public function getUnsubscribeRate(array $options = []): array
    {
        $totalContacts = ContactPerson::count();
        $unsubscribedContacts = ContactPerson::where('contact_status', ContactStatusEnum::DESABONNE)->count();

        $rate = $totalContacts > 0 
            ? round(($unsubscribedContacts / $totalContacts) * 100, 2) 
            : 0;

        // Status distribution
        $contactsByStatus = ContactPerson::get()
            ->groupBy(fn($c) => $c->contact_status?->value ?? 'Non défini')
            ->map(fn($group) => $group->count());

        return [
            'kpi' => 'Taux de contacts désabonnés (newsletter)',
            'type' => 'line',
            'unit' => '%',
            'frequency' => 'Mensuelle',
            'rate' => $rate,
            'unsubscribed' => $unsubscribedContacts,
            'total' => $totalContacts,
            'by_status' => $contactsByStatus->toArray(),
        ];
    }

    /**
     * Get list of available KPIs
     */
    public function getAvailableKpis(): array
    {
        return [
            'active_projects' => [
                'name' => 'Nombre total de projets actifs',
                'frequency' => 'Mensuelle',
                'chart_type' => 'score_card',
                'source' => 'Project',
            ],
            'convention_rate' => [
                'name' => 'Taux de conventions en cours',
                'frequency' => 'Mensuelle',
                'chart_type' => 'gauge',
                'source' => 'Convention',
            ],
            'total_grants' => [
                'name' => 'Montant total des subventions engagées',
                'frequency' => 'Trimestrielle',
                'chart_type' => 'histogram',
                'source' => 'Grant',
            ],
            'grant_reception_rate' => [
                'name' => 'Taux de réception des subventions',
                'frequency' => 'Trimestrielle',
                'chart_type' => 'stacked_bar',
                'source' => 'Grant',
            ],
            'partners_by_type' => [
                'name' => 'Nombre de partenaires par type',
                'frequency' => 'Trimestrielle',
                'chart_type' => 'pie',
                'source' => 'Partner',
            ],
            'projects_by_zone' => [
                'name' => 'Projets par zone géographique',
                'frequency' => 'Trimestrielle',
                'chart_type' => 'bar',
                'source' => 'Project',
            ],
            'projects_by_phase' => [
                'name' => 'Projets par phase (cycle de vie)',
                'frequency' => 'Mensuelle',
                'chart_type' => 'horizontal_bar',
                'source' => 'Project',
            ],
            'average_signing_delay' => [
                'name' => 'Délai moyen de signature des conventions',
                'frequency' => 'Trimestrielle',
                'chart_type' => 'boxplot',
                'source' => 'Convention',
            ],
            'task_delay_rate' => [
                'name' => 'Taux de retard des tâches',
                'frequency' => 'Mensuelle',
                'chart_type' => 'gauge',
                'source' => 'Task',
            ],
            'completed_evaluations' => [
                'name' => 'Nombre d\'évaluations réalisées',
                'frequency' => 'Semestrielle',
                'chart_type' => 'histogram',
                'source' => 'Evaluation',
            ],
            'average_partner_score' => [
                'name' => 'Score moyen des partenaires évalués',
                'frequency' => 'Semestrielle',
                'chart_type' => 'trend_line',
                'source' => 'Evaluation',
            ],
            'unsubscribe_rate' => [
                'name' => 'Taux de contacts désabonnés',
                'frequency' => 'Mensuelle',
                'chart_type' => 'line',
                'source' => 'ContactPerson',
            ],
        ];
    }
}
