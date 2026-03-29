<?php

namespace App\Services;

use App\Models\Absence;
use App\Repositories\AbsenceRepository;
use App\Traits\UploadFileTrait;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Requests\UpdateAbsenceRequest;

/**
 * class AbsenceService
 */
class AbsenceService
{
    use UploadFileTrait;

    /**
     * @var AbsenceRepository
     */
    public AbsenceRepository $absenceRepository;

    /**
     * @param AbsenceRepository $absenceRepository
     */
    public function __construct(AbsenceRepository $absenceRepository)
    {
        $this->absenceRepository = $absenceRepository;
    }

    /**
     * @return mixed
     */
    public function getAll(Request $request): mixed
    {
        $filters = $request->only([
            'collaborator_id',
            'is_active',
            'reason',
            'absence_type',
            'absence_status',
            'start_date',
            'start_date_from',
            'start_date_to',
            'start_time',
            'start_time_from',
            'start_time_to',
            'end_date',
            'end_date_from',
            'end_date_to',
            'end_time',
            'end_time_from',
            'end_time_to',
            'per_page',

        ]);
        return $this->absenceRepository->withFilters($filters);
    }

    /**
     * @return mixed
     */
    public function getAllWithoutPagination(): mixed
    {
        return $this->absenceRepository->allWithoutPagination();
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function show(int $id): mixed
    {
        return $this->absenceRepository->find($id);
    }

   /**
     * @param array $data
     * @param StoreAbsenceRequest $request
     * @return mixed
     */
    public function create(array $data, StoreAbsenceRequest $request): mixed
    {
        if ($request->hasFile('justification_path')) {
            $filePath = $this->uploadPublicFile($request->file('justification_path'), 'absences/justifications');
            $data['justification_path'] = $filePath;
        }

        return $this->absenceRepository->create($data);
    }

    /**
     * @param int $id
     * @param array $data
     * @param UpdateAbsenceRequest $request
     * @return Absence
     */
    public function update(int $id, array $data, UpdateAbsenceRequest $request): Absence
    {
        if ($request->hasFile('justification_path')) {
            $absence = $this->absenceRepository->find($id);
            $oldPath = $absence->justification_path;

            $newPath = $this->uploadPublicFile($request->file('justification_path'), 'absences/justifications');
            $data['justification_path'] = $newPath;

            if ($oldPath) {
                $this->deletePublicFile($oldPath);
            }
        }

        return $this->absenceRepository->update($data, $id);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function delete(int $id): mixed
    {
        return $this->absenceRepository->delete($id);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function bulkDestroy(array $ids): mixed
    {
        return $this->absenceRepository->bulkDelete($ids);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function restore(int $id): mixed
    {
        try {
            return $this->absenceRepository->restore($id);
        } catch (Exception $e) {
            abort(409, $e->getMessage());
        }
    }
}
