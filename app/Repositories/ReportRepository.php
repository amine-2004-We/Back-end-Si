<?php 

namespace App\Repositories;
use App\Models\Report;
use Illuminate\Support\Facades\Log;

class ReportRepository
{
     /**
     * Get filtered reports based on parameters.
     *
     * @param array $params
     * @return LengthAwarePaginator|Collection
     */
    public function getFilteredReports(array $filters)
    {
        $query = Report::query()->with([
            'task',
            'author',
            'creator',
        ]);

        if (!empty($filters['withTrashed']) && $filters['withTrashed'] == 'true') {
            $query->onlyTrashed();
        }

        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'ilike', '%' . $searchTerm . '%');
                   
            });
        }
        if (!empty($filters['filter']) && is_array($filters['filter'])) {
            foreach ($filters['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }
        if (!empty($filters['sort_by'])) {
            $direction = !empty($filters['sort_direction']) && in_array(strtolower($filters['sort_direction']), ['asc', 'desc'])
                ? $filters['sort_direction']
                : 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($filters['per_page'] ?? 10);


    }
    public function create(array $data): Report
    {
        return Report::create($data);
    }
    public function update(int $reportId, array $data): Report
{
    $report = Report::withTrashed()->findOrFail($reportId);
    $report->update($data);
    return $report;
}
    public function delete(int $reportId): ?bool
    {
        $report = Report::find($reportId);
        if ($report) {
            return $report->delete();
        }
        return null;
    }
    public function bulkDelete(array $ids): void
    {
        Report::whereIn('id', $ids)->delete();
    }
    public function restore(int $reportId): Report
    {
        $report = Report::withTrashed()->find($reportId);
        if ($report) {
            $report->restore();
        }
        return $report;
    }
    
public function show(int $reportId): Report
{
    Log::info("Fetching report with ID: $reportId");
    return Report::withTrashed()->with(['task', 'author', 'creator'])->findOrFail($reportId);
}
}