<?php

namespace App\Observers;

use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Auth;

class MedicalRecordObserver
{
    /***
     * @param MedicalRecord $medicalRecord
     * @return void
     */
    public function creating(MedicalRecord $medicalRecord): void
    {
        if (Auth::check()) {
            $medicalRecord->created_by = Auth::id();
        }
    }

}
