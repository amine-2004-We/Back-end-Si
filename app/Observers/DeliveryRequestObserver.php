<?php

namespace App\Observers;

use App\Models\DeliveryRequest;
use App\Services\Notification\MailService;
use App\Services\Notification\ObserverNotificationService;
use Illuminate\Support\Facades\Auth;

class DeliveryRequestObserver
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

    public function creating(DeliveryRequest $deliveryRequest)
    {
        if (Auth::check()) {
            $deliveryRequest->created_by = Auth::id();
        }

        if (empty($deliveryRequest->request_date)) {
            $deliveryRequest->request_date = now()->toDateString();
        }

        if (empty($deliveryRequest->status)) {
            $deliveryRequest->status = 'En attente';
        }

        if (empty($deliveryRequest->code)) {
            $year = now()->format('Y');
            
            $lastRequest = DeliveryRequest::withTrashed()
                ->where('code', 'like', "DLV-$year-%")
                ->orderBy('created_at', 'desc')
                ->first();
            
            $nextNumber = 1;
            if ($lastRequest && $lastRequest->code) {
                $parts = explode('-', $lastRequest->code);
                $lastNumber = end($parts);
                if (is_numeric($lastNumber)) {
                    $nextNumber = (int)$lastNumber + 1;
                }
            }
            
            $deliveryRequest->code = sprintf('DLV-%s-%04d', $year, $nextNumber);
        }
    }



     public function created( DeliveryRequest $deliveryRequest): void
    {

        $creatorEmail = $deliveryRequest->creator?->email;
        \Log::info('creator email',['creator_email'=>$creatorEmail]);
        MailService::sendMail(
            ['najiihsane22@gmail.com',$creatorEmail],
            "Création d'une demande de livraison",
            'emails.deliveryRequests.creationNotification',
            [
                'title' => 'Demande de livraison créée !!',
                'subject' => 'Confirmation de création',
               'deliveryRequest' => $deliveryRequest,
            ]
        );


        $superior = $deliveryRequest->creator?->collaborator->superior;
        if ($superior) {
        $superiorEmail = $superior->email;

        $defaultUrl = 'http://fzedprod1.eastus.cloudapp.azure.com';

$baseUrl = app()->environment('local')
    ? env('FRONTEND_URL_LOCAL')
    : (app()->environment('testing')
        ? env('FRONTEND_URL_PROD', 'http://fzed.eastus.cloudapp.azure.com')
        : $defaultUrl);
           


 
         $validationLink = rtrim($baseUrl, '/') . '/achat/demande-livraison';

    
    MailService::sendMail(
            [$superiorEmail,'i.ennajy@fondationzakoura.org'],
            "Création d'une demande de livraison",
            'emails.deliveryRequests.superiorNotification',
            [
                  'title' => 'Demande de livraison créée !!',
                'subject' => 'Confirmation de création',
               'deliveryRequest' => $deliveryRequest,
                'validationLink' => $validationLink,
            ]
        );
}

        \Log::info('Observer HIT', ['delivery_request_id' => $deliveryRequest->id, 'user_id' => $deliveryRequest->creator?->id]);
        $this->notifier->sendCreationNotification($deliveryRequest, $deliveryRequest->creator, "Demande de livraison");
    }
}
