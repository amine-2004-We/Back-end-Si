<?php

namespace App\Services;

use App\Models\CompetencyGrid;
use App\Repositories\CompetencyGridRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CompetencyGridService
{
    protected CompetencyGridRepository $competencyGridRepository;

    public function __construct(CompetencyGridRepository $competencyGridRepository)
    {
        $this->competencyGridRepository = $competencyGridRepository;
    }

    public function getAll(Request $request): mixed
    {
        $filters = $request->only([
            'grid_type',
            'grading_scheme',
            'lifecycle_status',
            'created_by_id',
            'from_date',
            'to_date',
            'search',
            'is_active',
            'code',
            'per_page',
        ]);

        return $this->competencyGridRepository->withFilters($filters);
    }

    public function getAllWithoutPagination(): mixed
    {
        return $this->competencyGridRepository->all();
    }

    public function show(int $id): mixed
    {
        return $this->competencyGridRepository->find($id);
    }

    public function create(array $data): CompetencyGrid
    {
        return DB::transaction(function () use ($data) {
            $criteria = $data['criteria'] ?? [];
            unset($data['criteria']);

            $grid = $this->competencyGridRepository->create($data);

            if (!empty($criteria)) {
                $this->competencyGridRepository->syncCriteria($grid, $criteria);
            }

            return $grid->load(['criteria', 'creator']);
        });
    }

    /**
     * @param array $data
     * @param CompetencyGrid $grid
     * @return CompetencyGrid
     */
    public function update(array $data, CompetencyGrid $grid): CompetencyGrid
    {
        return DB::transaction(function () use ($data, $grid) {
            $grid->fill(Arr::except($data, ['criteria']));
            $grid->save();

            if (array_key_exists('criteria', $data) && is_array($data['criteria'])) {
                $criterionIds = collect($data['criteria'])
                    ->pluck('criterion_id')
                    ->filter()
                    ->map(fn($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                $grid->criteria()->sync($criterionIds);
            }

            return $grid->load(['criteria', 'creator']);
        });
    }


    public function delete($grid)
    {
        return $this->competencyGridRepository->delete($grid);
    }

    public function bulkDestroy($grids): int
    {
        return $this->competencyGridRepository->bulkDelete($grids->pluck('id')->toArray());
    }

    public function restore(int $id): CompetencyGrid
    {
        try {
            return $this->competencyGridRepository->restore($id)->load(['criteria', 'creator']);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }

    public function findByIds(array $ids)
    {
        return CompetencyGrid::whereIn('id', $ids)->get();
    }

    /**
     * @return array
     */
    public function getFormOptions(): array
    {
        return [
            'grid_types' => [
                'evaluation' => 'Évaluation',
                'impact'     => 'Impact',
                'follow_up'  => 'Suivi',
            ],
            'grading_schemes' => [
                'status'  => 'Statut (Acquis/Non acquis)',
                'scale10' => 'Échelle sur 10',
                'scale20' => 'Échelle sur 20',
            ],
            'lifecycle_statuses' => [
                'in_progress' => 'En cours',
                'validated'   => 'Validée',
                'archived'    => 'Archivée',
            ],
        ];
    }
}
