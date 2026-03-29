<?php

namespace App\Observers;

use App\Models\MissionOrder;
use Illuminate\Support\Facades\DB;

class MissionOrderObserver
{
    /**
     * Handle the MissionOrder "creating" event.
     */
    public function creating(MissionOrder $missionOrder): void
    {
        $this->generateMissionOrderCode($missionOrder);
    }

    public function created(MissionOrder $missionOrder): void
    {
        DB::table('status_history')->insert([
            'domain' => 'RH',
            'object_type' => 'mission_orders',
            'object_id' => $missionOrder->id,
            'status' => 'pending',
            'user_id' => auth()->id(),
            'comment' => 'Mission ordre '. $missionOrder->status,
        ]);
    }

    /**
     * Handle the MissionOrder "updating" event.
     */
    public function updating(MissionOrder $missionOrder): void
    {
        // Only regenerate code if collaborator_id has changed
        if ($missionOrder->isDirty('collaborator_id')) {
            $this->generateMissionOrderCode($missionOrder);
        }
    }

    public function updated(MissionOrder $missionOrder): void
    {
        if($missionOrder->isDirty('status')){
            DB::table('status_history')->insert([
            'domain' => 'RH',
            'object_type' => 'mission_orders',
            'object_id' => $missionOrder->id,
            'status' => $missionOrder->status,
            'user_id' => auth()->id(),
            'comment' => $missionOrder->status == "approved" ? "Mission ordre approuvé par " . auth()->user()->name : "Mission ordre refusé par " . auth()->user()->name,
        ]);
    }
    }

    /**
     * Generate mission order code with format: OM-[Date]-[ID collab]-[Sequence]
     * Ensures uniqueness even for same collaborator on same day
     */
    private function generateMissionOrderCode(MissionOrder $missionOrder): void
    {
        $date = now()->format('Ymd'); // Format: 20250104
        $collaboratorId = $missionOrder->collaborator_id;
        
        // Find the next sequence number for this collaborator on this date
        $baseCode = "OM-{$date}-{$collaboratorId}";
        
        // Count existing mission orders with the same base pattern
        $existingCount = MissionOrder::withTrashed()
            ->where('mission_order_code', 'like', $baseCode . '%')
            ->count();
        
        // If there are existing codes, add sequence number
        if ($existingCount > 0) {
            $sequence = $existingCount + 1;
            $missionOrder->mission_order_code = "{$baseCode}-{$sequence}";
        } else {
            $missionOrder->mission_order_code = $baseCode;
        }
    }
}
