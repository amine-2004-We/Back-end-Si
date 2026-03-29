<?php

namespace App\Http\Controllers\Api;

use App\Constants\Role;
use App\Services\DeliveryOrderService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Http\Resources\DeliveryOrderResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreDeliveryOrderRequest;
use App\Http\Requests\UpdateDeliveryOrderRequest;
use App\Models\DeliveryOrder;
use App\Models\PurchaseOrder;
use App\Services\Notification\MailService;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;



class DeliveryOrderController extends Controller
{
    //
    

    protected $deliveryOrderService;

    public function __construct(DeliveryOrderService $deliveryOrderService)
    {
        $this->deliveryOrderService = $deliveryOrderService;
    }

   public function index(Request $request)
{
    try{
        $params = $request->all();
        $deliveryOrders = $this->deliveryOrderService->getDeliveryOrders($params);
        $deliveryOrders->load('items');
       
        return response()->json([
            'data' => DeliveryOrderResource::collection($deliveryOrders),
            'pagination' => [
                'total' => $deliveryOrders->total(),
                'count' => $deliveryOrders->count(),
                'per_page' => $deliveryOrders->perPage(),
                'current_page' => $deliveryOrders->currentPage(),
                'total_pages' => $deliveryOrders->lastPage(),
            ]
        ], Response::HTTP_OK);
    } catch (Exception $e) {
        Log::error('Error fetching delivery orders: ' . $e->getMessage());
        return response()->json(['message' => 'An error occurred while fetching delivery orders'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    public function show($id)
    {
        try {
            $deliveryOrder = $this->deliveryOrderService->findDeliveryOrderById($id);
            $deliveryOrder->load(['items', 'deliveryRequest']);
            return response()->json([
                'data' => new DeliveryOrderResource($deliveryOrder)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Delivery order not found: ' . $e->getMessage());
            return response()->json(['message' => 'Delivery order not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Error fetching delivery order: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching the delivery order'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(StoreDeliveryOrderRequest $request)
    {
        try {
            $data = $request->validated();
            $items=$data['items'] ?? [];
            $deliveryOrder = $this->deliveryOrderService->createDeliveryOrder($data, $items);
            return response()->json(new DeliveryOrderResource($deliveryOrder), Response::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('Error creating delivery order: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while creating the delivery order'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateDeliveryOrderRequest $request, $id)
    {
        try {
            $data = $request->validated();
            $deliveryOrder = $this->deliveryOrderService->updateDeliveryOrder($id, $data);
            return response()->json(new DeliveryOrderResource($deliveryOrder), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Delivery order not found: ' . $e->getMessage());
            return response()->json(['message' => 'Delivery order not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Error updating delivery order: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating the delivery order'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $this->deliveryOrderService->deleteDeliveryOrder($id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            Log::error('Delivery order not found: ' . $e->getMessage());
            return response()->json(['message' => 'Delivery order not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Error deleting delivery order: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while deleting the delivery order'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function restore($id)
    {
        try {
            $this->deliveryOrderService->restoreDeliveryOrder($id);
            return response()->json(['message' => 'Delivery order restored successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Delivery order not found: ' . $e->getMessage());
            return response()->json(['message' => 'Delivery order not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Error restoring delivery order: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while restoring the delivery order'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:delivery_orders,id',
        ]);
        try {
            $deletedCount = $this->deliveryOrderService->bulkDeleteDeliveryOrders($validated['ids']);
            return response()->json([
                'message' => "$deletedCount delivery order(s) deleted."
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error bulk deleting delivery orders: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while bulk deleting delivery orders'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }
 




    public function getPurchaseOrderArticles(int $id)
    {
        try {

            // eager load supplier and nested relations for the single purchaseRequest
            $purchaseOrder = PurchaseOrder::with('supplier', 'purchaseRequest.products.product.articles')->findOrFail($id);

            // use the purchase order supplier as a single value for the whole response
            $poSupplier = $purchaseOrder->supplier;
            $supplierPayload = $poSupplier ? [
                'id' => $poSupplier->id,
                'company_name' => $poSupplier->company_name,
            ] : null;

            // get all lines from the PO's single purchase request
            $lines = collect();
            if ($purchaseOrder->purchaseRequest) {
                $lines = $purchaseOrder->purchaseRequest->products;
            }

            // For each purchase request line, return one entry per article of the related product
            $items = $lines->flatMap(function ($line) {
                $product = $line->product ?? null;
                $articles = $product ? ($product->articles ?? collect()) : collect();

                // fallback entry when no article exists for the product
                if ($articles->isEmpty()) {
                    return [[
                        'purchase_request_id' => $line->purchase_request_id ?? null,
                        'line_id' => $line->id ?? null,
                        'product_id' => $line->product_id ?? null,
                        'article_id' => null,
                        'article_name' => null,
                        'quantity' => $line->quantity ?? null,
                    ]];
                }

                // map each article to an item row (supplier omitted here; it's in top-level payload)
                return $articles->map(function ($article) use ($line) {
                    return [
                        'purchase_request_id' => $line->purchase_request_id ?? null,
                        'line_id' => $line->id ?? null,
                        'product_id' => $line->product_id ?? null,
                        'article_id' => $article->id ?? null,
                        'article_name' => $article->name ?? null,
                        'quantity' => $line->quantity ?? null,
                    ];
                });
            })->values();

            return response()->json([
                'supplier' => $supplierPayload,
                'items' => $items,
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::error('Delivery order not found: ' . $e->getMessage());
            return response()->json(['message' => 'Delivery order not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Error fetching delivery order articles: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while fetching the delivery order articles'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    //validate delivery order from these statuses: Préparé,Émis,Livré,Annulé
    public function validateDeliveryOrder(Request $request, DeliveryOrder $deliveryOrder)
{
    $user = $request->user();

    $validated = $request->validate([
        'action' => ['required', 'in:validate,reject'],
    ]);
    \Log::info('status ',['status'=>$deliveryOrder]);
    $currentStatus = $deliveryOrder->status;
    if ($currentStatus !== "En attente") {
            \Log::info('Delivery order not pending', ['delivery_order_id' => $deliveryOrder->id, 'enum-status'=>DeliveryOrderStatus::ENATTENT->value]);
        return response()->json([
            'error' => 'Cette demande n’est plus en attente.'
        ], 409);
    }

    $collaborator = $deliveryOrder->creator?->collaborator;

    if (
        !$collaborator ||
        !$collaborator->superior ||
        (
            $collaborator->superior->user_id !== $user->id &&
            !$user->hasRole(Role::ADMIN_SI)
        )
    ) {
        return response()->json([
            'error' => 'Vous n’êtes pas autorisé à traiter cette demande.'
        ], 403);
    }

    // 4. Decision
    $deliveryOrder->status =
        $validated['action'] === 'reject'
           ?"Refusée"
            : "Validée";

    $deliveryOrder->save();

   \Log::info('Delivery order data', ['delivery_order' => $deliveryOrder->status]);

    $this->generateEmail( $deliveryOrder->status,$deliveryOrder);

    return response()->json([
        'message' => 'Décision enregistrée avec succès.',
        'delivery_order' => $deliveryOrder,
    ]);
}

    public function generateEmail(string $status, DeliveryOrder $deliveryOrder)
    {
        //log received data
        \Log::info('Generating email for delivery order', ['status' => $status]);
        try{
           $creatorEmail = $deliveryOrder->creator?->email;


    MailService::sendMail(
            [$creatorEmail,'i.ennajy@fondationzakoura.org'],
            "Décision sur l'ordre de livraison",
            'emails.deliveryOrders.validationDecision',
            [
                'title' => 'Décision sur l\'ordre de livraison',
                'subject' => 'Décision sur l\'ordre de livraison',
                'status'=>$status,
               'delivery_order' => $deliveryOrder,
            ]
        );
        \Log::info("Delivery order decision email sent to: " . $creatorEmail);
    }
    catch(\Exception $e){
        \Log::error("Error sending delivery order decision email: " . $e->getMessage());
}

}



}
