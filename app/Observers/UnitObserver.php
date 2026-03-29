<?php

namespace App\Observers;

use App\Models\Unit;
use App\Repositories\UnitRepository;
use Illuminate\Support\Facades\Auth;

/**
 * Observes the Unit model for lifecycle events.
 */
class UnitObserver
{
    protected UnitRepository $unitRepository;

    /**
     * Inject UnitRepository.
     *
     * @param UnitRepository $unitRepository
     */
    public function __construct(UnitRepository $unitRepository)
    {
        $this->unitRepository = $unitRepository;
    }

    /**
     *
     * @param Unit $unit
     * @return void
     */
    public function creating(Unit $unit): void
    {
       if (empty($unit->created_by) && Auth::check()) {
            $unit->created_by = Auth::id();
        }
    }

    /**
     *
     * @param Unit $unit
     * @return void
     */
    public function updating(Unit $unit): void
    {
    }

    /**
     *
     * @param Unit $unit
     * @return void
     */
    public function deleted(Unit $unit): void
    {
        //
    }

    /**
     *
     * @param Unit $unit
     * @return void
     */
    public function restored(Unit $unit): void
    {
        //
    }

    /**
     *
     * @param Unit $unit
     * @return void
     */
    public function forceDeleted(Unit $unit): void
    {
        //
    }
}