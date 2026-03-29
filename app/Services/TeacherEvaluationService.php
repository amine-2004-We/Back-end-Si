<?php
namespace App\Services;

use App\Models\TeacherEvaluation;
use App\Repositories\TeacherEvaluationRepository;

class TeacherEvaluationService
{
    private $repository;

    public function __construct(TeacherEvaluationRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getFiltered(array $params)
    {
        return $this->repository->getFiltered($params);
    }

    public function create(array $data)
    {
        //created by
        $data['created_by'] = auth()->id();
        return $this->repository->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->repository->bulkDelete($ids);
    }

    public function restore(int $id): TeacherEvaluation
    {
        return $this->repository->restore($id);
    }
}