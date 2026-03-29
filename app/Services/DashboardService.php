<?php

namespace App\Services;

use App\Models\Collaborator;
use App\Models\Convention;
use App\Models\FinancialResource;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Site;
use App\Enums\ConventionStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DashboardService
{
    /**
     * Get dashboard chart data for any model
     * Supports: Convention, Collaborator, Site, Project, etc.
     *
     * @param string $modelType Type of model (convention, collaborator, site, project, etc.)
     * @param array $options Configuration options
     * @return array Chart data ready for frontend
     */
    public function getDashboardData(string $modelType, array $options = []): array
    {
        $modelType = strtolower($modelType);

        return match ($modelType) {
            'convention' => $this->getConventionDashboard($options),
            'collaborator' => $this->getCollaboratorDashboard($options),
            'site' => $this->getSiteDashboard($options),
            'project' => $this->getProjectDashboard($options),
            'financial' => $this->getFinancialDashboard($options),
            default => ['error' => "Dashboard for '{$modelType}' not supported"],
        };
    }

    /**
     * Convention Dashboard
     * - Convention rate by month
     * - Subventions by quarter
     * - Project statistics
     */
    public function getConventionDashboard(array $options = []): array
    {
        $year = $options['year'] ?? now()->year;

        return [
            'title' => 'Convention Dashboard',
            'year' => $year,
            'metrics' => [
                'convention_rate' => $this->getConventionRateData($year),
                'subventions' => $this->getSubventionsQuarterlyData($year),
                'projects' => $this->getProjectsStatistics(),
            ],
        ];
    }

    /**
     * Collaborator Dashboard
     * - Collaborators by department (pie chart)
     * - Collaborators by status (bar chart)
     * - Collaborators growth over time (line chart)
     */
    public function getCollaboratorDashboard(array $options = []): array
    {
        $year = $options['year'] ?? now()->year;

        return [
            'title' => 'Collaborators Dashboard',
            'year' => $year,
            'metrics' => [
                'by_department' => $this->getCollaboratorsByDepartment(),
                'by_status' => $this->getCollaboratorsByStatus(),
                'growth_over_time' => $this->getCollaboratorsGrowthOverTime($year),
                'total_count' => Collaborator::count(),
            ],
        ];
    }

    /**
     * Site Dashboard
     * - Sites by region (pie chart)
     * - Sites by type (bar chart)
     * - Units per site (histogram)
     */
    public function getSiteDashboard(array $options = []): array
    {
        return [
            'title' => 'Sites Dashboard',
            'metrics' => [
                'by_region' => $this->getSitesByRegion(),
                'by_type' => $this->getSitesByType(),
                'units_per_site' => $this->getBeneficiariesPerSite(),
                'total_count' => Site::count(),
            ],
        ];
    }

    /**
     * Project Dashboard
     * - Projects by status (pie chart)
     * - Projects timeline (bar chart)
     * - Budget utilization (histogram)
     */
    public function getProjectDashboard(array $options = []): array
    {
        $year = $options['year'] ?? now()->year;

        return [
            'title' => 'Projects Dashboard',
            'year' => $year,
            'metrics' => [
                'by_status' => $this->getProjectsByStatus(),
                'timeline' => $this->getProjectsTimeline($year),
                'budget_utilization' => $this->getProjectsBudgetUtilization(),
                'total_count' => Project::count(),
            ],
        ];
    }

    /**
     * Financial Dashboard
     * - Financial resources by type (pie chart)
     * - Financial flow over time (line chart)
     * - Budget vs actual (comparison chart)
     */
    public function getFinancialDashboard(array $options = []): array
    {
        $year = $options['year'] ?? now()->year;

        return [
            'title' => 'Financial Dashboard',
            'year' => $year,
            'metrics' => [
                'by_type' => $this->getFinancialResourcesByType(),
                'monthly_flow' => $this->getFinancialMonthlyFlow($year),
                'total_resources' => $this->getTotalFinancialResources(),
            ],
        ];
    }

    // ============ CONVENTION METRICS ============

    private function getConventionRateData(int $year): array
    {
        $months = [];
        $rates = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($year, $month, 1);
            $endDate = $startDate->clone()->endOfMonth();

            $totalConventions = Convention::whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $signedConventions = Convention::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', ConventionStatus::Signed)
                ->count();

            $rate = $totalConventions > 0 ? round(($signedConventions / $totalConventions) * 100, 2) : 0;

            $months[] = $startDate->format('M');
            $rates[] = $rate;
        }

        return [
            'type' => 'gauge',
            'title' => 'Taux de conventions en cours',
            'unit' => '%',
            'frequency' => 'Mensuelle',
            'labels' => $months,
            'data' => $rates,
            'current_value' => end($rates) ?? 0,
        ];
    }

    private function getSubventionsQuarterlyData(int $year): array
    {
        $quarters = [];
        $amounts = [];

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $startMonth = ($quarter - 1) * 3 + 1;
            $endMonth = $startMonth + 2;

            $startDate = Carbon::createFromDate($year, $startMonth, 1);
            $endDate = Carbon::createFromDate($year, $endMonth, 1)->endOfMonth();

            $totalAmount = FinancialResource::where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('slice_date', [$startDate, $endDate])
                          ->orWhereBetween('created_at', [$startDate, $endDate]);
                })
                ->where('financial_type', 'Subvention')
                ->sum('amount_received');

            $quarters[] = "Q{$quarter} {$year}";
            $amounts[] = round((float) $totalAmount, 2);
        }

        return [
            'type' => 'histogram',
            'title' => 'Montant total des subventions engagées',
            'unit' => 'DH',
            'frequency' => 'Trimestrielle',
            'labels' => $quarters,
            'data' => $amounts,
            'total' => array_sum($amounts),
        ];
    }

    private function getProjectsStatistics(): array
    {
        $activeStatusIds = ProjectStatus::whereIn('name', ['Actif', 'Active'])->pluck('id');
        $inProgressStatusIds = ProjectStatus::whereIn('name', ['En cours', 'In progress'])->pluck('id');

        $totalProjects = Project::when($activeStatusIds->isNotEmpty(), function ($query) use ($activeStatusIds) {
            $query->whereIn('project_status_id', $activeStatusIds);
        })->count();

        $projectsInProgress = Project::when($inProgressStatusIds->isNotEmpty(), function ($query) use ($inProgressStatusIds) {
            $query->whereIn('project_status_id', $inProgressStatusIds);
        })->count();

        return [
            'type' => 'metric',
            'total_active_projects' => $totalProjects,
            'projects_in_progress' => $projectsInProgress,
            'label' => 'Nombre total de projets actifs',
            'label_in_progress' => 'Nombre de projets ayant un statut "En cours"',
        ];
    }

    // ============ COLLABORATOR METRICS ============

    private function getCollaboratorsByDepartment(): array
    {
        $collaborators = Collaborator::with('department')
            ->get()
            ->groupBy(fn ($c) => $c->department?->name ?? 'Unassigned')
            ->map(fn ($group) => $group->count());

        return [
            'type' => 'pie',
            'title' => 'Collaborateurs par département',
            'labels' => $collaborators->keys()->toArray(),
            'data' => $collaborators->values()->toArray(),
        ];
    }

    private function getCollaboratorsByStatus(): array
    {
        $collaborators = Collaborator::with('collaboratorStatus')
            ->get()
            ->groupBy(fn ($c) => $c->collaboratorStatus?->type ?? 'No Status')
            ->map(fn ($group) => $group->count());

        return [
            'type' => 'bar',
            'title' => 'Collaborateurs par statut',
            'labels' => $collaborators->keys()->toArray(),
            'data' => $collaborators->values()->toArray(),
        ];
    }

    private function getCollaboratorsGrowthOverTime(int $year): array
    {
        $months = [];
        $counts = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($year, $month, 1);
            $endDate = $startDate->clone()->endOfMonth();

            $count = Collaborator::whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $months[] = $startDate->format('M');
            $counts[] = $count;
        }

        return [
            'type' => 'line',
            'title' => 'Croissance des collaborateurs',
            'unit' => 'nombre',
            'frequency' => 'Mensuelle',
            'labels' => $months,
            'data' => $counts,
        ];
    }

    // ============ SITE METRICS ============

    private function getSitesByRegion(): array
    {
        $sites = Site::with('commune.province.region')
            ->get()
            ->groupBy(fn ($s) => $s->commune?->province?->region?->name ?? 'Unknown Region')
            ->map(fn ($group) => $group->count());

        return [
            'type' => 'pie',
            'title' => 'Sites par région',
            'labels' => $sites->keys()->toArray(),
            'data' => $sites->values()->toArray(),
        ];
    }

    private function getSitesByType(): array
    {
        $sites = Site::get()
            ->groupBy('type')
            ->map(fn ($group) => $group->count());

        return [
            'type' => 'bar',
            'title' => 'Sites par type',
            'labels' => $sites->keys()->toArray(),
            'data' => $sites->values()->toArray(),
        ];
    }

    private function getBeneficiariesPerSite(): array
    {
        // Adjust according to your beneficiary-site relationship
        $beneficiaries = Site::withCount('units')
            ->get()
            ->map(fn ($site) => [
                'site' => $site->name,
                'count' => $site->units_count ?? 0,
            ]);

        return [
            'type' => 'histogram',
            'title' => 'Unités par site',
            'labels' => $beneficiaries->pluck('site')->toArray(),
            'data' => $beneficiaries->pluck('count')->toArray(),
        ];
    }

    // ============ PROJECT METRICS ============

    private function getProjectsByStatus(): array
    {
        $projects = Project::with('projectStatus')
            ->get()
            ->groupBy('projectStatus.name')
            ->map(fn ($group) => $group->count());

        return [
            'type' => 'pie',
            'title' => 'Projets par statut',
            'labels' => $projects->keys()->toArray(),
            'data' => $projects->values()->toArray(),
        ];
    }

    private function getProjectsTimeline(int $year): array
    {
        $months = [];
        $counts = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($year, $month, 1);
            $endDate = $startDate->clone()->endOfMonth();

            $count = Project::whereBetween('start_date', [$startDate, $endDate])
                ->count();

            $months[] = $startDate->format('M');
            $counts[] = $count;
        }

        return [
            'type' => 'bar',
            'title' => 'Timeline des projets',
            'unit' => 'nombre',
            'frequency' => 'Mensuelle',
            'labels' => $months,
            'data' => $counts,
        ];
    }

    private function getProjectsBudgetUtilization(): array
    {
        $projects = Project::select('project_name', 'total_budget', 'zakoura_contribution')
            ->get()
            ->filter(fn ($p) => $p->total_budget > 0)
            ->map(fn ($p) => [
                'name' => $p->project_name,
                'utilization' => round(($p->zakoura_contribution / $p->total_budget) * 100, 2),
            ]);

        return [
            'type' => 'bar',
            'title' => 'Utilisation du budget des projets',
            'unit' => '%',
            'labels' => $projects->pluck('name')->toArray(),
            'data' => $projects->pluck('utilization')->toArray(),
        ];
    }

    // ============ FINANCIAL METRICS ============

    private function getFinancialResourcesByType(): array
    {
        $resources = FinancialResource::get()
            ->groupBy('financial_type')
            ->map(fn ($group) => $group->sum('amount_received'));

        return [
            'type' => 'pie',
            'title' => 'Ressources financières par type',
            'unit' => 'DH',
            'labels' => $resources->keys()->toArray(),
            'data' => $resources->values()->map(fn ($v) => round($v, 2))->toArray(),
        ];
    }

    private function getFinancialMonthlyFlow(int $year): array
    {
        $months = [];
        $flows = [];

        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($year, $month, 1);
            $endDate = $startDate->clone()->endOfMonth();

            $flow = FinancialResource::whereBetween('slice_date', [$startDate, $endDate])
                ->orWhereBetween('created_at', [$startDate, $endDate])
                ->sum('amount_received');

            $months[] = $startDate->format('M');
            $flows[] = round((float) $flow, 2);
        }

        return [
            'type' => 'line',
            'title' => 'Flux financier mensuel',
            'unit' => 'DH',
            'frequency' => 'Mensuelle',
            'labels' => $months,
            'data' => $flows,
        ];
    }

    private function getTotalFinancialResources(): array
    {
        $total = FinancialResource::sum('amount_received');
        $byType = FinancialResource::get()
            ->groupBy('financial_type')
            ->map(fn ($group) => round($group->sum('amount_received'), 2));

        return [
            'type' => 'metric',
            'total_amount' => round((float) $total, 2),
            'by_type' => $byType->toArray(),
            'unit' => 'DH',
        ];
    }

    /**
     * Get multiple dashboards at once
     * @param array $types Array of dashboard types to fetch
     * @param array $options Configuration options
     * @return array
     */
    public function getMultipleDashboards(array $types, array $options = []): array
    {
        $dashboards = [];

        foreach ($types as $type) {
            $dashboards[$type] = $this->getDashboardData($type, $options);
        }

        return $dashboards;
    }
}
