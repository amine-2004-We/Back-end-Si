<?php

namespace App\Observers;

use App\Models\Site;
use App\Repositories\SiteRepository;
use Illuminate\Support\Facades\Auth;

class SiteObserver
{
    protected SiteRepository $siteRepository;

    public function __construct(SiteRepository $siteRepository)
    {
        $this->siteRepository = $siteRepository;
    }

    /**
     * Handle the Site "creating" event.
     * This event is fired before a new model is saved.
     *
     * @param Site $site
     * @return void
     */
    public function creating(Site $site): void
    {
        if (empty($site->created_by) && Auth::check()) {
            $site->created_by = Auth::id();
        }

        if (empty($site->site_id) && !empty($site->country)) {
            $site->site_id = $this->siteRepository->generateUniqueSiteId($site->country);
        }
    }

    /**
     * Handle the Site "updating" event.
     *
     * @param Site $site
     * @return void
     */
    public function updating(Site $site): void
    {
        //
    }

    /**
     * Handle the Site "deleted" event.
     *
     * @param Site $site
     * @return void
     */
    public function deleted(Site $site): void
    {
        //
    }

    /**
     * Handle the Site "restored" event.
     *
     * @param Site $site
     * @return void
     */
    public function restored(Site $site): void
    {
        //
    }

    /**
     * Handle the Site "forceDeleted" event.
     *
     * @param Site $site
     * @return void
     */
    public function forceDeleted(Site $site): void
    {
        //
    }
}
