<?php

namespace App\Repositories;

use App\Models\Partner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;
use Illuminate\Support\Collection;

class PartnerRepository
{
    /**
     * Get all partners with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function all($perPage = 15): LengthAwarePaginator
    {
        return Partner::paginate($perPage);
    }

    public function allWithoutPagination(): Collection
    {
        return Partner::all('id','partner_name');
    }

    /**
     * Find a partner by ID.
     *
     * @param int $id
     * @return Partner
     */
    public function find($id): Partner
    {
        return Partner::findOrFail($id);
    }

    /**
     * Create a new partner.
     *
     * @param array $data
     * @return Partner
     */
    public function create(array $data): Partner
    {
        if (isset($data['partner_logo']) && $data['partner_logo'] instanceof UploadedFile) {
            $data['partner_logo'] = $data['partner_logo']->store('logos', 'public');
        }
        return Partner::create($data);
    }

    /**
     * Update an existing partner.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        $partner = $this->find($id);

         if (isset($data['partner_logo']) && $data['partner_logo'] instanceof UploadedFile) {
            if ($partner->partner_logo) {
                Storage::disk('public')->delete($partner->partner_logo);
            }
            $data['partner_logo'] = $data['partner_logo']->store('logos', 'public');
        }

        return $partner->update($data);
    }

    /**
     * SUPPRIMÉ : La méthode toggleActivation a été scindée en restoreByIds et softDeleteByIds
     */
    // public function toggleActivation(array $ids): array { ... }


    /**
     * AJOUTÉ : Gère la réactivation des partenaires (Clôturé -> Actif)
     *
     * @param array $ids
     * @return array
     */
    public function restoreByIds(array $ids): array
    {
        $results = [];
        $partnersToRestore = Partner::onlyTrashed()->whereIn('id', $ids)->get();

        foreach ($partnersToRestore as $partner) {
            try {
                // Vérifier les duplicata actifs
                $existingActivePartner = Partner::where('id', '!=', $partner->id)
                    ->where(function ($query) use ($partner) {
                        $query->where('partner_name', $partner->partner_name);
                        if ($partner->email) {
                            $query->orWhere('email', $partner->email);
                        }
                    })
                    ->whereNull('deleted_at') // Uniquement les partenaires actifs
                    ->first();

                if ($existingActivePartner) {
                    $results[] = [
                        'id' => $partner->id,
                        'success' => false,
                        'message' => "Impossible de réactiver '{$partner->partner_name}'. Un partenaire actif avec le même nom/email existe déjà (ID: {$existingActivePartner->id})."
                    ];
                } else {
                    if ($partner->restore()) {
                        $results[] = [
                            'id' => $partner->id,
                            'success' => true,
                            'message' => "Partenaire '{$partner->partner_name}' réactivé."
                        ];
                    } else {
                         $results[] = [
                            'id' => $partner->id,
                            'success' => false,
                            'message' => "Échec de la réactivation de '{$partner->partner_name}'."
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::error("Error restoring partner ID {$partner->id}: " . $e->getMessage());
                $results[] = [ 'id' => $partner->id, 'success' => false, 'message' => "Erreur inattendue lors de la réactivation."];
            }
        }
        return $results;
    }

    /**
     * AJOUTÉ : Gère la clôture des partenaires (Actif -> Clôturé)
     *
     * @param array $ids
     * @param string|null $closureReason
     * @return array
     */
    public function softDeleteByIds(array $ids): array // <- UN SEUL ARGUMENT ICI
    {
        $results = [];
        $partnersToDelete = Partner::whereIn('id', $ids)->whereNull('deleted_at')->get();

        foreach ($partnersToDelete as $partner) {
            try {

                if ($partner->delete()) {
                    $results[] = [
                        'id' => $partner->id,
                        'success' => true,
                         'message' => "Partenaire '{$partner->partner_name}' désactivé."
                    ];
                } else {
                     $results[] = [
                        'id' => $partner->id,
                        'success' => false,
                        'message' => "Échec de la désactivation de '{$partner->partner_name}'."
                    ];
                }
            } catch (Exception $e) {
                Log::error("Error soft-deleting partner ID {$partner->id}: " . $e->getMessage());
                $results[] = [ 'id' => $partner->id, 'success' => false, 'message' => "Erreur inattendue lors de la désactivation."];
            }
        }
        return $results;
    }


    /**
     * Get all partners with filters and pagination.
     *
     * @param array $filters An associative array of filters
     * @return LengthAwarePaginator
     */
     public function getAllPartners(array $filters = []): LengthAwarePaginator
     {
        $query = Partner::query()
        ->with([
            'contactPeople',
            'naturePartner',
            'structurePartner',
            'status',
            'notes',
            'creator'
        ]);

    if (isset($filters['activation_status']) && $filters['activation_status'] !== 'active') {
         $query->withTrashed();
    } else {
        $query->whereNull('deleted_at');
    }

    if (isset($filters['activation_status'])) {
        if ($filters['activation_status'] === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($filters['activation_status'] === 'deactivated') {
            $query->whereNotNull('deleted_at');
        }
    }

    if (isset($filters['partner_name']) && $filters['partner_name'] !== '') {
        $query->where('partner_name', 'ilike', '%' . $filters['partner_name'] . '%');
    }

    if (isset($filters['nature_partner']) && $filters['nature_partner'] !== '') {
        $query->whereHas('naturePartner', function ($q) use ($filters) {
            $q->where('name', $filters['nature_partner']);
        });
    }

    if (isset($filters['partner_type']) && $filters['partner_type'] !== '') {
        $query->where('partner_type', $filters['partner_type']);
    }

    if (isset($filters['structure_partner']) && $filters['structure_partner'] !== '') {
        $query->whereHas('structurePartner', function ($q) use ($filters) {
            $q->where('name', $filters['structure_partner']);
        });
    }

    if (isset($filters['status']) && $filters['status'] !== '') {
        $query->whereHas('status', function ($q) use ($filters) {
            $q->where('name', $filters['status']);
        });
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
}
