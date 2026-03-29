<?php
namespace App\Services;
use App\Repositories\TypeDepartmentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\TypeDepartement;
use Mockery\Matcher\Type;

class TypeDepartmentService
{
    protected $typeDepartmentRepository;
    public function __construct(TypeDepartmentRepository $typeDepartmentRepository)
    {
        $this->typeDepartmentRepository = $typeDepartmentRepository;
    }
    public function getFilteredTypeDepartments(array $params): LengthAwarePaginator
    {
        return $this->typeDepartmentRepository->getFilteredDepartments($params);
    }


    public function createTypeDepartment(array $data): TypeDepartement
    {
        return $this->typeDepartmentRepository->create($data);
    }

    public function findTypeDepartment(int $id): ?TypeDepartement
    {
        return $this->typeDepartmentRepository->find($id);
    }

    public function updateTypeDepartment(int $id, array $data): TypeDepartement
    {
        return $this->typeDepartmentRepository->update($id, $data);
    }

    public function deleteTypeDepartment(int $id): bool
    {
        return $this->typeDepartmentRepository->delete($id);
    }
    public function restoreTypeDepartment(int $id): TypeDepartement
    {
        return $this->typeDepartmentRepository->restore($id);
    }
    public function bulkDeleteTypeDepartments(array $ids): int
    {
        return $this->typeDepartmentRepository->bulkDelete($ids);
    }
}