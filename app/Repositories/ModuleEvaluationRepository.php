<?php

namespace App\Repositories;

use App\Enums\ModuleEvaluationStatus;
use App\Enums\ModuleEvaluationType;
use App\Models\ModuleEvaluation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModuleEvaluationRepository
{
    protected string $attachmentsDir = 'module_evaluations';

    /**
     *  @return Builder
     */
    public function baseQuery(): Builder
    {
        return ModuleEvaluation::query()->withDisplayRelations();
    }

    /**
     * @param Builder $q
     * @param array $filters
     * @return Builder
     */
    public function applyFilters(Builder $q, array $filters): Builder
    {
        if (!empty($filters['with_trashed'])) {
            $q->withTrashed();
        }

        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $q->where(function (Builder $qq) use ($term) {
                $qq->where('evaluation_id', 'LIKE', "%{$term}%");
            });
        }

        if (!empty($filters['module_id'])) {
            $q->where('module_id', (int) $filters['module_id']);
        }

        if (!empty($filters['participant_id'])) {
            $q->where('participant_id', (int) $filters['participant_id']);
        }

        if (!empty($filters['trainer_id'])) {
            $q->where('trainer_id', (int) $filters['trainer_id']);
        }

        if (!empty($filters['status'])) {
            $q->where('status', (string) $filters['status']);
        }

        if (!empty($filters['evaluation_type'])) {
            $q->where('evaluation_type', (string) $filters['evaluation_type']);
        }

        if (!empty($filters['evaluated_from'])) {
            $q->whereDate('evaluated_at', '>=', $filters['evaluated_from']);
        }

        if (!empty($filters['evaluated_to'])) {
            $q->whereDate('evaluated_at', '<=', $filters['evaluated_to']);
        }

        if (!empty($filters['created_by_id'])) {
            $q->where('created_by_id', (int) $filters['created_by_id']);
        }

        if(!empty($filters['is_active'])){
            if($filters['is_active'] == 'true'){
                $q->whereNull('deleted_at');
            }else{
                $q->whereNotNull('deleted_at');
            }
        }
        return $q;
    }

    /**
     * Paginate evaluations with filters and sensible ordering.
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $q = $this->baseQuery();
        $this->applyFilters($q, $filters);

        $q->orderByRaw('deleted_at IS NOT NULL')
          ->orderByDesc('created_at');

        return $q->paginate($perPage)->appends($filters);
    }

    /**
     * @param int $id
     * @return ModuleEvaluation|null
     */
    public function find(int $id): ?ModuleEvaluation
    {
        return $this->baseQuery()->find($id);
    }

    /**
     * @param int $id
     * @return ModuleEvaluation|null
     */
    public function findWithTrashed(int $id): ?ModuleEvaluation
    {
        return ModuleEvaluation::withTrashed()->withDisplayRelations()->find($id);
    }

    /**
     * @param array $filters
     * @param int|null $limit
     * @return Collection
     */
    public function list(array $filters = [], ?int $limit = null): Collection
    {
        $q = $this->baseQuery();
        $this->applyFilters($q, $filters);

        $q->orderByDesc('created_at');

        if ($limit !== null) {
            $q->limit($limit);
        }

        return $q->get();
    }

   /**
    * @param array $data
    * @param UploadedFile|array|null $files
    * @return ModuleEvaluation
    */
    public function create(array $data, UploadedFile|array|null $files = null): ModuleEvaluation
    {
        return DB::transaction(function () use ($data, $files) {
            /** @var ModuleEvaluation $model */
            $model = new ModuleEvaluation($data);

            $model->save();

            if ($files) {
                $stored = $this->storeUploadedFiles($files);
                $current = $model->attachments ?: [];
                $model->attachments = array_values(array_unique(array_merge($current, $stored)));
                $model->save();
            }

            return $this->find($model->id);
        });
    }

    /**
     * Update an evaluation.
     *
     * @param int $id
     * @param array $data
     * @param UploadedFile|array|null $addFiles
     * @param array<string> $removePaths
     * @param bool $replaceAll
     */
    public function update(
        int $id,
        array $data,
        UploadedFile|array|null $addFiles = null,
        array $removePaths = [],
        bool $replaceAll = false
    ): ModuleEvaluation {
        return DB::transaction(function () use ($id, $data, $addFiles, $removePaths, $replaceAll) {
            /** @var ModuleEvaluation $model */
            $model = ModuleEvaluation::query()->findOrFail($id);
            $model->fill($data);
            $model->save();

            // Manage attachments JSON
            $current = $model->attachments ?: [];

            if ($replaceAll) {
                // delete all current before replacing
                $this->deleteStoredFiles($current);
                $current = [];
            } else {
                // remove specific ones
                if (!empty($removePaths)) {
                    $toRemove = array_values(array_intersect($current, $removePaths));
                    if ($toRemove) {
                        $this->deleteStoredFiles($toRemove);
                        $current = array_values(array_diff($current, $toRemove));
                    }
                }
            }

            if ($addFiles) {
                $stored = $this->storeUploadedFiles($addFiles);
                $current = array_values(array_unique(array_merge($current, $stored)));
            }

            if ($model->attachments !== $current) {
                $model->attachments = $current;
                $model->save();
            }

            return $this->find($model->id);
        });
    }

    public function updateStatus(int $id, ModuleEvaluationStatus|string $status, ?string $trainerComments = null): ModuleEvaluation
    {
        /** @var ModuleEvaluation $model */
        $model = ModuleEvaluation::query()->findOrFail($id);

        $model->status = (string) $status;
        if ($trainerComments !== null) {
            $model->trainer_comments = $trainerComments;
        }
        $model->save();

        return $this->find($model->id);
    }

    public function deleteMany(array $ids): int
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) return 0;

        return (int) ModuleEvaluation::whereIn('id', $ids)->delete();
    }

    public function restore(int $id): ModuleEvaluation
    {
        /** @var ModuleEvaluation $model */
        $model = ModuleEvaluation::withTrashed()->findOrFail($id);
        $model->restore();

        return $this->find($model->id);
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        $statusMap = method_exists(ModuleEvaluationStatus::class, 'labels')
            ? ModuleEvaluationStatus::labels()
            : collect(ModuleEvaluationStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name])->all();

        $typeMap = method_exists(ModuleEvaluationType::class, 'labels')
            ? ModuleEvaluationType::labels()
            : collect(ModuleEvaluationType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name])->all();

        return [
            'statuses' => $statusMap,
            'evaluation_types' => $typeMap,
        ];
    }

    /**
     * @param UploadedFile|array $files
     * @return array
     */
    protected function storeUploadedFiles(UploadedFile|array $files): array
    {
        $files = is_array($files) ? $files : [$files];
        $stored = [];

        $subdir = date('Y/m/d');
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) continue;

            $path = $file->store($this->attachmentsDir . '/' . $subdir, ['disk' => 'public']);
            if ($path) {
                $stored[] = $path;
            }
        }

        return $stored;
    }

    /**
     * @param array $paths
     */
    protected function deleteStoredFiles(array $paths): void
    {
        if (empty($paths)) return;

        $paths = array_values(array_unique(array_filter($paths)));
        Storage::disk('public')->delete($paths);
    }
}
