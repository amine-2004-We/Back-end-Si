<?php

namespace App\Observers;

use App\Models\Module;
use App\Models\Training;
use Illuminate\Support\Facades\Auth;

class ModuleObserver
{
    public function creating(Module $module): void
    {
        // set creator
        if (empty($module->created_by_id) && Auth::check()) {
            $module->created_by_id = Auth::id();
        }

        // generate code only if not provided
        if (empty($module->module_id) && $module->training_id) {
            $training = Training::find($module->training_id);
            if (!$training) {
                return;
            }

            // Build a stable base from the training code.
            // Your existing codes look like "MOD-FORM0002-02", i.e. "MOD-FORM" + last numeric part.
            $parts = explode('-', (string) $training->training_id);         // e.g. FORM-INIT-0002
            $last  = end($parts) ?: '';
            $num   = preg_replace('/\D/', '', $last) ?: '0000';             // "0002"
            $base  = 'MOD-FORM' . $num;                                     // "MOD-FORM0002"

            // Find the highest existing suffix for this base (including soft-deleted)
            $existing = Module::withTrashed()
                ->where('module_id', 'like', $base . '-%')
                ->pluck('module_id');

            $max = 0;
            foreach ($existing as $mid) {
                if (preg_match('/-(\d+)$/', $mid, $m)) {
                    $max = max($max, (int) $m[1]);
                }
            }

            $next = $max + 1;                                              // increment!
            $module->module_id = sprintf('%s-%02d', $base, $next);         // e.g. MOD-FORM0002-03
        }
    }
}
