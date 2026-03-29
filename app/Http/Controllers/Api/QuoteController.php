<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteRequest;
use App\Http\Requests\UpdateQuoteRequest;
use App\Http\Requests\DeleteQuoteRequest;
use App\Http\Requests\UpdateQuoteAttachmentRequest;
use App\Models\Article;
use App\Models\PurchaseList;
use App\Models\Quote;
use App\Models\Supplier;
use App\Services\Notification\MailService;
use App\Services\QuoteService;
use App\Traits\UploadFileTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * class QuoteController
 */
class QuoteController extends Controller
{
    use UploadFileTrait;
    /**
     * @var QuoteService
     */
    public QuoteService $quoteService;

    /**
     * @param QuoteService $quoteService
     */
    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $quotes = $this->quoteService->getAll($request);

            if (method_exists($quotes, 'getCollection') && $request->boolean('with_relations')) {
                $quotes->getCollection()->load([
                    'items.article', 'supplier', 'purchaseList','createdBy.collaborator.superior.user'
                ]);
            }

            return response()->json($quotes, Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur dans le serveur! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param StoreQuoteRequest $request
     * @return JsonResponse
     */
    public function store(StoreQuoteRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            if($request->hasFile('attachment_path')) {
                $attachementPath = $this->uploadPublicFile($request->file('attachment_path'), 'quotes');
                $data['attachment_path'] = $attachementPath;
            }

            $quote = $this->quoteService
                ->create($data)
                ->load(['items', 'supplier', 'purchaseList','createdBy']);

            return response()->json([
                'message' => 'Devis créé avec succès',
                'quote'   => $quote,
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur dans le serveur! ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Quote $quote
     * @return JsonResponse
     */
    public function show(Quote $quote): JsonResponse
    {
        try {
            $quote = $this->quoteService->show((int) $quote->id);

            if (!$quote) {
                return response()->json(['message' => 'Devis non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $quote->load(['items', 'supplier', 'purchaseList','createdBy']);

            return response()->json([
                'message' => 'Devis récupéré avec succès',
                'quote'   => $quote,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du devis: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param UpdateQuoteRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateQuoteRequest $request, int $id): JsonResponse
    {
        try {
            $quote = $this->quoteService->show($id);

            if (!$quote) {
                return response()->json(['message' => 'Devis non trouvé'], Response::HTTP_NOT_FOUND);
            }

            $data = $request->validated();

            if ($request->hasFile('attachment_path')) {
                $data['attachment_path'] = $this->uploadPublicFile($request->file('attachment_path'), 'quotes');
            } else {
                unset($data['attachment_path']);
            }

            $updatedQuote = $this->quoteService->update($data, $quote)
                ->load(['items', 'supplier', 'purchaseList']);

            return response()->json([
                'message' => 'Devis mis à jour avec succès',
                'quote'   => $updatedQuote,
            ], Response::HTTP_OK);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour du devis: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Quote $quote
     * @return JsonResponse
     */
    public function destroy(Quote $quote): JsonResponse
    {
        try {
            $this->quoteService->delete((int) $quote->id);

            return response()->json([
                'message' => 'Devis supprimé avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression du devis: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param DeleteQuoteRequest $request
     * @return JsonResponse
     */
    public function bulkDelete(DeleteQuoteRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $ids = $validated['ids'] ?? [];

            $this->quoteService->bulkDestroy($ids);

            return response()->json([
                'message' => count($ids) . ' devis supprimé(s) avec succès !'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la suppression des devis: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Quote $quote
     * @return JsonResponse
     */
    public function restore(Quote $quote): JsonResponse
    {
        try {
            $quote = $this->quoteService->restore((int) $quote->id);

            return response()->json([
                'message' => 'Devis restauré avec succès',
                'quote'   => $quote,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la restauration du devis: ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * @return JsonResponse
     */
    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'statuses' => [
                    'En attente'  => 'En attente',
                    'Validé' => 'Validé',
                    'Refusé' => 'Refusé',
                ],
                'payment_terms' => [
                    'upon_receipt'  => 'À réception',
                    '30d_end_month' => '30j fin de mois',
                    'other'         => 'Autre',
                ],
                'suppliers'         => Supplier::select('id','supplier_id','company_name','trade_name')->get(),
                'purchase_lists' => PurchaseList::with('items')->get(),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération des options: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param  UpdateQuoteAttachmentRequest $request
     * @param  Quote $quote
     * @return JsonResponse
     */
    public function updateAttachment(UpdateQuoteAttachmentRequest $request, Quote $quote): JsonResponse
    {
        try {
            $this->quoteService->updateAttachment($request,$quote->id);

            return response()->json([
                'message' => 'Pièce jointe mise à jour avec succès',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour de la pièce jointe: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param Quote $quote
     * @return JsonResponse
     */
    public function validateQuote(Request $request, Quote $quote)
    {
        try {

            $user = $request->user();
            $creator = $quote->createdBy;

            if (!$user) {
                abort(401, 'Non authentifié.');
            }
            
            /*
            if ($creator) {
                $creator->load('collaborator.superior');
                
                $superiorUserId = optional(optional($creator->collaborator)->superior)->user_id;
                
                if ($superiorUserId && $superiorUserId !== $user->id) {
                    abort(403, 'Seul le supérieur du collaborateur peut valider le devis.');
                }
            }
            */
            
            $quote->status = 'Validé';
            $quote->save();
              $this->generateEmail( $quote->status,$quote);

            return response()->json(['message' => 'Devis validé avec succès']);
            
        } catch (\Exception $e) {
            
            return response()->json([
                'error' => 'Erreur lors de la validation du devis: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param Quote $quote
     * @return JsonResponse
     */
    public function rejectQuote(Request $request, Quote $quote)
    {
        try {
        
            $user = $request->user();
            $creator = $quote->createdBy;
            
            if (!$user) {
                abort(401, 'Non authentifié.');
            }

            /*
            if ($creator) {
                $creator->load('collaborator.superior');
                
                $superiorUserId = optional(optional($creator->collaborator)->superior)->user_id;
                
                if ($superiorUserId && $superiorUserId !== $user->id) {
                    abort(403, 'Seul le supérieur du collaborateur peut rejeter le devis.');
                }
            }
            */
            
            $quote->status = 'Refusé';
            $quote->save();
                $this->generateEmail( $quote->status,$quote);

            return response()->json(['message' => 'Devis refusé avec succès']);
            
        } catch (\Exception $e) {
            
            return response()->json([
                'error' => 'Erreur lors du rejet du devis: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

     public function generateEmail(string $status,Quote $quote)
    {
        //log received data
        \Log::info('Generating email for quote', ['status' => $status]);
        try{
           $creatorEmail = $quote->createdBy?->email;


    MailService::sendMail(
            [$creatorEmail,'i.ennajy@fondationzakoura.org'],
            "Décision sur un devis",
            'emails.quotes.validationDecision',
            [
                'title' => 'Décision sur le devis',
                'subject' => 'Décision sur le devis',
                'status'=>$status,
               'quote' => $quote,
            ]
        );
        \Log::info("Purchase request decision email sent to: " . $creatorEmail);
    }
    catch(\Exception $e){
        Log::error("Error sending quote decision email: " . $e->getMessage());
}
    }
}