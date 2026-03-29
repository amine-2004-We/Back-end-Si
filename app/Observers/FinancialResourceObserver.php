<?php

namespace App\Observers;

use App\Models\FinancialResource;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class FinancialResourceObserver
{
    /***
     * @param FinancialResource $financialResource
     * @return void
     */
    public function creating(FinancialResource $financialResource)
    {
        $year = now()->year;
        $project=Project::withoutTrashed()->findOrFail($financialResource->project_id);
        $nextNumber = FinancialResource::withTrashed()
            ->whereYear('created_at', $year)
            ->count()+1;
        $financialResource->financial_resources_code='SUBV-'.$year.'-'.$project->project_code.'-'.$nextNumber;
        if(Auth::check())
        {
            $financialResource->created_by=Auth::id();
        }
    }
}
