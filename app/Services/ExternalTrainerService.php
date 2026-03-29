<?php

namespace App\Services;

use App\Models\ExternalTrainer;
use App\Models\Trainer;
use App\Repositories\ExternalTrainerRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class ExternalTrainerService
{
    public function __construct(protected ExternalTrainerRepository $repository, protected Trainer $trainerModel) {}

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->getPaginated($filters, $perPage);
    }

    public function findById(int $id): ?ExternalTrainer
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): ExternalTrainer
    {
        return DB::transaction(function () use ($data) {
            // The base trainer holds shared properties
            $baseTrainer = $this->trainerModel->create([
                'type' => 'external',
                'created_by_id' => Auth::id(),
                'interventions_evaluation' => $data['interventions_evaluation'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'is_available' => $data['is_available'] ?? true,
            ]);

            // The external trainer holds specific properties
            $externalTrainerData = [
                'trainer_id' => $baseTrainer->id,
                'full_name' => $data['full_name'],
                'affiliation_type' => $data['affiliation_type'],
                'cabinet_id' => $data['cabinet_id'] ?? null,
                'phone' => $data['phone'],
                'email' => $data['email'],
                'created_by' => Auth::id(),
                'trainer_identifier' => 'EXT-' . str_pad($baseTrainer->id, 5, '0', STR_PAD_LEFT),
            ];

            if (isset($data['cv'])) {
                $externalTrainerData['cv_path'] = $data['cv']->store('trainers/cvs', 'public');
            }

            $externalTrainer = ExternalTrainer::create($externalTrainerData);

            return $externalTrainer->load($this->repository->defaultWith);
        });
    }

    public function update(ExternalTrainer $externalTrainer, array $data): ExternalTrainer
    {
        return DB::transaction(function () use ($externalTrainer, $data) {
            // Update shared properties on the base trainer model
            $trainerData = Arr::only($data, ['interventions_evaluation', 'remarks', 'is_available']);
            if (!empty($trainerData)) {
                $externalTrainer->trainer->update($trainerData);
            }

            // Update specific properties on the external trainer model
            $externalTrainerData = Arr::only($data, ['full_name', 'affiliation_type', 'cabinet_id', 'phone', 'email']);

            if (isset($data['cv'])) {
                if ($externalTrainer->cv_path) {
                    Storage::disk('public')->delete($externalTrainer->cv_path);
                }
                $externalTrainerData['cv_path'] = $data['cv']->store('trainers/cvs', 'public');
            }
            
            if (!empty($externalTrainerData)) {
                $externalTrainer->update($externalTrainerData);
            }

            return $externalTrainer->fresh($this->repository->defaultWith);
        });
    }

    /**
     * Soft delete an external trainer.
     *
     * @param ExternalTrainer $externalTrainer
     * @return bool
     */
    public function delete(ExternalTrainer $externalTrainer): bool
    {
        // The repository's delete method handles the soft delete.
        return $this->repository->delete($externalTrainer) ?? false;
    }

    /**
     * Toggle the activation status for a list of external trainers.
     * This will soft delete or restore them.
     *
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        return $this->repository->toggleActivation($ids);
    }
}
