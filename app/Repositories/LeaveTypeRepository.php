<?php

namespace App\Repositories;

use App\Models\LeaveType;

class LeaveTypeRepository
{
    public function all()
    {
        return LeaveType::query()->get();
    }
    public function withTrashed()
    {
        return LeaveType::onlyTrashed()->get();
    }
    public function show(string $leaveTypeId): LeaveType
    {
        return LeaveType::query()->findOrFail($leaveTypeId);
    }
    public function store(array $data): LeaveType
    {
        return LeaveType::query()->create($data);
    }
    public function update(string $leaveTypeId ,array $data): LeaveType
    {
        $leaveType = LeaveType::query()->findOrFail($leaveTypeId);
        $leaveType->update($data);
        return $leaveType;
    }

    public function delete(string $leaveTypeId): bool
    {
        $leaveType = LeaveType::query()->findOrFail($leaveTypeId);
        return $leaveType->delete();
    }
    public function restore(string $leaveTypeId): bool
    {
        $leaveType = LeaveType::onlyTrashed()->findOrFail($leaveTypeId);
        return $leaveType->restore();

    }
    public function bulkDelete(array $ids): bool
    {
       return LeaveType::query()->whereIn('id', $ids)->delete();
    }
}
