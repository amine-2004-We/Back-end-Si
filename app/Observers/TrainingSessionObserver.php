<?php

namespace App\Observers;

use App\Models\TrainingSession;
use App\Models\Module;

class TrainingSessionObserver
{
    /**
     * Handle the TrainingSession "creating" event.
     */
    public function creating(TrainingSession $trainingSession): void
    {
        if (empty($trainingSession->session_identifier)) {
            $module = Module::find($trainingSession->module_id);
            $prefix = 'SEAN-' . ($module->module_code ?? 'MOD' . $trainingSession->module_id) . '-';
            
            $latestSession = TrainingSession::where('session_identifier', 'like', $prefix . '%')
                ->withTrashed()
                ->orderBy('session_identifier', 'desc')
                ->first();

            $nextId = 1;
            if ($latestSession) {
                $lastIdNumber = (int) substr($latestSession->session_identifier, strlen($prefix));
                $nextId = $lastIdNumber + 1;
            }

            $trainingSession->session_identifier = $prefix . str_pad($nextId, 2, '0', STR_PAD_LEFT);
        }
    }
}