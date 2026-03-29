<?php

namespace App\Services;

use App\Models\Commune;
use App\Models\Site;
use App\Repositories\SiteRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class SiteService
{
    protected SiteRepository $siteRepository;

    public function __construct(SiteRepository $siteRepository)
    {
        $this->siteRepository = $siteRepository;
    }

    /**
     * Get all sites.
     *
     * @return Collection<int, Site>
     */
    public function getAllSites(): Collection
    {
        return $this->siteRepository->getAll();
    }

    /**
     * Get sites with pagination and filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator<Site>
     */
    public function getPaginatedSites(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->siteRepository->getPaginated($filters, $perPage);
    }

    /**
     * Find a site by its ID.
     *
     * @param int $id
     * @return Site|null
     */
    public function findSiteById(int $id): ?Site
    {
        return $this->siteRepository->findById($id);
    }

    /**
     * Create a new site.
     *
     * @param array<string, mixed> $data
     * @return Site
     */
    public function createSite(array $data): Site
    {
        $data['created_by'] = Auth::id();

        if (!isset($data['status'])) {
            $data['status'] = 'Actif';
        }

        if (!isset($data['site_id'])) {
            $data['site_id'] = $this->generateSiteId($data);
        }

        return $this->siteRepository->create($data);
    }

    /**
     * Update an existing site.
     *
     * @param Site $site
     * @param array<string, mixed> $data
     * @return bool
     */
    public function updateSite(Site $site, array $data): bool
    {
        return $this->siteRepository->update($site, $data);
    }

    /**
     * Delete a site (soft delete).
     *
     * @param Site $site
     * @return bool|null
     */
    public function deleteSite(Site $site): ?bool
    {
        return $this->siteRepository->delete($site);
    }

    /**
     * Soft delete multiple sites.
     *
     * @param array $ids
     * @return array
     */
    public function bulkDeleteSites(array $ids): array
    {
        return $this->siteRepository->toggleActivation($ids);
    }

    /**
     * Generate a unique site ID based on provided data.
     *
     * @param array $data
     * @return string
     */
    protected function generateSiteId(array $data): string
    {
        $countryCode = strtoupper($data['country'] ?? 'XX');
        
        $commune = isset($data['commune_id']) ? Commune::find($data['commune_id']) : null;
        $cercle = $commune?->cercle;
        $province = $cercle?->province;
        $region = $province?->region;

        $regionCode = $region?->code ?? '00';
        $provinceCode = $province?->code ?? '00';
        $communeCode = $commune?->code ?? '00';

        $baseId = "SITE-{$countryCode}-{$regionCode}-{$provinceCode}-{$communeCode}";
        $count = Site::where('site_id', 'like', "{$baseId}%")->count();
        
        return "{$baseId}-" . str_pad((string)($count + 1), 4, '0', STR_PAD_LEFT);
    }
}
