<?php

namespace App\Repositories;
use App\Models\RecruitmentRequest;


class RecruitmentRequestRepository
{
    /**
     * get filtered recruitment requests with pagination
     */
    public function getFilteredRecruitmentRequests(array $filters)
    {
       
        $query = RecruitmentRequest::query();
        $query = RecruitmentRequest::query()->with([
        'creator',                            // Load the user who created the request
        'creator.collaborator',               // Load creator's HR profile
        'creator.collaborator.superior.user'  // Load the creator's manager details
    ]);
        // Apply withTrashed or onlyTrashed based on the parameter.
        if (!empty($filters['withTrashed']) && $filters['withTrashed'] == 'true') {
            $query->onlyTrashed();
        }

        // Handle Global Search
        if (!empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                
                $q->where('company_name', 'ilike', '%' . $searchTerm . '%')
                    ->orWhere('email', 'ilike', '%' . $searchTerm . '%');
            });
        }

        // Handle Specific Column Filters
        if (!empty($filters['filter']) && is_array($filters['filter'])) {
            foreach ($filters['filter'] as $key => $value) {
                $query->where($key, $value);
            }
        }

        // Handle Sorting
        if (!empty($filters['sort_by'])) {
            $direction = !empty($filters['sort_direction']) && in_array(strtolower($filters['sort_direction']), ['asc', 'desc'])
                ? $filters['sort_direction']
                : 'asc';
            $query->orderBy($filters['sort_by'], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Handle Pagination
        $perPage = !empty($filters['per_page']) ? (int) $filters['per_page'] : 10;

        return $query->paginate($perPage);
    }
    public function find($id)
    {
        return RecruitmentRequest::withTrashed()->find($id);
    }
    public function create(array $data): RecruitmentRequest
    {
        return RecruitmentRequest::create($data);
    }
    public function update(int $id, array $data): RecruitmentRequest
    {
        $recruitmentRequest = RecruitmentRequest::withTrashed()->findOrFail($id);
        $recruitmentRequest->update($data);
        return $recruitmentRequest;
    }
    public function delete(int $id): void
    {
        $recruitmentRequest = RecruitmentRequest::findOrFail($id);
        $recruitmentRequest->delete();
    }
    public function bulkDelete(array $ids): void
    {
        RecruitmentRequest::whereIn('id', $ids)->delete();
    }
    public function restore(int $id): RecruitmentRequest
    {
        $recruitmentRequest = RecruitmentRequest::withTrashed()->find($id);
        if ($recruitmentRequest) {
            $recruitmentRequest->restore();
        }
        return $recruitmentRequest;
    }
}