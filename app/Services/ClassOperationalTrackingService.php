<?php

namespace App\Services;

use App\Enums\ClassStateEnum;
use App\Enums\ClassStatusValueEnum;
use App\Models\ClassStatusHistory;
use App\Models\ProjectClass;
use App\Repositories\ClassRepository;

class ClassOperationalTrackingService
{
    public function __construct(
        private ClassRepository $classRepository
    ) {}

    /**
     * Update the state of a class.
     * 
     * @param int $classId
     * @param string $newState
     * @param array $data Additional data (transfer_to_project_id, relocate_to_class_id, relocation_date)
     * @return ProjectClass
     */
    public function updateState(int $classId, string $newState, array $data = []): ProjectClass
    {
        $class = $this->classRepository->find($classId);
        
        // Convert string to enum
        $stateEnum = ClassStateEnum::tryFrom($newState);
        if (!$stateEnum) {
            throw new \InvalidArgumentException("Invalid class state: {$newState}");
        }

        $updateData = [
            'class_state' => $stateEnum,
        ];

        // Handle transfer
        if ($stateEnum === ClassStateEnum::TRANSFER) {
            if (!isset($data['transfer_to_project_id'])) {
                throw new \InvalidArgumentException('transfer_to_project_id is required for transfer state');
            }
            $updateData['transfer_to_project_id'] = $data['transfer_to_project_id'];
        }

        // Handle relocation
        if ($stateEnum === ClassStateEnum::RELOCATION) {
            if (!isset($data['relocate_to_class_id'])) {
                throw new \InvalidArgumentException('relocate_to_class_id is required for relocation state');
            }
            $updateData['relocate_to_class_id'] = $data['relocate_to_class_id'];
            $updateData['relocation_date'] = $data['relocation_date'] ?? now()->toDateString();
        }

        return $this->classRepository->update($classId, $updateData);
    }

    /**
     * Update the status of a class.
     * 
     * @param int $classId
     * @param string $newStatus
     * @param array $data Additional data (status_change_date, status_change_reason, perpetuation_project_id)
     * @return ProjectClass
     */
    public function updateStatus(int $classId, string $newStatus, array $data = []): ProjectClass
    {
        $class = $this->classRepository->find($classId);
        
        // Convert string to enum
        $statusEnum = ClassStatusValueEnum::tryFrom($newStatus);
        if (!$statusEnum) {
            throw new \InvalidArgumentException("Invalid class status: {$newStatus}");
        }

        $updateData = [
            'class_status_value' => $statusEnum,
            'status_change_date' => $data['status_change_date'] ?? now()->toDateString(),
            'status_change_reason' => $data['status_change_reason'] ?? null,
        ];

        // Handle perpetuation
        if ($statusEnum === ClassStatusValueEnum::PERPETUATED) {
            if (!isset($data['perpetuation_project_id'])) {
                throw new \InvalidArgumentException('perpetuation_project_id is required for perpetuated status');
            }
            $updateData['perpetuation_project_id'] = $data['perpetuation_project_id'];
        }

        return $this->classRepository->update($classId, $updateData);
    }

    /**
     * Auto-transfer classes that should be transferred after 2 years.
     * 
     * Classes in Preschool or INDH that were created 2 years ago should be transferred to the AREF of the corresponding region.
     * 
     * @return \Illuminate\Support\Collection
     */
    public function autoTransferClasses()
    {
        $transferredClasses = collect();

        // Get classes created 2 years ago that are still in "Création" state
        $classesToTransfer = ProjectClass::where('class_state', 'Création')
            ->where('created_at', '<=', now()->subYears(2))
            ->get();

        foreach ($classesToTransfer as $class) {
            try {
                // Find the corresponding AREF project for the region
                $arefProject = $this->findArefProjectForRegion($class->region_id);

                if ($arefProject) {
                    $this->updateState($class->id, ClassStateEnum::TRANSFER->value, [
                        'transfer_to_project_id' => $arefProject->id,
                    ]);
                    $transferredClasses->push($class);
                }
            } catch (\Exception $e) {
                // Log the error and continue
                \Log::error("Failed to auto-transfer class {$class->id}: " . $e->getMessage());
            }
        }

        return $transferredClasses;
    }

    /**
     * Get class status history.
     * 
     * @param int $classId
     * @return \Illuminate\Support\Collection
     */
    public function getHistory(int $classId)
    {
        return ClassStatusHistory::where('class_id', $classId)
            ->with(['user', 'transferToProject', 'relocateToClass', 'perpetuationProject'])
            ->orderByDesc('change_date')
            ->get();
    }

    /**
     * Find the AREF project for a given region.
     * 
     * @param int $regionId
     * @return \App\Models\Project|null
     */
    private function findArefProjectForRegion(int $regionId)
    {
        // This assumes there's a relationship or naming convention for AREF projects
        // You may need to adjust based on your actual project structure
        return null; // Placeholder - implement based on your business logic
    }

    /**
     * Get available states for dropdown.
     */
    public static function getStates(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], ClassStateEnum::cases());
    }

    /**
     * Get available statuses for dropdown.
     */
    public static function getStatuses(): array
    {
        return array_map(fn($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], ClassStatusValueEnum::cases());
    }
}
