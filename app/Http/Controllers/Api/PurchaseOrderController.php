<?php

namespace App\Http\Controllers\Api;
use App\Constants\Role;
use App\Services\Pdfs\PurchaseOrderPdfService;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\UpdatePurchaseOrderRequest;
use App\Http\Requests\DeletePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\BudgetLine;
use App\Models\BudgetLineProject;
use App\Models\Collaborator;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use App\Models\Quote;
use App\Models\Supplier;
use App\Models\PurchaseList;
use App\Models\Article;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;


class PurchaseOrderController extends Controller
{
    /**
     * Récupère tous les bons de commande liés à une demande d'achat, avec toutes les lignes (sans pagination)
     */
    public function getByPurchaseRequest($purchaseRequestId): JsonResponse
    {
        if (empty($purchaseRequestId)) {
            return response()->json([
                'error' => 'Le paramètre purchaseRequestId est requis.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!is_numeric($purchaseRequestId) || intval($purchaseRequestId) <= 0) {
            return response()->json([
                'error' => 'Le paramètre purchaseRequestId doit être un entier valide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $purchaseOrders = PurchaseOrder::with(['lines.article.product'])
                ->where('purchase_request_id', $purchaseRequestId)
                ->get();

            return response()->json([
                'data' => $purchaseOrders
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected PurchaseOrderService $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $orders = $this->purchaseOrderService->getAll($request);

            // ✅ CORRECTION : Charger purchaseRequest pour tous les enregistrements
            if (method_exists($orders, 'getCollection') && $request->boolean('with_relations')) {
                $orders->getCollection()->load([
                    'quote', 
                    'supplier', 
                    'issuer.collaborator.superior', 
                    'purchaseRequest' // ✅ Ajouter cette relation
                ]);
            }

            return response()->json([
                'data' => $orders,
            ], Response::HTTP_OK);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['issuer_id']= auth()->id();
            $po = $this->purchaseOrderService
                ->create($data, $request)
                ->load(['quote', 'supplier', 'issuer', 'purchaseRequest']);

            return response()->json([
                'message' => 'Bon de commande créé avec succès',
                'data' => new PurchaseOrderResource($po),
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /***
     * @param PurchaseOrder $purchase_order
     * @return JsonResponse
     */
    public function show(PurchaseOrder $purchase_order): JsonResponse
    {
        try {
            // ✅ CORRECTION : Charger explicitement la relation purchaseRequest
            $purchase_order->load([
                'quote', 
                'supplier', 
                'issuer.collaborator.superior', 
                'purchaseRequest' // Cette relation doit exister dans le modèle
            ]);

            // ✅ AJOUT : Transformer les noms de clés pour correspondre au frontend
            $data = $purchase_order->toArray();
            
            // Convertir purchaseRequest en purchase_request pour le frontend
            if (isset($data['purchase_request'])) {
                $data['purchase_request_id'] = $data['purchase_request']['id'];
            }

            return response()->json([
                'message' => 'Bon de commande récupéré avec succès',
                'purchase_order' => $data, // ✅ Utiliser 'purchase_order' comme clé
            ], Response::HTTP_OK);
            
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /***
     * @param UpdatePurchaseOrderRequest $request
     * @param $purchase_order
     * @return JsonResponse
     */
    public function update(UpdatePurchaseOrderRequest $request, $purchase_order): JsonResponse
    {
        try {

            if ($purchase_order instanceof PurchaseOrder) {
                $purchaseOrderModel = $purchase_order;

                if (!$purchaseOrderModel->exists) {
                    return response()->json(
                        ['error' => 'Bon de commande non trouvé'],
                        Response::HTTP_NOT_FOUND
                    );
                }
            } else {
                $id = (int) $purchase_order;

                if ($id <= 0) {
                    return response()->json(
                        ['error' => 'ID de bon de commande invalide'],
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $purchaseOrderModel = PurchaseOrder::find($id);

                if (!$purchaseOrderModel) {
                    return response()->json(
                        ['error' => 'Bon de commande non trouvé'],
                        Response::HTTP_NOT_FOUND
                    );
                }
            }

            if ($request->status === 'approved' && $request->filled('items')) {
                $purchaseOrderModel->load('purchaseRequest');
                $projectId = $purchaseOrderModel->purchaseRequest->project_id ?? null;
                if (!$projectId) {
                    throw new \Exception('Projet introuvable pour ce bon de commande');
                }
                foreach ($request->items as $item) {
                    $totalHt  = $item['unit_price_ht'] * $item['quantity'];
                    $tva      = $totalHt * $item['tva_rate'];
                    $totalTtc = $totalHt + $tva;
                    $prLine = PurchaseRequestLine::where('product_id', $item['product_id'])
                        ->latest()
                        ->first();
                    if (!$prLine || !$prLine->budget_line_id) {
                        throw new \Exception(
                            "Aucune ligne budgétaire trouvée pour le produit ID {$item['product_id']}"
                        );
                    }
                    $budgetLineProject = BudgetLineProject::where('budget_line_id', $prLine->budget_line_id)
                        ->where('project_id', $projectId)
                        ->firstOrFail();
                    $budgetLineProject->increment('engaged_amount', $totalTtc);
                    $budgetLineProject->update(['status' => 'engaged']);
                }
            }

            $data = $request->validated();
            unset($data['items']);

            $po = $this->purchaseOrderService
                ->update($purchaseOrderModel->id, $data)
                ->load(['quote', 'supplier', 'issuer', 'purchaseRequest']);

            return response()->json([
                'message' => 'Bon de commande mis à jour avec succès',
                'data'    => new PurchaseOrderResource($po),
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            if ($id instanceof PurchaseOrder) {
                $purchaseOrder = $id;
                if (!$purchaseOrder->exists) {
                    return response()->json(['error' => 'Bon de commande non trouvé'], Response::HTTP_NOT_FOUND);
                }
            } else {
                $idValue = (int) $id;
                if ($idValue <= 0) {
                    return response()->json(['error' => 'ID de bon de commande invalide'], Response::HTTP_BAD_REQUEST);
                }
                $purchaseOrder = PurchaseOrder::find($idValue);
                if (!$purchaseOrder) {
                    return response()->json(['error' => 'Bon de commande non trouvé'], Response::HTTP_NOT_FOUND);
                }
            }

            $this->purchaseOrderService->delete($purchaseOrder->id);

            return response()->json([
                'message' => 'Bon de commande supprimé avec succès',
                'id' => $purchaseOrder->id
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(DeletePurchaseOrderRequest $request): JsonResponse
    {
        try {
            $ids = $request->validated()['ids'] ?? [];
            $this->purchaseOrderService->bulkDestroy($ids);

            return response()->json([
                'message' => count($ids) . ' bon(s) de commande supprimé(s) avec succès !'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore(PurchaseOrder $purchase_order): JsonResponse
    {
        try {
            $po = $this->purchaseOrderService->restore($purchase_order->id);

            return response()->json([
                'message' => 'Bon de commande restauré avec succès',
                'data' => new PurchaseOrderResource($po),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur serveur: ' . $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    public function options(): JsonResponse
    {
        try {
            return response()->json([
                'statuses' => [
                    'draft' => 'Brouillon',
                    'in_review' => 'En validation',
                    'approved' => 'Validé',
                    'cancelled' => 'Annulé',
                    'rejected' => 'Rejeté',
                ],
                'currencies' => ['MAD', 'EUR', 'USD'],
                'payment_methods' => [
                    'transfer' => 'Virement',
                    'check' => 'Chèque',
                    'cash' => 'Espèces',
                ],
                'suppliers' => Supplier::select('id','trade_name','company_name','supplier_id')->get(),
                'quotes' => Quote::with('supplier')
                    ->select('id', 'quote_number', 'status', 'purchase_list_id', 'total_amount_ht', 'vat_amount', 'total_amount_ttc', 'supplier_id')->get(),
                'issuers' => Collaborator::select('id','last_name','first_name')->get(),
                'purchase_requests' => PurchaseRequest::select('id','code')->get(),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    /**
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function validatePurchaseOrder(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:validate,reject'
        ]);

        try {
            $purchaseOrder = PurchaseOrder::find($id);

            if (!$purchaseOrder) {
                return response()->json(['error' => 'Bon de commande non trouvé'], Response::HTTP_NOT_FOUND);
            }

            if (in_array($purchaseOrder->status, ['approved', 'cancelled', 'rejected'])) {
                return response()->json([
                    'error' => 'Ce bon de commande est déjà traité (Statut : ' . $purchaseOrder->status . ')'
                ], 400);
            }

            /** @var User $user */
            $user = auth()->user();
            $action = $request->input('action');


            if ($action === 'reject') {
                if (!$user->hasAnyRole([Role::RESPONSABLE_ACHAT, Role::CONTROLE_DE_GESTION, Role::DIRECTEUR_GENERAL, Role::ADMIN_SI])) {
                    return response()->json(['error' => 'Vous n\'avez pas les droits pour rejeter ce document.'], 403);
                }

                $purchaseOrder->status = 'cancelled';
                $purchaseOrder->save();

                return response()->json([
                    'message' => 'Bon de commande rejeté.',
                    'data' => new PurchaseOrderResource($purchaseOrder)
                ], Response::HTTP_OK);
            }


            $message = '';

            // Step 1: Responsable Achat
            if (!$purchaseOrder->validated_by_procurement_manager) {
                if (!$user->hasRole(Role::RESPONSABLE_ACHAT) && !$user->hasRole(Role::ADMIN_SI)) {
                    return response()->json(['error' => 'Étape 1 : Seul le Responsable Achat peut valider à ce stade.'], 403);
                }

                $purchaseOrder->validated_by_procurement_manager = true;
                $message = 'Validé par le Responsable Achats. En attente du Contrôle de Gestion.';
            }
            // Step 2: Contrôle de Gestion (Requires Step 1)
            elseif (!$purchaseOrder->validated_by_controlling) {
                if (!$user->hasRole(Role::CONTROLE_DE_GESTION) && !$user->hasRole(Role::ADMIN_SI)) {
                    return response()->json(['error' => 'Étape 2 : Seul le Contrôleur de Gestion peut valider à ce stade.'], 403);
                }

                $purchaseOrder->validated_by_controlling = true;
                $message = 'Validé par le Contrôle de Gestion. En attente de la Direction Générale.';
            }
            // Step 3: Directeur Général (Requires Step 1 & 2)
            elseif (!$purchaseOrder->validated_by_board) {
                if (!$user->hasRole(Role::DIRECTEUR_GENERAL) && !$user->hasRole(Role::ADMIN_SI)) {
                    return response()->json(['error' => 'Étape 3 : Seul le Directeur Général peut finaliser la validation.'], 403);
                }

                $purchaseOrder->validated_by_board = true;
                $purchaseOrder->status = 'approved';
                $message = 'Bon de commande validé et finalisé par la Direction Générale.';
            }
            else {
                return response()->json(['message' => 'Ce bon de commande est déjà entièrement validé.'], 200);
            }


            $purchaseOrder->save();

            // Après validation, mettre à jour le(s) marché(s) lié(s) par po_number
            if ($purchaseOrder->status === 'approved') {
                $poNumber = $purchaseOrder->po_number;
                // Chercher tous les marchés dont purchase_order_refs contient ce po_number
                $calltenders = \App\Models\Calltender::whereNotNull('purchase_order_refs')->get();
                foreach ($calltenders as $calltender) {
                    $refs = array_map('trim', explode(',', $calltender->purchase_order_refs));
                    if (in_array($poNumber, $refs)) {
                        // Recalculer la somme des BC validés référencés
                        $validPoNumbers = $refs;
                        $used = \App\Models\PurchaseOrder::whereIn('po_number', $validPoNumbers)
                            ->where('status', 'approved')
                            ->sum('total_amount_ttc');
                        // On diminue le montant du marché
                        $calltender->total_amount = (float)$calltender->total_amount - (float)$used;
                        $calltender->save();
                    }
                }
            }

            return response()->json([
                'message' => $message,
                'data' => new PurchaseOrderResource($purchaseOrder)
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la validation: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function quotePurchaseRequests(int $quoteId): JsonResponse
    {
        try {
            $quote = Quote::with(['purchaseList.requests'])->find($quoteId);
            if (!$quote) {
                return response()->json(['error' => 'Quote not found'], Response::HTTP_NOT_FOUND);
            }
            $purchaseRequests = $quote->purchaseList && $quote->purchaseList->requests
                ? $quote->purchaseList->requests
                : [];
            return response()->json(['data' => $purchaseRequests], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function quotePurchaseRequestItems(Request $request, int $quoteId, int $purchaseRequestId)
    {
        $quote = Quote::with(['items.article.product'])->findOrFail($quoteId);
        $pr = PurchaseRequest::with(['products.product'])->findOrFail($purchaseRequestId);

        $items = [];
        $totalTTC = 0.0;

        foreach ($quote->items as $qi) {
            $article = $qi->article;
            if (!$article || !$article->product) continue;

            $product = $article->product;
            $matchingPrLine = $pr->products->firstWhere('product_id', $product->id);
            if (!$matchingPrLine) continue;

            $quantity = (float)($qi->quantity ?? $matchingPrLine->quantity ?? 0);
            $unitPriceHt = (float)($qi->unit_price_ht ?? $matchingPrLine->unit_price ?? 0);
            $tvaRate = (float)($qi->tva_rate ?? $quote->vat_rate ?? 0);
            $brand = $qi->brand ?? $article->brand ?? $product->brand ?? null;

            $lineTotal = $quantity * $unitPriceHt * (1 + $tvaRate / 100);
            $totalTTC += $lineTotal;

            $items[] = [
                'purchase_request_line_id' => $matchingPrLine->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'brand' => $brand,
                'quantity' => $quantity,
                'unit_price_ht' => $unitPriceHt,
                'tva_rate' => $tvaRate,
                'article_id' => $article->id,
                'article_name' => $article->name,
                'ambiguous_quote_match' => false,
            ];
        }

        return response()->json([
            'quote_id' => $quote->id,
            'purchase_request_id' => $pr->id,
            'items' => $items,
            'total_ttc' => round($totalTTC, 2),
        ]);
    }

    public function getPurchaseListByQuote(int $quoteId): JsonResponse
    {
        try {
            $quote = Quote::find($quoteId);

            if (!$quote) {
                return response()->json(['error' => 'Quote not found'], Response::HTTP_NOT_FOUND);
            }

            if (!$quote->purchase_list_id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Quote has no purchase list',
                    'items' => [],
                    'count' => 0
                ], Response::HTTP_OK);
            }

            $purchaseList = PurchaseList::with(['items' => function($query) {
                $query->with('product');
            }])->find($quote->purchase_list_id);

            if (!$purchaseList) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase list not found',
                    'items' => [],
                    'count' => 0
                ], Response::HTTP_OK);
            }

            $items = [];
            foreach ($purchaseList->items as $item) {
                $pivot = $item->pivot;

                $quantity = $pivot->quantity ?? 1;
                $unitPriceHt = $item->reference_price ?? 0;
                $tvaRate = 20;

                $totalHt = $quantity * $unitPriceHt;
                $totalTTC = $totalHt * (1 + $tvaRate / 100);

                $items[] = [
                    'id' => $item->id,
                    'article_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price_ht' => $unitPriceHt,
                    'tva_rate' => $tvaRate,
                    'total_ht' => $totalHt,
                    'total_ttc' => $totalTTC,
                    'article' => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'reference' => $item->article_id,
                        'designation' => $item->specifications,
                        'brand' => $item->brand
                    ]
                ];
            }

            return response()->json([
                'success' => true,
                'quote_id' => $quoteId,
                'quote_number' => $quote->quote_number,
                'purchase_list_id' => $quote->purchase_list_id,
                'items' => $items,
                'count' => count($items),
                'debug_info' => [
                    'purchase_list_found' => true,
                    'items_loaded' => $purchaseList->items->count()
                ]
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function purchaseRequestProducts(int $purchaseRequestId): JsonResponse
    {
        try {
            $pr = PurchaseRequest::with(['products' => function($query) {
                $query->with('product');
            }])->findOrFail($purchaseRequestId);

            $items = [];
            $totalTTC = 0.0;

            foreach ($pr->products as $index => $prLine) {
                $product = $prLine->product;
                $quantity = (float) ($prLine->quantity ?? 0);
                $unitPriceHt = (float) ($prLine->unit_price_ht ?? $prLine->unit_price ?? 0);

                $tvaRate = 20.0;

                if (isset($prLine->tva_rate)) {
                    $tvaRate = (float) $prLine->tva_rate;
                } elseif ($product && isset($product->tva_rate)) {
                    $tvaRate = (float) $product->tva_rate;
                } elseif (isset($prLine->tva)) {
                    $tvaRate = (float) $prLine->tva;
                }

                $totalHt = $quantity * $unitPriceHt;
                $lineTotal = $totalHt * (1 + $tvaRate / 100);
                $totalTTC += $lineTotal;

                $items[] = [
                    'id' => $prLine->id,
                    'purchase_request_line_id' => $prLine->id,
                    'product_id' => $prLine->product_id,
                    'product_name' => $product ? $product->name : 'Produit non spécifié',
                    'brand' => $product ? ($product->brand ?? $product->marque ?? null) : ($prLine->brand ?? null),
                    'quantity' => $quantity,
                    'unit_price_ht' => $unitPriceHt,
                    'tva_rate' => $tvaRate,
                    'technical_justification' => $prLine->technical_justification ?? $prLine->justification ?? '',
                    'total_ht' => $totalHt,
                    'total_ttc' => $lineTotal,
                ];
            }

            return response()->json([
                'success' => true,
                'purchase_request_id' => $pr->id,
                'purchase_request_code' => $pr->code,
                'items' => $items,
                'total_ttc' => round($totalTTC, 2),
                'count' => count($items)
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Purchase request not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'error while loading products',
                'message' => 'An error occurred while loading purchase request products'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getQuoteDetails(int $quoteId): JsonResponse
    {
        try {
            $quote = Quote::with([
                'purchaseList.items.article',
                'supplier'
            ])->find($quoteId);

            if (!$quote) {
                return response()->json(['error' => 'Quote not found'], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'total_ht' => $quote->total_ht,
                'total_tva' => $quote->total_tva,
                'total_ttc' => $quote->total_ttc,
                'purchase_list_id' => $quote->purchase_list_id,
                'supplier_id' => $quote->supplier_id,
                'items' => $quote->purchaseList ? $quote->purchaseList->items : [],
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json(['error' => 'error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /***
     * @param int $purchaseOrderId
     * @return JsonResponse
     */
    public function getPurchaseOrderLines(int $purchaseOrderId): JsonResponse
    {
        try {
            $lines = $this->purchaseOrderService->getPurchaseOrderLines($purchaseOrderId);

            return response()->json([
                'items' => $lines
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json(
                ['error' => $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    // Dans PurchaseOrderController.php
// Remplacer les méthodes pdfPreview et pdfDownload par ce code :

public function pdfPreview(int $id, PurchaseOrderPdfService $pdfService) {
    $po = PurchaseOrder::with([
        'quote',
        'quote.items.article',
        'supplier',
        'issuer',
        'purchaseRequest',
        'purchaseRequest.products.product'
    ])->findOrFail($id);

    $po->products_details = $po->purchaseRequest && $po->purchaseRequest->products
        ? collect($po->purchaseRequest->products)->map(function($prLine) {
            $product = $prLine->product;
            $quantity = (float) ($prLine->quantity ?? 0);
            $unitPriceHt = (float) ($prLine->unit_price_ht ?? $prLine->unit_price ?? 0);
            $tvaRate = isset($prLine->tva_rate) ? (float) $prLine->tva_rate : 20.0;
            $totalHt = $quantity * $unitPriceHt;
            $totalTtc = $totalHt * (1 + $tvaRate / 100);
            return [
                'designation' => $product ? $product->name : 'Produit non spécifié',
                'quantity' => $quantity,
                'unit_price_ht' => $unitPriceHt,
                'tva_rate' => $tvaRate,
                'total_ht' => $totalHt,
                'total_ttc' => $totalTtc,
                'product_id' => $prLine->product_id,
                'article_id' => $prLine->article_id ?? null,
            ];
        })
        : collect();

    if ($po->status !== 'approved') {
        return response()->json([
            'error' => 'Bon de commande non validé'
        ], 403);
    }

    $path = $pdfService->generate($po);
    return response()->file($path);
}

public function pdfDownload(int $id, PurchaseOrderPdfService $pdfService) {
    $po = PurchaseOrder::with([
        'quote',
        'quote.items.article',
        'supplier',
        'issuer',
        'purchaseRequest',
        'purchaseRequest.products.product'
    ])->findOrFail($id);

    $po->products_details = $po->purchaseRequest && $po->purchaseRequest->products
        ? collect($po->purchaseRequest->products)->map(function($prLine) {
            $product = $prLine->product;
            $quantity = (float) ($prLine->quantity ?? 0);
            $unitPriceHt = (float) ($prLine->unit_price_ht ?? $prLine->unit_price ?? 0);
            $tvaRate = isset($prLine->tva_rate) ? (float) $prLine->tva_rate : 20.0;
            $totalHt = $quantity * $unitPriceHt;
            $totalTtc = $totalHt * (1 + $tvaRate / 100);
            return [
                'designation' => $product ? $product->name : 'Produit non spécifié',
                'quantity' => $quantity,
                'unit_price_ht' => $unitPriceHt,
                'tva_rate' => $tvaRate,
                'total_ht' => $totalHt,
                'total_ttc' => $totalTtc,
                'product_id' => $prLine->product_id,
                'article_id' => $prLine->article_id ?? null,
            ];
        })
        : collect();

    $path = $pdfService->generate($po);
    return response()->download(
        $path,
        "purchase-order-{$po->po_number}.pdf"
    );
}


}
