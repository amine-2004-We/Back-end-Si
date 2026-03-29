<?php

namespace App\Observers;

use App\Models\Collaborator;
use App\Models\PurchaseRequest;
use App\Services\Notification\MailService;
use App\Services\Notification\ObserverNotificationService;
use App\Services\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PurchaseRequestObserver
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
    public function creating(PurchaseRequest $header): void
    {
        $header->code = $this->generateReference($header->department_id);
    }

    public function updating(PurchaseRequest $header): void
    {
        if ($header->isDirty('department_id')) {
            $header->code = $this->generateReference($header->department_id);
        }
    }

    private function generateReference(int $departmentId): string
    {
        $date = Carbon::now()->format('Ymd');
        $deptCode = 'DEPT' . $departmentId;
        $base = "DA-{$date}-{$deptCode}-";

        $codes = PurchaseRequest::withTrashed()
            ->where('code', 'like', $base . '%')
            ->pluck('code');

        $maxSuffix = 0;
        foreach ($codes as $code) {
            $suffixStr = substr($code, strrpos($code, '-') + 1);
            $suffixNum = (int)$suffixStr;
            if ($suffixNum > $maxSuffix) {
                $maxSuffix = $suffixNum;
            }
        }

        $next = $maxSuffix + 1;
        $suffix = str_pad($next, 4, '0', STR_PAD_LEFT);

        return $base . $suffix;
    }

    public function created(PurchaseRequest $purchaseRequest): void
    {
  
        $creatorEmail = $purchaseRequest->user?->email;
        \Log::info('creator email',['creator_email'=>$creatorEmail]);
        MailService::sendMail(
            ['najiihsane22@gmail.com',$creatorEmail],
            "Création d'une demande d'achat",
            'emails.notification',
            [
                'title' => 'Demande d’achat créée !!',
                'subject' => 'Confirmation de création',
               'purchaseRequest' => $purchaseRequest,
            ]
        );

       
        $superior = $purchaseRequest->user->collaborator->superior;
        if ($superior) {
        $superiorEmail = $superior->email;

        $defaultUrl = 'http://fzedprod1.eastus.cloudapp.azure.com';

$baseUrl = app()->environment('local')
    ? env('FRONTEND_URL_LOCAL')
    : (app()->environment('testing')
        ? env('FRONTEND_URL_PROD', 'http://fzed.eastus.cloudapp.azure.com')
        : $defaultUrl);
           


 
         $validationLink = rtrim($baseUrl, '/') . '/demandes-achats';

    
    MailService::sendMail(
            [$superiorEmail],
            "Création d'une demande d'achat",
            'emails.superiorNotification',
            [
                'title' => 'Demande d’achat créée !!',
                'subject' => 'Confirmation de Validation',
                'validationLink' => $validationLink,
               'purchaseRequest' => $purchaseRequest,
            ]
        );
}

        \Log::info('Observer HIT', ['purchase_request_id' => $purchaseRequest->id, 'user_id' => $purchaseRequest->user_id]);
        $this->notifier->sendCreationNotification($purchaseRequest, $purchaseRequest->user,"Demande d'achat");
    }
}



