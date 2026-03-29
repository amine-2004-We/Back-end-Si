<?php
namespace App\Repositories;

use App\Models\TeacherEvaluation;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;


class TeacherEvaluationRepository
{

     /**
     * Get filtered reports based on parameters.
     *
     * @param array $params
     * @return LengthAwarePaginator|Collection
     */
    public function getFiltered(array $params): LengthAwarePaginator
    {

        $query= TeacherEvaluation::query()->with([
            'program',
            'programType',
            'teacher',
            'unit',
            'creator'
        ]);
        if (!empty($params['withTrashed']) && $params['withTrashed'] == 'true') {
            $query->onlyTrashed();
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->orWhere('status', 'ilike', '%' . $searchTerm . '%')
                  ->orWhereHas('teacher', function ($subQ) use ($searchTerm) {
                      $subQ->where('first_name', 'ilike', '%' . $searchTerm . '%')
                        ->orWhere('last_name', 'ilike', '%' . $searchTerm . '%');
                  });
            });
        }
        if (!empty($params['filter']) && is_array($params['filter'])) {
            foreach ($params['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }

        if (!empty($params['sort_by'])) {
            $query->orderBy($params['sort_by'], $params['sort_direction'] ?? 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
       return $query->paginate($filters['per_page'] ?? 10);
        
    }

    public function create(array $data): TeacherEvaluation
    {
        return TeacherEvaluation::create($data);
    }

    public function update(int $id, array $data): TeacherEvaluation
    {
        $teacherEvaluation =  TeacherEvaluation::withTrashed()->findOrFail($id);
        $teacherEvaluation->update($data);
        return $teacherEvaluation;
    }
    public function delete(int $id): void
    {
        $teacherEvaluation = $this->find($id);
        $teacherEvaluation->delete();
    }

    public function find(int $id): TeacherEvaluation
    {
        return TeacherEvaluation::withTrashed()->findOrFail($id);
    }

    public function bulkDelete(array $ids): void
    {
        TeacherEvaluation::whereIn('id', $ids)->delete();
    }

    public function restore(int $id): TeacherEvaluation
    {
        $teacherEvaluation = TeacherEvaluation::onlyTrashed()->findOrFail($id);
        $teacherEvaluation->restore();
        return $teacherEvaluation;
    }

}

