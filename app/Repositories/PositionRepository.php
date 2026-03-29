<?php

namespace App\Repositories;

use App\Models\Position;

class PositionRepository
{
    public function all()
    {
        return Position::query()->get();
    }
    public function allWithTrashed()
    {
        return Position::onlyTrashed()->get();
    }
    public function find($id):Position
    {
        return Position::query()->findOrFail($id);
    }
    public function store(array $data): Position
    {
        return Position::query()->create($data);
    }
    public function update(string $positionId, array $data): Position
    {
        $position = Position::query()->findOrFail($positionId);
        $position->update($data);
        return $position;
    }
    public function delete(string $positionId): bool
    {
        $position = Position::query()->findOrFail($positionId);
        return $position->delete();
    }
    public function restore(string $classTypeId): bool
    {
        $classTypeId = Position::onlyTrashed()->findOrFail($classTypeId);
        return $classTypeId->restore();

    }
    public function bulkDelete(array $ids): int
    {
        return Position::whereIn('id', $ids)->delete();
    }
}
