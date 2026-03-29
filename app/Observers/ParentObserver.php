<?php

namespace App\Observers;

use App\Models\ParentModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ParentObserver
{
    /**
     * Handle the ParentModel "creating" event.
     */
    public function creating(ParentModel $parent)
    {
        $nextNumber = ParentModel::withTrashed()->count() + 1;
        $parent->parent_id = 'PRT-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        // Set created_by to the authenticated user
        if (Auth::check()) {
            $parent->created_by = Auth::id();
        }
    }

    /**
     * Handle the ParentModel "created" event.
     */
    public function created(ParentModel $parent)
    {
        //
    }

    /**
     * Handle the ParentModel "updating" event.
     */
    public function updating(ParentModel $parent)
    {
        //
    }

    /**
     * Handle the ParentModel "updated" event.
     */
    public function updated(ParentModel $parent)
    {
        //
    }

    /**
     * Handle the ParentModel "deleted" event.
     */
    public function deleted(ParentModel $parent)
    {
        //
    }

    /**
     * Handle the ParentModel "restored" event.
     */
    public function restored(ParentModel $parent)
    {
        //
    }

    /**
     * Handle the ParentModel "force deleted" event.
     */
    public function forceDeleted(ParentModel $parent)
    {
        //
    }
}
