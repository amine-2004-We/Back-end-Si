<?php

namespace App\Repositories;

use App\Models\Candidate;
use App\Models\Collaborator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rules\Can;

class CandidateRepository
{
    public function getFilteredCandidates(array $filters): LengthAwarePaginator
    {
        

         if (!empty($filters['with_trashed']) && $filters['with_trashed'] == 'true') {
            $query = Candidate::onlyTrashed();
        } else {
            $query = Candidate::query();
        }
       

        if (!empty($filters['last_name'])) {
            $query->where('last_name', 'ilike', '%' . $filters['last_name'] . '%');
        }


        if (!empty($filters['cin'])) {
            $query->where('cin', 'ilike', '%' . $filters['cin'] . '%');
        }

        if (!empty($filters['cnss'])) {
            $query->where('cnss', 'ilike', '%' . $filters['cnss'] . '%');
        }

        if (!empty($filters['candidate_status'])) {
            $query->where('status', $filters['candidate_status']);
        }
         if (!empty($filters['email'])) {
            $query->where('email', $filters['email']);
        }

        if (!empty($filters['candidate_source'])) {
            $query->where('source', $filters['candidate_source']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if (isset($filters['activation_status']) && $filters['activation_status'] === 'all') {
            $query->orderByRaw('deleted_at IS NOT NULL');
        }

        $query->orderBy($sortBy, $sortDirection);

        $perPage = $filters['per_page'] ?? 10;
        $page = $filters['page'] ?? 1;

        return $query->paginate($perPage, ['*'], 'page', $page);
    }
    public function create(array $data): Candidate
    {
        return Candidate::create($data);
    }
    public function update(string $candidateId, array $data): Candidate
    {
        $candidate = Candidate::findOrFail($candidateId);
        $candidate->update($data);
        return $candidate;
    }
    public function delete(string $candidateId): void
    {
        $candidate = Candidate::withTrashed()->find($candidateId);
        if ($candidate) {
            $candidate->delete();
        } else {
            throw new ModelNotFoundException("Candidate with ID {$candidateId} not found.");
        }
    }
    public function getCandidate(int $candidateId): Candidate
    {
        $candidate = Candidate::withTrashed()->find($candidateId);
        if (!$candidate) {
            throw new ModelNotFoundException("Candidate with ID {$candidateId} not found.");
        }
        return $candidate;
    }
    public function restore(int $candidateId): void
    {
        $candidate = Candidate::onlyTrashed()->find($candidateId);
        if ($candidate) {
            $candidate->restore();
        } else {
            throw new ModelNotFoundException("Candidate with ID {$candidateId} not found.");
        }
    }
    public function bulkDelete(array $candidateIds): void
    {
        $candidates = Candidate::whereIn('id', $candidateIds)->get();

        if ($candidates->isEmpty()) {
            throw new ModelNotFoundException("No candidates found for the provided IDs.");
        }

        foreach ($candidates as $candidate) {
            $candidate->delete();
        }
    }
}