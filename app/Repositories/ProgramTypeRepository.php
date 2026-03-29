<?php

namespace App\Repositories;

use App\Models\ProgramType;

class ProgramTypeRepository 
{
   public function getAll($request)
    {
        $query = ProgramType::query();

        // Apply filters from the request if any
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->get();
    }

    public function findById(int $id): ?ProgramType
    {
        return ProgramType::find($id);
    }

    public function create(array $data): ?ProgramType
    {
        return ProgramType::create($data);
    }

    public function update(array $data, int $id): ?ProgramType
    {
        $programType = ProgramType::find($id);
        if ($programType) {
            $programType->update($data);
            return $programType;
        }
        return null;
    }

    public function delete(int $id): ?ProgramType
    {
        $programType = ProgramType::find($id);
        if ($programType) {
            $programType->delete();
            return $programType;
        }
        return null;
    }

    public function restore(int $id): ?ProgramType
    {
        $programType = ProgramType::withTrashed()->find($id);
        if ($programType && $programType->trashed()) {
            $programType->restore();
            return $programType;
        }
        return null;
    }
}