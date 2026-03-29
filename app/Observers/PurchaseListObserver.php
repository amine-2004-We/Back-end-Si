<?php

namespace App\Observers;

use App\Models\PurchaseList;
use App\Services\Notification\MailService;
use App\Services\Notification\ObserverNotificationService;

class PurchaseListObserver
{

    /**
     * @var ObserverNotificationService
     */
    protected ObserverNotificationService $notifier;

    /**
     * @param ObserverNotificationService $notifier
     */
    public function __construct(ObserverNotificationService $notifier)
    {
        $this->notifier = $notifier;
    }
    public function creating(PurchaseList $purchaseList): void
    {
        if (empty($purchaseList->purchase_list_id)) {
            $year = now()->format('Y');

            // Get the highest sequence number for this year
            $last = PurchaseList::withTrashed()
                ->where('purchase_list_id', 'LIKE', "LA-{$year}-%")
                ->orderByDesc('purchase_list_id')
                ->first();

            if ($last) {
                // Extract the sequence number from the last ID and increment
                $lastSeq = intval(substr($last->purchase_list_id, -4));
                $seq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
            } else {
                $seq = '0001';
            }

            $purchaseList->purchase_list_id = "LA-{$year}-{$seq}";
        }
    }

      public function created( PurchaseList $purchaseList): void
    {

        $creatorEmail = $purchaseList->creator?->email;
        \Log::info('creator email',['creator_email'=>$creatorEmail]);
        MailService::sendMail(
            ['najiihsane22@gmail.com',$creatorEmail],
            "Création d'une liste d'achats",
            'emails.purchaseLists.creationNotification',
            [
                'title' => 'Liste d\'achats créée !!',
                'subject' => 'Confirmation de création',
               'purchaseList' => $purchaseList,
            ]
        );


        $superior = $purchaseList->creator?->collaborator->superior;
        if ($superior) {
        $superiorEmail = $superior->email;

        $defaultUrl = 'http://fzedprod1.eastus.cloudapp.azure.com';

$baseUrl = app()->environment('local')
    ? env('FRONTEND_URL_LOCAL')
    : (app()->environment('testing')
        ? env('FRONTEND_URL_PROD', 'http://fzed.eastus.cloudapp.azure.com')
        : $defaultUrl);
           


 
         $validationLink = rtrim($baseUrl, '/') . '/purchase-lists';

    
    MailService::sendMail(
            [$superiorEmail,'i.ennajy@fondationzakoura.org'],
            "Création d'une liste d'achats",
            'emails.purchaseLists.superiorNotification',
            [
                  'title' => 'Liste d\'achats créée !!',
                'subject' => 'Confirmation de création',
               'purchaseList' => $purchaseList,
                'validationLink' => $validationLink,
            ]
        );
}

        \Log::info('Observer HIT', ['purchase_list_id' => $purchaseList->id, 'user_id' => $purchaseList->creator?->id]);
        $this->notifier->sendCreationNotification($purchaseList, $purchaseList->creator, "Liste d'achats");
    }
}
