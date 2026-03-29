<?php

namespace App\Services;
use App\Models\RecruitmentRequest;
use App\Repositories\RecruitmentRequestRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;
class RecruitmentRequestService
{
    protected $recruitmentRequestRepository;
    public function __construct(RecruitmentRequestRepository $recruitmentRequestRepository)
    {
        $this->recruitmentRequestRepository = $recruitmentRequestRepository;
    }

    public function getFilteredRecruitmentRequests(array $filters): LengthAwarePaginator
    {
        return $this->recruitmentRequestRepository->getFilteredRecruitmentRequests($filters);
    }

    public function createRecruitmentRequest(array $data, $file = null): RecruitmentRequest
    {
        if ($file) {
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = $originalName . '_' . time() . '.' . $extension;
            $path = $file->storeAs('attechments_job_files', $filename, 'public');
            $data['job_file'] = $path;
        }
        // assign the currently authenticated user  as the  creator 
        $data['created_by'] = auth()->id();

        return $this->recruitmentRequestRepository->create($data);
    }

       
    

    public function getRecruitmentRequestById(int $id): RecruitmentRequest
    {
        return $this->recruitmentRequestRepository->find($id);
    }

    public function updateRecruitmentRequest(int $id, array $data, $file = null): RecruitmentRequest
    {
        $recruitmentRequest = $this->recruitmentRequestRepository->find($id);
        if ($file) {
            if ($recruitmentRequest && $recruitmentRequest->job_file && \Storage::disk('public')->exists($recruitmentRequest->job_file)) {
                \Storage::disk('public')->delete($recruitmentRequest->job_file);
            }
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = $originalName . '_' . time() . '.' . $extension;
            $path = $file->storeAs('attechments_job_files', $filename, 'public');
            $data['job_file'] = $path;
        }
        return $this->recruitmentRequestRepository->update($id, $data);
    }

    public function deleteRecruitmentRequest(int $id): void
    {
        $this->recruitmentRequestRepository->delete($id);
    }
    public function validateRecruitmentRequest(int $id): RecruitmentRequest
    {
        $recruitmentRequest = $this->recruitmentRequestRepository->find($id);
        if (!$recruitmentRequest) {
            throw new \Exception('Recruitment Request not found');
        }
        $recruitmentRequest->status = 'Validée';
        $recruitmentRequest->save();
        return $recruitmentRequest;
    }

    public function rejectRecruitmentRequest(int $id): RecruitmentRequest
    {
        $recruitmentRequest = $this->recruitmentRequestRepository->find($id);
        if (!$recruitmentRequest) {
            throw new \Exception('Recruitment Request not found');
        }
        $recruitmentRequest->status = 'Rejeté';
        $recruitmentRequest->save();
        return $recruitmentRequest;
    }

    public function restoreRecruitmentRequest(int $id): void
    {
        $this->recruitmentRequestRepository->restore($id);
    }
    public function bulkDeleteRecruitmentRequests(array $ids): void
    {
        $this->recruitmentRequestRepository->bulkDelete($ids);
    }

}