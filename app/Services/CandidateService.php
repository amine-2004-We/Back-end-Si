<?php

namespace App\Services;

use App\Http\Requests\StoreCandidateRequest;
use App\Http\Requests\UpdateCandidateRequest;
use App\Models\Candidate;
use App\Models\Collaborator;
use App\Repositories\CandidateRepository;

class CandidateService
{
    protected CandidateRepository $candidateRepository;

    public function __construct(CandidateRepository $candidateRepository)
    {
        $this->candidateRepository = $candidateRepository;
    }
    public function getFilteredCandidates(array $params)
    {
        return $this->candidateRepository->getFilteredCandidates($params);
    }
    public function getCandidate(int $candidateId): Candidate
    {
        return $this->candidateRepository->getCandidate($candidateId);
    }
    public function delete(int $candidateId): void
    {
        $this->candidateRepository->delete($candidateId);
    }
      public function create(StoreCandidateRequest $request): Candidate
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }
        $candidate = $this->candidateRepository->create($data);
        return $candidate;
    }

     public function update(int $candidateId, UpdateCandidateRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('candidates', 'public');
        }

        $candidate = $this->candidateRepository->update($candidateId, $data);
        return $candidate;
    }
    public function restore(int $candidateId): void{
        $this->candidateRepository->restore($candidateId);
    }
    public function bulkDelete(array $candidateIds): void
    {
        $this->candidateRepository->bulkDelete($candidateIds);
    }
}