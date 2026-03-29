<?php

namespace App\Repositories;

use App\Models\Beneficiary;
use App\Models\Group;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class BeneficiaryRepository
{
    /**
     * @var array
     */
    public array $defaultWith = [
        'currentSchoolLevel.cycle',
        'creator',
        'creator.collaborator.superior.user',
        'group.class',
        'parents',
        'statusHistories',
        'statusHistories.previousGroup',
        'statusHistories.destinationGroup',
        'statusHistories.transferCommune',
        'statusHistories.transferClass',
        
    ];

    /**
     *
     * @return Collection<int, Beneficiary>
     */
    public function getAll(): Collection
    {
        try {
            return Beneficiary::withTrashed()->with($this->defaultWith)->get();
        } catch (Exception $e) {
            Log::error('Error fetching all beneficiaries: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Beneficiary>
     */
   public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
{
    try {
        $query = Beneficiary::query();
        $query->withTrashed();
        $query->with($this->defaultWith);
        
        
        $perPage = isset($filters['per_page']) ? (int)$filters['per_page'] : $perPage;
        $page = isset($filters['page']) ? (int)$filters['page'] : 1;
        
     
        $filtersToProcess = $filters;
        unset($filtersToProcess['per_page'], $filtersToProcess['page'], $filtersToProcess['sort_by'], $filtersToProcess['sort_direction']);
        
      
        foreach ($filtersToProcess as $key => $value) {
            if (empty($value) && $value !== 0 && $value !== '0') {
                continue;
            }

            switch ($key) {
                case 'search':
                case 'globalSearch':
                    $query->where(function (Builder $q) use ($value) {
                        $q->whereRaw('LOWER(last_name) LIKE ?', ['%' . strtolower($value) . '%'])
                            ->orWhereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($value) . '%'])
                            ->orWhereRaw('LOWER(beneficiary_id) LIKE ?', ['%' . strtolower($value) . '%'])
                            ->orWhereRaw('LOWER(massar_code) LIKE ?', ['%' . strtolower($value) . '%']);
                    });
                    break;
                    
                case 'beneficiary_id':
                case 'massar_code':
                case 'parent_phone':
                    $query->where($key, 'like', '%' . $value . '%');
                    break;
                    
                case 'last_name':
                case 'first_name':
                case 'place_of_residence':
                case 'gender':
                case 'nationality':
                case 'status':
                case 'insurance_status':
                case 'radiation_reason':
                    $query->where($key, $value);
                    break;
                    
                case 'date_of_birth':
                case 'enrollment_date':
                case 'radiation_date':
                    $query->whereDate($key, $value);
                    break;
                    
                case 'current_school_level_id':
                case 'group_id':
                case 'created_by':
                    $query->where($key, $value);
                    break;
                    
                case 'activation_status':
                    if ($value === 'active') {
                        $query->whereNull('deleted_at');
                    } elseif ($value === 'deactivated') {
                        $query->whereNotNull('deleted_at');
                    }
                    break;
                    
                case 'class_id':
                    if (class_exists(Group::class)) {
                        $query->whereHas('group', fn(Builder $q) => $q->where('class_id', $value));
                    }
                    break;
                    
                case 'level_id':
                    if (class_exists(Group::class) && class_exists(\App\Models\ProjectClass::class) && class_exists(\App\Models\Level::class)) {
                        $query->whereHas('group.class', fn(Builder $q) => $q->where('level_id', $value));
                    }
                    break;
                    
                case 'cycle_id':
                    if (class_exists(Group::class) && class_exists(\App\Models\ProjectClass::class) && class_exists(\App\Models\Level::class)) {
                        $query->whereHas('group.class.level', fn(Builder $q) => $q->where('cycle_id', $value));
                    }
                    break;
                    
                case 'site_id':
                    if (class_exists(Group::class) && class_exists(\App\Models\ProjectClass::class) && class_exists(\App\Models\Unit::class)) {
                        $query->whereHas('group.class.unit', fn(Builder $q) => $q->where('site_id', $value));
                    }
                    break;
                    
                case 'region_id':
                    if (class_exists(Group::class) && class_exists(\App\Models\ProjectClass::class) && class_exists(\App\Models\Unit::class) && class_exists(\App\Models\Site::class) && class_exists(\App\Models\Commune::class) && class_exists(\App\Models\Cercle::class) && class_exists(\App\Models\Province::class) && class_exists(\App\Models\Region::class)) {
                        $query->whereHas('group.class.unit.site.commune.cercle.province.region', fn(Builder $q) => $q->where('regions.id', $value));
                    }
                    break;
            }
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        if (isset($filters['activation_status']) && $filters['activation_status'] === 'all') {
            $query->orderByRaw('deleted_at IS NOT NULL');
        }
        $query->orderBy($sortBy, $sortDirection);

        // Ensure relationships are loaded
        $query->with($this->defaultWith);

        // Validate perPage (prevent potential issues)
        $perPage = max(1, min($perPage, 100)); // Limit between 1 and 100
        
        // Execute pagination
        return $query->paginate($perPage, ['*'], 'page', $page);
        
    } catch (Exception $e) {
        Log::error('Error paginating beneficiaries with filters: ' . $e->getMessage());
        // Return empty paginator on error
        return new LengthAwarePaginator([], 0, $perPage, $page);
    }
}
    /**
     *
     * @param int $id
     * @return Beneficiary|null
     */
    public function findById(int $id): ?Beneficiary
    {
        try {
            return Beneficiary::withTrashed()->with($this->defaultWith)->find($id);
        } catch (Exception $e) {
            Log::error("Error finding beneficiary by ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new beneficiary.
     *
     * @param array<string, mixed> $data
     * @return Beneficiary
     * @throws Exception
     */
    public function create(array $data): Beneficiary
    {
        return Beneficiary::create($data);
    }

    /**
     *
     * @param Beneficiary $beneficiary
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(Beneficiary $beneficiary, array $data,$newGroupId): bool
    {
        try {
            if (!empty($newGroupId)){
                $data['group_id'] = $newGroupId;
            }
            return $beneficiary->update($data);
        } catch (Exception $e) {
            Log::error("Error updating beneficiary ID {$beneficiary->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param Beneficiary $beneficiary
     * @return bool|null
     */
    public function delete(Beneficiary $beneficiary): ?bool
    {
        try {
            return $beneficiary->delete();
        } catch (Exception $e) {
            Log::error("Error soft-deleting beneficiary ID {$beneficiary->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param array<int> $ids
     * @return array<array{id: int, success: bool, message: string}>
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];
        foreach ($ids as $id) {
            $beneficiary = Beneficiary::withTrashed()->find($id);

            if (!$beneficiary) {
                $results[] = [
                    'id' => $id,
                    'success' => false,
                    'message' => 'Bénéficiaire non trouvé.'
                ];
                continue;
            }

            try {
                if ($beneficiary->trashed()) {
                    $beneficiary->restore();
                    $message = 'Bénéficiaire restauré avec succès.';
                } else {
                    $beneficiary->delete();
                    $message = 'Bénéficiaire désactivé avec succès.';
                }
                $results[] = [
                    'id' => $id,
                    'success' => true,
                    'message' => $message
                ];
            } catch (Exception $e) {
                Log::error("Error toggling activation for beneficiary ID {$id}: " . $e->getMessage());
                $results[] = [
                    'id' => $id,
                    'success' => false,
                    'message' => 'Erreur lors du changement de statut: ' . $e->getMessage()
                ];
            }
        }
        return $results;
    }

    /**
     *
     * @param int $groupId
     * @return string
     */
    public function generateUniqueBeneficiaryId(int $groupId): string
    {
        try {
            if (!class_exists(Group::class)) {
                Log::warning("Group model not found. Generating generic beneficiary_id.");
                return 'BNF-NOGRP-' . uniqid();
            }

            $group = Group::find($groupId);
            if (!$group) {
                throw new Exception("Group with ID {$groupId} not found for beneficiary ID generation.");
            }
            $groupCode = $group->id ?? 'UNK';

            $prefix = 'BNF-' . 'GRP' . strtoupper($groupCode) . '-';
            $existing = Beneficiary::withTrashed()->where('beneficiary_id', 'like', $prefix . '%')->pluck('beneficiary_id')->toArray();

            $maxSequence = 0;
            foreach ($existing as $beneficiaryId) {
                $parts = explode('-', $beneficiaryId);
                $numericPart = (int) end($parts);
                $maxSequence = max($maxSequence, $numericPart);
            }

            $next = str_pad($maxSequence + 1, 3, '0', STR_PAD_LEFT);

            return $prefix . $next;
        } catch (Exception $e) {
            Log::error('Error generating unique beneficiary ID: ' . $e->getMessage());
            return 'BNF-ERR-' . uniqid();
        }
    }
    /**
     *
     * @param int $classId The ID of the class to count beneficiaries for
     * @return int The number of beneficiaries linked to that class
     */
    public function countByClassId(int $classId): int
    {
        return Beneficiary::whereHas('group', function ($query) use ($classId) {
            $query->where('class_id', $classId);
        })->count();
    }

    /**
     * Get beneficiaries eligible for assurance export (e.g., insurance_status 'Non assuré').
     *
     * @return Collection<int, Beneficiary>
     */
    public function getEligibleForAssuranceExport(): Collection
    {
        try {
            \Log::info('Fetching beneficiaries eligible for assurance export from repo...');
            return Beneficiary::query()
                ->where('insurance_status', 'Non assuré') // Corrected field
                ->get();
            \Log::info('Fetched ' . $beneficiaries->count() . ' beneficiaries eligible for assurance export.');
        } catch (Exception $e) {
            Log::error('Error fetching beneficiaries for assurance export: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Update the 'insurance_status' for a list of beneficiaries.
     *
     * @param array<int> $beneficiaryIds
     * @param string $newStatus The new status to set ('En cours' or 'Assuré')
     * @return int The number of records updated
     */
    public function updateAssuranceStatus(array $beneficiaryIds, string $newStatus): int
    {
        try {
            return Beneficiary::whereIn('id', $beneficiaryIds)->update(['insurance_status' => $newStatus]); // Corrected field
        } catch (Exception $e) {
            Log::error("Error updating assurance status to '{$newStatus}': " . $e->getMessage());
            return 0;
        }
    }
}

