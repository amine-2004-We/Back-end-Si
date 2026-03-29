<?php

namespace App\Services;

use App\Enums\ModuleEvaluationStatus;
use App\Enums\ModuleEvaluationType;
use App\Models\ModuleEvaluation;
use App\Repositories\ModuleEvaluationRepository;
use App\Traits\UploadFileTrait; // 1. Import the trait
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleEvaluationService
{
    use UploadFileTrait; // 2. Use the trait

    public function __construct(
        protected ModuleEvaluationRepository $repo
    ) {}

    /**
     * @param  array  $filters
     * @param  int    $perPage
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filters = $this->normalizeFilters($filters);
        return $this->repo->paginate($filters, $perPage);
    }

    /**
     * @param  array    $filters
     * @param  int|null $limit
     */
    public function list(array $filters = [], ?int $limit = null): Collection
    {
        $filters = $this->normalizeFilters($filters);
        return $this->repo->list($filters, $limit);
    }

    /**
     * Single evaluation with relations.
     */
    public function get(int $id): ?ModuleEvaluation
    {
        return $this->repo->find($id);
    }

    /**
     * Single evaluation with trashed if needed.
     */
    public function getWithTrashed(int $id): ?ModuleEvaluation
    {
        return $this->repo->findWithTrashed($id);
    }

    /**
     * @param  array   $data     Fillable fields
     * @param  Request $request  The full request object to access files
     */
    public function create(array $data, Request $request): ModuleEvaluation
    {
        return DB::transaction(function () use ($data, $request) {
            if ($request->hasFile('attachments')) {
                $paths = [];
                foreach ($request->file('attachments') as $file) {
                    $paths[] = $this->uploadPublicFile($file, 'module_evaluations');
                }
                $data['attachments'] = $paths;
            }

            $data = $this->coerceEnums($data);
            return $this->repo->create($data);
        });
    }

    /**
     * Update a module evaluation and handle file changes.
     *
     * @param  int     $id
     * @param  array   $data       Fields to update
     * @param  Request $request    The full request object to access files
     */
    public function update(int $id, array $data, Request $request): ModuleEvaluation
    {
        return DB::transaction(function () use ($id, $data, $request) {
            $evaluation = $this->repo->find($id);

            $existingAttachments = $evaluation->attachments ?? [];

            // 1. Remove files marked for deletion
            $removedPaths = $request->input('removed_attachments', []);
            if (!empty($removedPaths)) {
                foreach ($removedPaths as $path) {
                    $this->deletePublicFile($path);
                }
            }
            $remainingPaths = array_diff($existingAttachments, $removedPaths);

            // 2. Add new uploaded files
            $newPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $newPaths[] = $this->uploadPublicFile($file, 'module_evaluations');
                }
            }

            // 3. Merge and set the final array of attachment paths
            $data['attachments'] = array_merge(array_values($remainingPaths), $newPaths);
            
            $data = $this->coerceEnums($data);
            return $this->repo->update($id, $data);
        });
    }

    /**
     * Update status (and optional trainer comments).
     *
     * @param  int                         $id
     * @param  ModuleEvaluationStatus|string $status
     * @param  string|null                 $trainerComments
     */
    public function updateStatus(
        int $id,
        ModuleEvaluationStatus|string $status,
        ?string $trainerComments = null
    ): ModuleEvaluation {
        return $this->repo->updateStatus($id, $status, $trainerComments);
    }

    /**
     * Soft-delete many evaluations.
     *
     * @param  array<int> $ids
     */
    public function deleteMany(array $ids): int
    {
        return $this->repo->deleteMany($ids);
    }

    /**
     * Restore a soft-deleted evaluation.
     */
    public function restore(int $id): ModuleEvaluation
    {
        return $this->repo->restore($id);
    }

    /**
     * Provide static options (enums -> labels).
     */
    public function getOptions(): array
    {
        return $this->repo->getOptions();
    }

    /**
     * @param array $filters
     * @return array
     */
    protected function normalizeFilters(array $filters): array
    {
        $out = [];

        if (!empty($filters['search'])) {
            $out['search'] = trim((string) $filters['search']);
        }

        foreach (['module_id', 'participant_id', 'trainer_id', 'created_by_id'] as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $out[$key] = (int) $filters[$key];
            }
        }

        foreach (['evaluated_from', 'evaluated_to'] as $key) {
            if (!empty($filters[$key])) {
                $out[$key] = (string) $filters[$key];
            }
        }

        if (!empty($filters['status'])) {
            $out['status'] = (string) $filters['status'];
        }

        if (!empty($filters['evaluation_type'])) {
            $out['evaluation_type'] = (string) $filters['evaluation_type'];
        }

        if (!empty($filters['with_trashed'])) {
            $out['with_trashed'] = (bool) $filters['with_trashed'];
        }

        return $out;
    }

    /**
     * Coerce enum-able fields to raw string values before persistence.
     * Accepts either native enum case or string.
     */
    protected function coerceEnums(array $data): array
    {
        if (array_key_exists('status', $data) && $data['status'] instanceof ModuleEvaluationStatus) {
            $data['status'] = $data['status']->value;
        }

        if (array_key_exists('evaluation_type', $data) && $data['evaluation_type'] instanceof ModuleEvaluationType) {
            $data['evaluation_type'] = $data['evaluation_type']->value;
        }

        return $data;
    }
}
