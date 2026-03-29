<?php

namespace App\Services;

use App\Http\Requests\StoreCollaboratorRequest;
use App\Http\Requests\StoreLeaveTypeStore;
use App\Models\LeaveType;
use App\Repositories\LeaveTypeRepository;

class LeaveTypeService
{
    protected LeaveTypeRepository $leaveTypeRepository;

    public function __construct(LeaveTypeRepository $leaveTypeRepository)
    {
        $this->leaveTypeRepository = $leaveTypeRepository;
    }

    public function all()
    {
        return $this->leaveTypeRepository->all();
    }
    public function allWithTrashed()
    {
        return $this->leaveTypeRepository->withTrashed();
    }

    public function show(string $leaveTypeId): LeaveType
    {
        return $this->leaveTypeRepository->show($leaveTypeId);
    }

    public function create(StoreLeaveTypeStore $request): LeaveType
    {
        $data = $request->validated();
        return $this->leaveTypeRepository->store($data);
    }

    public function update(string $leaveTypeId, StoreLeaveTypeStore $request): LeaveType{
        $data = $request->validated();
        return $this->leaveTypeRepository->update($leaveTypeId, $data);
    }

    public function delete(string $leaveTypeId): bool
    {
        return $this->leaveTypeRepository->delete($leaveTypeId);
    }

    public function restore(string $leaveTypeId): bool
    {
        return $this->leaveTypeRepository->restore($leaveTypeId);
    }
    public function bulkDelete(array $ids): bool
    {
        return LeaveType::query()->whereIn('id', $ids)->delete();
    }
}

