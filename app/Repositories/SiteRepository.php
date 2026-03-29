<?php

namespace App\Repositories;

use App\Models\Site;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class SiteRepository
{
    /**
     *
     * @return Collection<int, Site>
     */
    public function getAll(): Collection
    {
        try {
            return Site::withTrashed()->with([
                'commune.cercle.province.region.country',
                'douar',
                'creator'
            ])->get();
        } catch (Exception $e) {
            Log::error('Error fetching all sites: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePawarePaginator<Site>
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = Site::query();

            $query->withTrashed();

            if (isset($filters['activation_status'])) {
                if ($filters['activation_status'] === 'active') {
                    $query->whereNull('deleted_at');
                } elseif ($filters['activation_status'] === 'deactivated') {
                    $query->whereNotNull('deleted_at');
                }
            } else {
                $query->whereNull('deleted_at');
            }

           if (!empty($filters['name'])) {
            $query->where('name', 'ilike', '%' . $filters['name'] . '%');
        }

            foreach ($filters as $key => $value) {
                if (!empty($value) && !in_array($key, ['activation_status', 'name', 'page', 'per_page', 'sort_by', 'sort_direction'])) {
                    switch ($key) {
                        case 'internal_code':
                        case 'partner_reference_code':
                            $query->whereRaw('LOWER(' . $key . ') LIKE ?', ['%' . strtolower($value) . '%']);
                            break;
                        case 'type':
                        case 'status':
                            $query->where($key, $value);
                            break;
                        case 'start_date':
                            $query->whereDate($key, $value);
                            break;
                        case 'country_id':
                            if ($value !== 'all' && $value !== '') {
                                $query->whereHas('commune.cercle.province.region.country', fn (Builder $q) => $q->where('countries.id', $value));
                            }
                            break;
                        case 'region_id':
                            if ($value !== 'all' && $value !== '') {
                                $query->whereHas('commune.cercle.province.region', fn (Builder $q) => $q->where('regions.id', $value));
                            }
                            break;
                        case 'province_id':
                            $query->whereHas('commune.cercle.province', fn (Builder $q) => $q->where('provinces.id', $value));
                            break;
                        case 'cercle_id':
                            $query->whereHas('commune.cercle', fn (Builder $q) => $q->where('cercles.id', $value));
                            break;
                        case 'commune_id':
                            $query->where('commune_id', $value);
                            break;
                        case 'douar_id':
                            $query->where('douar_id', $value);
                            break;
                    }
                }
            }

            $query->with([
                'commune.cercle.province.region.country',
                'douar',
                'creator'
            ]);

            $sortBy = $filters['sort_by'] ?? 'created_at';
            $sortDirection = $filters['sort_direction'] ?? 'desc';

            if (isset($filters['activation_status']) && $filters['activation_status'] === 'all') {
                $query->orderByRaw('deleted_at IS NOT NULL');
            }
            $query->orderBy($sortBy, $sortDirection);

            $perPage = $filters['per_page'] ?? $perPage;
            $page = $filters['page'] ?? 1;

            return $query->paginate($perPage, ['*'], 'page', $page);

        } catch (Exception $e) {
            Log::error('Error paginating sites with filters: ' . $e->getMessage());
            return Site::whereRaw('1=0')->paginate($perPage); // Return empty paginator on failure
        }
    }

    /**
     *
     * @param int $id
     * @return Site|null
     */
    public function findById(int $id): ?Site
    {
        try {
            return Site::withTrashed()->with([
                'commune.cercle.province.region.country',
                'douar',
                'creator'
            ])->find($id);
        } catch (Exception $e) {
            Log::error("Error finding site by ID {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     *
     * @param array<string, mixed> $data
     * @return Site|null
     */
    public function create(array $data): ?Site
    {
        try {
            return Site::create($data);
        } catch (Exception $e) {
            Log::error('Error creating site: ' . $e->getMessage());
            return null;
        }
    }

    /**
     *
     * @param Site $site
     * @param array<string, mixed> $data
     * @return bool
     */
    public function update(Site $site, array $data): bool
    {
        try {
            return $site->update($data);
        } catch (Exception $e) {
            Log::error("Error updating site ID {$site->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param Site $site
     * @return bool|null
     */
    public function delete(Site $site): ?bool
    {
        try {
            return $site->delete();
        } catch (Exception $e) {
            Log::error("Error deleting site ID {$site->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    public function toggleActivation(array $ids): array
    {
        $results = [];

        $sitesToRestore = Site::onlyTrashed()->whereIn('id', $ids)->get();
        $sitesToDelete = Site::whereIn('id', $ids)->whereNull('deleted_at')->get();

        foreach ($sitesToRestore as $site) {
            try {
                $existingActiveSite = Site::where('id', '!=', $site->id)
                                          ->where(function ($query) use ($site) {
                                              $query->where('name', $site->name);
                                              if ($site->internal_code) {
                                                  $query->orWhere('internal_code', $site->internal_code);
                                              }
                                          })
                                          ->whereNull('deleted_at') // Only check against active sites
                                          ->first();

                if ($existingActiveSite) {
                    $results[] = [
                        'id' => $site->id,
                        'success' => false,
                        'message' => "Impossible de réactiver le site '{$site->name}' (ID: {$site->id}). Un site actif avec le même nom ou code interne existe déjà (ID: {$existingActiveSite->id})."
                    ];
                } else {
                    if ($site->restore()) {
                        $results[] = [
                            'id' => $site->id,
                            'success' => true,
                            'message' => "Site '{$site->name}' (ID: {$site->id}) réactivé avec succès."
                        ];
                    } else {
                        $results[] = [
                            'id' => $site->id,
                            'success' => false,
                            'message' => "Échec de la réactivation du site '{$site->name}' (ID: {$site->id})."
                        ];
                    }
                }
            } catch (Exception $e) {
                Log::error("Error restoring site ID {$site->id}: " . $e->getMessage());
                $results[] = [
                    'id' => $site->id,
                    'success' => false,
                    'message' => "Une erreur inattendue est survenue lors de la réactivation du site '{$site->name}' (ID: {$site->id})."
                ];
            }
        }

        foreach ($sitesToDelete as $site) {
            try {
                if ($site->delete()) {
                    $results[] = [
                        'id' => $site->id,
                        'success' => true,
                        'message' => "Site '{$site->name}' (ID: {$site->id}) désactivé avec succès."
                    ];
                } else {
                    $results[] = [
                        'id' => $site->id,
                        'success' => false,
                        'message' => "Échec de la désactivation du site '{$site->name}' (ID: {$site->id})."
                    ];
                }
            } catch (Exception $e) {
                Log::error("Error soft-deleting site ID {$site->id}: " . $e->getMessage());
                $results[] = [
                    'id' => $site->id,
                    'success' => false,
                    'message' => "Une erreur inattendue est survenue lors de la désactivation du site '{$site->name}' (ID: {$site->id})."
                ];
            }
        }

        return $results;
    }

    /**
     *
     * @param string $countryCode
     * @return string
     */
    public function generateUniqueSiteId(string $countryCode): string
    {
        try {
            $prefix = 'SITE-' . strtoupper($countryCode) . '-';
            $existing = Site::withTrashed()->where('site_id', 'like', $prefix . '%')->pluck('site_id')->toArray();

            $maxSequence = 0;

            foreach ($existing as $siteId) {
                $numericPart = (int) (explode('-', $siteId)[2] ?? 0);
                $maxSequence = max($maxSequence, $numericPart);
            }

            $next = str_pad($maxSequence + 1, 3, '0', STR_PAD_LEFT);

            return $prefix . $next;
        } catch (Exception $e) {
            Log::error('Error generating unique site ID: ' . $e->getMessage());
            return 'SITE-' . strtoupper($countryCode) . '-ERR';
        }
    }
}
