<?php

namespace App\Repositories;

use App\Models\PresenceSheet;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Exception;
use Illuminate\Support\Facades\Log;

class PresenceSheetRepository
{
    /**
     * Get a paginated list of presence sheets with filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator<PresenceSheet>
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = PresenceSheet::query()->with(['task', 'declarer', 'creator']);

            $query->withTrashed();

            if (isset($filters['activation_status']) && $filters['activation_status'] === 'inactive') {
                $query->onlyTrashed();
            } else {
                $query->whereNull('deleted_at');
            }

            if (!empty($filters['task_id'])) {
                $query->where('task_id', $filters['task_id']);
            }

            if (!empty($filters['globalSearch'])) {
                $searchTerm = $filters['globalSearch'];
                $query->where(function (Builder $q) use ($searchTerm) {
                    $q->where('event_date', 'like', "%{$searchTerm}%")
                      ->orWhereHas('task', fn(Builder $taskQuery) => $taskQuery->where('title', 'like', "%{$searchTerm}%"))
                      ->orWhereHas('declarer', fn(Builder $userQuery) => $userQuery->where('name', 'like', "%{$searchTerm}%"));
                });
            }

            $perPage = $filters['per_page'] ?? $perPage;
            $page = $filters['page'] ?? 1;

            return $query->latest('created_at')->paginate($perPage, ['*'], 'page', $page);

        } catch (Exception $e) {
            Log::error('Error paginating presence sheets: ' . $e->getMessage());
            return new LengthAwarePaginator([], 0, $perPage);
        }
    }

    /**
     * Toggle the activation status (soft-delete/restore) of presence sheets.
     *
     * @param array<int> $ids
     * @return array<array{id: int, success: bool, message: string}>
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $sheet = PresenceSheet::withTrashed()->find($id);

            if (!$sheet) {
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Feuille de présence non trouvée.'];
                continue;
            }

            try {
                if ($sheet->trashed()) {
                    $sheet->restore();
                    $message = 'Feuille de présence restaurée avec succès.';
                } else {
                    $sheet->delete();
                    $message = 'Feuille de présence désactivée avec succès.';
                }
                $results[] = ['id' => $id, 'success' => true, 'message' => $message];
            } catch (Exception $e) {
                Log::error("Error toggling activation for presence sheet ID {$id}: " . $e->getMessage());
                $results[] = ['id' => $id, 'success' => false, 'message' => 'Erreur lors du changement de statut.'];
            }
        }
        return $results;
    }
}
