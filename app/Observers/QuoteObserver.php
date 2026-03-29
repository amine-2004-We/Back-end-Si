<?php

namespace App\Observers;

use App\Models\Quote;
use App\Models\Supplier;
use App\Services\Notification\MailService;
use App\Services\Notification\ObserverNotificationService;

class QuoteObserver
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
    public function creating(Quote $quote): void
    {
        $quote->created_by = auth()->user()->id;
        $this->generateQuoteNumberIfNeeded($quote);
    }

    public function updating(Quote $quote): void
    {
        if($quote->created_by == null){
             $quote->created_by = auth()->user()->id;
        }
        $this->generateQuoteNumberIfNeeded($quote);
    }

    private function generateQuoteNumberIfNeeded(Quote $quote): void
    {
        // Générer uniquement si supplier_id est défini et quote_number vide ou supplier_id changé
        if ($quote->supplier_id && (!$quote->quote_number || $quote->isDirty('supplier_id'))) {
            $year = now()->format('Y');

            $supplierNumber = Supplier::find($quote->supplier_id)?->supplier_id;
            if (!$supplierNumber) {
                // Stop si fournisseur invalide
                return;
            }

            $countThisYear = Quote::withTrashed()
                ->where('supplier_id', $quote->supplier_id)
                ->whereYear('created_at', $year)
                ->count();

            $seq  = str_pad((string) ($countThisYear + 1), 4, '0', STR_PAD_LEFT);

            $quote->quote_number = "DEV-{$year}-{$supplierNumber}-{$seq}";
        }
    }

     public function created(Quote $quote): void
    {

        $creatorEmail = $quote->createdBy?->email;
        \Log::info('creator email',['creator_email'=>$creatorEmail]);
        MailService::sendMail(
            ['najiihsane22@gmail.com',$creatorEmail],
            "Création d'un devis",
            'emails.quotes.creationNotification',
            [
                'title' => 'Devis créé !!',
                'subject' => 'Confirmation de création',
               'quote' => $quote,
            ]
        );


        $superior = $quote->createdBy?->collaborator->superior;
        if ($superior) {
        $superiorEmail = $superior->email;

        $defaultUrl = 'http://fzedprod1.eastus.cloudapp.azure.com';

$baseUrl = app()->environment('local')
    ? env('FRONTEND_URL_LOCAL')
    : (app()->environment('testing')
        ? env('FRONTEND_URL_PROD', 'http://fzed.eastus.cloudapp.azure.com')
        : $defaultUrl);
           


 
         $validationLink = rtrim($baseUrl, '/') . '/quotes';

    
    MailService::sendMail(
            [$superiorEmail,'i.ennajy@fondationzakoura.org'],
            "Création d'un devis",
            'emails.quotes.superiorNotification',
            [
                  'title' => 'Devis créé !!',
                'subject' => 'Confirmation de création',
               'quote' => $quote,
                'validationLink' => $validationLink,
            ]
        );
}

        \Log::info('Observer HIT', ['quote_id' => $quote->id, 'user_id' => $quote->createdBy?->id]);
        $this->notifier->sendCreationNotification($quote, $quote->createdBy, "Devis");
    }
}
