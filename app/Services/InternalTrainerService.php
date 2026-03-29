<?php

namespace App\Services;

use App\Models\InternalTrainer;
use App\Models\Trainer;
use App\Repositories\InternalTrainerRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * class InternalTrainerService
 */
class InternalTrainerService
{
    /**
     * @var InternalTrainerRepository
     */
    protected InternalTrainerRepository $internalTrainerRepository;

    /**
     * @param InternalTrainerRepository $internalTrainerRepository
     */
    public function __construct(InternalTrainerRepository $internalTrainerRepository)
    {
        $this->internalTrainerRepository = $internalTrainerRepository;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getAll(Request $request): mixed
    {
        $filters = $request->only(['collaborator_id', 'trainer_id', 'is_active', 'per_page']);
        $perPage = $filters['per_page'] ?? 10;

        return $this->internalTrainerRepository->withFilters($filters)->paginate($perPage);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function show(int $id): mixed
    {
        return $this->internalTrainerRepository->find($id);
    }

    /**
     * @param array $data
     * @return InternalTrainer
     */
    public function create(array $data): InternalTrainer
    {
        return DB::transaction(function () use ($data) {
            $trainer = Trainer::create([
                'is_available' => $data['is_available'] ?? true,
                'interventions_evaluation' => $data['interventions_evaluation'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'type' => 'internal',
            ]);

            $internalTrainerData = [
                'collaborator_id' => $data['collaborator_id'],
                'trainer_id' => $trainer->id,
            ];

            return $this->internalTrainerRepository->create($internalTrainerData);
        });
    }

    /**
     * @param int $id
     * @param array $data
     * @return InternalTrainer
     */
    public function update(int $id, array $data): InternalTrainer
    {
        return DB::transaction(function () use ($id, $data) {
            $internalTrainer = $this->internalTrainerRepository->find($id);

            if ($internalTrainer->trainer) {
                $trainerData = [];
                if (isset($data['is_available'])) {
                    $trainerData['is_available'] = $data['is_available'];
                }
                if (isset($data['interventions_evaluation'])) {
                    $trainerData['interventions_evaluation'] = $data['interventions_evaluation'];
                }
                if (isset($data['remarks'])) {
                    $trainerData['remarks'] = $data['remarks'];
                }
                if (!empty($trainerData)) {
                    $internalTrainer->trainer->update($trainerData);
                }
            }

            $internalTrainer->update($data);

            return $internalTrainer;
        });
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        return $this->internalTrainerRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDestroy(array $ids): mixed
    {
        return $this->internalTrainerRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id): mixed
    {
        try {
            return $this->internalTrainerRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
