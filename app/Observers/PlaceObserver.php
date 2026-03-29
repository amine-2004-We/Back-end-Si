<?php

namespace App\Observers;

use App\Models\Place;
use Illuminate\Support\Facades\Auth;

class PlaceObserver
{
    public function creating (Place $place){
        $place->created_by=Auth::id();
    }
}
