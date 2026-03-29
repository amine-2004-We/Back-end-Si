<?php

namespace App\Observers;

use App\Models\RouteModel as Route;

class RouteObserver
{
    /**
     * Handle the Route "creating" event.
     *
     * @param  \App\Models\Route  $route
     * @return void
     */
    public function creating(Route $route): void
    {
        $year = now()->year;
        $route->route_code = 'TR-' . $year . '-' . count(Route::withTrashed()->get());
    }
    
    /**
     * @param \App\Models\Route $route
     * @return void
     */
    public function updating(Route $route): void
    {
        $year = now()->year;
        $route->route_code = 'TR-' . $year;
    }
}
