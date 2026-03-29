<?php

namespace App\Observers;

use App\Models\DeliveryOrder;
use App\Services\Notification\MailService;
use App\Services\Notification\ObserverNotificationService;
use Illuminate\Support\Facades\DB;

class DeliveryOrderObserver
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

    
    public function creating(DeliveryOrder $deliveryOrder): void
    {
    
        if (empty($deliveryOrder->order_id) && $deliveryOrder->purchase_order_id) {
            \Log::info('Generating order_id for DeliveryOrder observer', ['purchase_order_id' => $deliveryOrder->purchase_order_id]);
            $this->generateOrderId($deliveryOrder);
        }
    }

    
    private function generateOrderId(DeliveryOrder $deliveryOrder): void
    {
        
        DB::transaction(function () use ($deliveryOrder) {
            
            $poId = $deliveryOrder->purchase_order_id;
            $baseIdPattern = 'ODL-' . $poId . '-%';

          
            $lastDeliveryOrder = DeliveryOrder::withTrashed()
                ->where('purchase_order_id', $poId)
                ->where('order_id', 'like', $baseIdPattern)
                ->orderByDesc('order_id')
                ->lockForUpdate() 
                ->first();

            $nextSequence = 1;

            if ($lastDeliveryOrder) {
               
                $lastIdParts = explode('-', $lastDeliveryOrder->order_id);
                
                $lastSequence = (int) end($lastIdParts); 
                $nextSequence = $lastSequence + 1;
            }

          
            $formattedSequence = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

            $deliveryOrder->order_id = 'ODL-' . $poId . '-' . $formattedSequence;
        });
    }



    public function created( DeliveryOrder $deliveryOrder): void
    {

        $creatorEmail = $deliveryOrder->creator?->email;
        \Log::info('creator email',['creator_email'=>$creatorEmail]);
        MailService::sendMail(
            ['najiihsane22@gmail.com',$creatorEmail],
            "Création d'un ordre livraison",
            'emails.deliveryOrders.creationNotification',
            [
                'title' => 'Demande de livraison créée !!',
                'subject' => 'Confirmation de création',
               'deliveryOrder' => $deliveryOrder,
            ]
        );


        $superior = $deliveryOrder->creator?->collaborator->superior;
        if ($superior) {
        $superiorEmail = $superior->email;

        $defaultUrl = 'http://fzedprod1.eastus.cloudapp.azure.com';

$baseUrl = app()->environment('local')
    ? env('FRONTEND_URL_LOCAL')
    : (app()->environment('testing')
        ? env('FRONTEND_URL_PROD', 'http://fzed.eastus.cloudapp.azure.com')
        : $defaultUrl);
           


 
         $validationLink = rtrim($baseUrl, '/') . '/ordres-de-livraison';

    
    MailService::sendMail(
            [$superiorEmail,'i.ennajy@fondationzakoura.org'],
            "Création d'un ordre de livraison",
            'emails.deliveryOrders.superiorNotification',
            [
                  'title' => 'Ordre de livraison créé !!',
                'subject' => 'Confirmation de création',
               'deliveryOrder' => $deliveryOrder,
                'validationLink' => $validationLink,
            ]
        );
}

        \Log::info('Observer HIT', ['delivery_order_id' => $deliveryOrder->id, 'user_id' => $deliveryOrder->creator?->id]);
        $this->notifier->sendCreationNotification($deliveryOrder, $deliveryOrder->creator, "Ordre de livraison");
    }
}
