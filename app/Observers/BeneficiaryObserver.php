<?php

namespace App\Observers;

use App\Models\Beneficiary;
use App\Models\Collaborator;
use App\Models\Group;
use App\Services\Notification\ObserverNotificationService;
use Illuminate\Support\Facades\Log;
use Exception;

class BeneficiaryObserver
{
    protected $notifier;

    public function __construct(ObserverNotificationService $notifier)
    {
        $this->notifier = $notifier;
    }

    /**
     * Handle the Beneficiary "creating" event.
     *
     * @param  \App\Models\Beneficiary  $beneficiary
     * @return void
     * @throws \Exception
     */
    public function creating(Beneficiary $beneficiary): void
    {
        if (empty($beneficiary->beneficiary_id) && !empty($beneficiary->group_id)) {
            if (!class_exists(Group::class)) {
                throw new Exception("Group model not found. Cannot generate beneficiary ID.");
            }

            $group = Group::find($beneficiary->group_id);

            if (!$group) {
                throw new Exception("Group with ID {$beneficiary->group_id} not found for beneficiary ID generation.");
            }

            $groupCode = $group->code ?? $group->id;

            $prefix = 'BNF-' . strtoupper($groupCode) . '-';

            $latestBeneficiary = Beneficiary::withTrashed()
                                    ->where('beneficiary_id', 'like', $prefix . '%')
                                    ->orderBy('beneficiary_id', 'desc')
                                    ->first();

            $maxSequence = 0;
            if ($latestBeneficiary) {
                $parts = explode('-', $latestBeneficiary->beneficiary_id);
                $numericPart = (int) end($parts);
                $maxSequence = $numericPart;
            }

            $nextSequence = str_pad($maxSequence + 1, 3, '0', STR_PAD_LEFT);
            $beneficiary->beneficiary_id = $prefix . $nextSequence;


        } elseif (empty($beneficiary->beneficiary_id) && empty($beneficiary->group_id)) {
            Log::warning("Beneficiary is being created without a group_id. beneficiary_id will not be automatically generated.");
        }
    }

    public function created(Beneficiary $beneficiary): void {
        $this->notifier->sendCreationNotification($beneficiary,$beneficiary->creator, label: 'Bénéficiaire');
    }



    /**
     * Handle the Beneficiary "updated" event.
     */
    public function updated(Beneficiary $beneficiary): void
    {
        //
    }

    /**
     * Handle the Beneficiary "deleted" event.
     */
    public function deleted(Beneficiary $beneficiary): void
    {
        //
    }

    /**
     * Handle the Beneficiary "restored" event.
     */
    public function restored(Beneficiary $beneficiary): void
    {
        //
    }

    /**
     * Handle the Beneficiary "force deleted" event.
     */
    public function forceDeleted(Beneficiary $beneficiary): void
    {
        //
    }
}
