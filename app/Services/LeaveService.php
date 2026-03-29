<?php

namespace App\Services;

use App\Http\Requests\StoreLeaveRequest;
use App\Models\Leave;
use App\Repositories\CollaboratorRepository;
use App\Repositories\LeaveRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LeaveService
{
    protected LeaveRepository $leaveRepository;
    protected CollaboratorRepository $collaboratorRepository;

    /***
     * @param LeaveRepository $leaveRepository
     * @param CollaboratorRepository $collaboratorRepository
     */
    public function __construct(LeaveRepository $leaveRepository, CollaboratorRepository $collaboratorRepository)
    {
        $this->leaveRepository = $leaveRepository;
        $this->collaboratorRepository = $collaboratorRepository;
    }

    /***
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getAllLeaves(array $filters): LengthAwarePaginator
    {
        return $this->leaveRepository->getAllLeaves(
            Auth::user(),
            $filters
        );
    }

    /***
     * @param array $fillters
     * @return LengthAwarePaginator
     */
    public function getAllTrashedLeaves(array $fillters = []): LengthAwarePaginator
    {
        return $this->leaveRepository->getAllTrashedLeaves(
            Auth::user(),
            $fillters
        );
    }

    /***
     * @param StoreLeaveRequest $request
     * @return Leave
     */
    public function store(StoreLeaveRequest $request): Leave
    {
        $data = $request->validated();
        return $this->leaveRepository->store($data);
    }

    /***
     * @param int $id
     * @param StoreLeaveRequest $request
     * @return Leave
     */
    public function update(int $id, StoreLeaveRequest $request): Leave
    {
        $data = $request->validated();

        if ($data['status'] === "approved") {
            $collaborator = $this->collaboratorRepository->find($data['collaborator_id']);

            if ($collaborator) {
                $nbrDays = $data['nbr_days'] ?? 0;

                $newDays = max(0, $collaborator->annual_leave_days - $nbrDays);

                $collaborator->update([
                    'annual_leave_days' => $newDays,
                ]);
            }
        }

        return $this->leaveRepository->update($id, $data);
    }

    /***
     * @param int $id
     * @return Leave
     */
    public function show(int $id): Leave
    {
        return $this->leaveRepository->show($id);
    }

    /***
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->leaveRepository->delete($id);
    }

    /***
     * @param int $id
     * @return bool
     */
    public function restore(int $id): bool
    {
        return $this->leaveRepository->restore($id);
    }

    /***
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids): bool
    {
        return $this->leaveRepository->bulkDelete($ids);
    }
}
