<?php

namespace App\Observers;

use App\Models\ContactPerson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ContactPersonObserver
{
    public function creating(ContactPerson $contact): void
    {
        if (empty($contact->contact_code)) {
            $currentYearMonth = Carbon::now()->format('Ym'); // e.g., 202509

            $lastContact = ContactPerson::withTrashed()
                ->where('contact_code', 'like', 'CONT-' . $currentYearMonth . '-%')
                ->orderByRaw('LENGTH(contact_code) DESC, contact_code DESC')
                ->first();

            $nextNumber = 1;
            if ($lastContact) {
                preg_match('/CONT-\d{6}-(\d+)/', $lastContact->contact_code, $matches);
                if (isset($matches[1])) {
                    $nextNumber = (int)$matches[1] + 1;
                }
            }
            $contact->contact_code = 'CONT-' . $currentYearMonth . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
    }

}
