<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePurchaseListRequest;
use App\Models\Article;
use App\Models\Departement;
use App\Models\PurchaseList;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use App\Models\User;
use App\Services\Notification\MailService;
use App\Services\PurchaseListService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Services\pdfs\PurchaseListPdfService;





class PurchaseListController extends Controller
{
    public function index(Request $request)
    {
        $perPage       = (int) $request->input('perPage', 15);
        $page          = (int) $request->input('page', 1);
        $sortBy        = $request->input('sortBy', 'created_at');
        $sortDir       = strtolower($request->input('sortDir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = PurchaseList::query();

        $query->with([
            'requests.project',
            'items.product',
            'createdBy.collaborator.superior.user',
            'quotes.items',
            'quotes.supplier',
            'department',
        ]);

        if ($request->filled('purchase_list_id')) {
            $needle = $request->input('purchase_list_id');
            $query->where('purchase_list_id', 'like', '%' . $needle . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', (int) $request->input('created_by'));
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->input('created_from'));
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->input('created_to'));
        }

        if ($request->filled('has_quotes')) {
            $has = (int) $request->input('has_quotes') === 1;
            $query->whereHas('quotes', function ($q) {
            }, $has ? '>' : '=', 0);
        }

        $sortable = ['created_at', 'updated_at', 'purchase_list_id', 'status', 'priority', 'total_quantity_requested'];
        if (!in_array($sortBy, $sortable, true)) {
            $sortBy = 'created_at';
        }
        $query->orderBy($sortBy, $sortDir);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json($paginator);
    }


    public function show($id)
    {
        $purchaseList = PurchaseList::with([
            'requests',
            'items',
            'createdBy',
            'quotes.supplier',
            'quotes.items',
        ])->find($id);

        if (!$purchaseList) {
            return response()->json(['error' => 'Purchase List not found'], 404);
        }

        return response()->json($purchaseList);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'purchase_request_ids' => 'required|array|min:1',
            'purchase_request_ids.*' => 'integer|exists:purchase_requests,id',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|integer|exists:articles,id',
            'articles.*.quantity' => 'required|numeric|min:1',
            'priority' => 'nullable|in:High,Medium,Low',
            'observations' => 'nullable|string',
            'emitting_department' => 'required|integer|exists:departements,id',
        ]);

        $purchaseListService = new PurchaseListService();
        
        $purchaseList = $purchaseListService->create([
            'created_by' => Auth::id(),
            'emitting_department' => $data['emitting_department'], 
            'priority' => $data['priority'] ?? null,
            'observations' => $data['observations'] ?? null,
            'total_quantity_requested' => collect($data['articles'])->sum('quantity'),
            'status' => 'in_progress',
        ]);

        $purchaseList->requests()->attach($data['purchase_request_ids']);

        $syncPayload = [];
        foreach ($data['articles'] as $article) {
            $syncPayload[$article['article_id']] = ['quantity' => $article['quantity']];
        }
        $purchaseList->items()->sync($syncPayload);

        return response()->json($purchaseList->load(['requests','items','creator']), 201);
    }

    public function getPurchaseRequestLinesByPurchaseListId($id)
    {
        $lines = PurchaseRequestLine::where('purchase_request_id', $id)->with('product.articles')->get();

        return response()->json($lines);
    }


    public function update(UpdatePurchaseListRequest $request, $id): JsonResponse
    {
        $purchaseList = PurchaseList::find($id);

        if (!$purchaseList) {
            return response()->json(['error' => 'Purchase List not found'], 404);
        }

        $data = $request->validated();

        $purchaseList->fill($request->only([
            'created_by',
            'emitting_department',
            'priority',
            'observations',
            'status',
        ]));
        $purchaseList->save();

        if (!empty($data['request_ids'])) {
            $purchaseList->requests()->sync($data['request_ids']);
        }

        if (!empty($data['articles'])) {
            $syncPayload = [];
            foreach ($data['articles'] as $article) {
                $syncPayload[$article['article_id']] = [
                    'quantity' => $article['quantity'],
                ];
            }

            $purchaseList->items()->sync($syncPayload);

            $purchaseList->total_quantity_requested = collect($data['articles'])->sum('quantity');
            $purchaseList->save();
        }

        return response()->json([
            'message' => 'Purchase List updated successfully.',
            'data' => $purchaseList->load(['requests', 'items', 'creator']),
        ]);
    }

     public function destroy($id): JsonResponse
    {
        try {

            $purchaseList = PurchaseList::find($id);

            if (!$purchaseList) {
                return response()->json([
                    'error' => 'Purchase List not found'
                ], 404);
            }

            $deleted =  $purchaseList->delete();
            return response()->json(['message' => 'Purchase List deleted successfully. ' . $deleted ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error deleting Purchase List: '.$e->getMessage()], 500);
        }
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->input('ids');
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'Invalid or missing IDs.'], 422);
        }

        try {
            PurchaseList::whereIn('id', $ids)->delete();
            return response()->json([
                'message' => 'Purchase Lists deleted successfully.',
                'deleted_ids' => $ids
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error deleting Purchase Lists: '.$e->getMessage()], 500);
        }
    }

    public function restore($id): JsonResponse
    {
        try {
            $purchaseList = PurchaseList::withTrashed()->find($id);

            if (!$purchaseList) {
                return response()->json([
                    'error' => 'Purchase List not found'
                ], 404);
            }

            if (is_null($purchaseList->deleted_at)) {
                return response()->json([
                    'message' => 'Purchase List is not deleted, nothing to restore.'
                ]);
            }

            $purchaseList->restore();

            return response()->json([
                'message' => 'Purchase List restored successfully.',
                'data'    => $purchaseList
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error restoring Purchase List: ' . $e->getMessage()
            ], 500);
        }
    }

    public function options(): JsonResponse
    {
        try {
            $departments = Departement::all('id','name');

            $users = User::select('id', 'name')
                ->get();

            $articles = Article::select('id', 'article_id','product_id','name')
                ->orderBy('article_id')
                ->get();

            $purchase_requests = PurchaseRequest::select('id', 'code')
                ->orderByDesc('id')
                ->get();
            \Log::info('purchase_requests', ['data' => $purchase_requests]);

            $priorities = [
                'High'   => 'Haute',
                'Medium' => 'Moyenne',
                'Low'    => 'Basse',
            ];

            $statuses = [
                'in_progress'     => 'En cours',
                'sent_for_quote'  => 'Envoyée pour devis',
                'closed'          => 'Clôturée',
            ];

            return response()->json([
                'departments'        => $departments,
                'users'              => $users,
                'articles'           => $articles,
                'purchase_requests'  => $purchase_requests,
                'priorities'         => $priorities,
                'statuses'           => $statuses,
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des options'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function validatePurchaseList(Request $request, PurchaseList $purchaseList): JsonResponse
    {
        $user = $request->user();
        $creator = $purchaseList->createdBy;

        if (!$creator) {
            abort(403, 'Le créateur de la liste n\'a pas été trouvé.');
        }

        if (
            !$creator->relationLoaded('collaborator') && method_exists($creator, 'collaborator')
                ? !$creator->collaborator
                : false
        ) {
            $creator->load('collaborator.superior');
        }

        $superiorUserId = optional(optional($creator->collaborator)->superior)->user_id;

        if ($superiorUserId !== $user->id) {
            abort(403, 'Seul le supérieur du collaborateur peut valider la liste d’achat.');
        }

        $purchaseList->status = 'sent_for_quote';
        $purchaseList->save();
         $this->generateEmail( $purchaseList->status,$purchaseList);


        return response()->json(['message' => 'Liste d’achat validée avec succès']);
    }

    public function rejectPurchaseList(Request $request, PurchaseList $purchaseList): JsonResponse
    {
        $user = $request->user();
        $creator = $purchaseList->createdBy;

        if (!$creator) {
            abort(403, 'Le créateur de la liste n\'a pas été trouvé.');
        }

        if (
            !$creator->relationLoaded('collaborator') && method_exists($creator, 'collaborator')
                ? !$creator->collaborator
                : false
        ) {
            $creator->load('collaborator.superior');
        }

        $superiorUserId = optional(optional($creator->collaborator)->superior)->user_id;

        if ($superiorUserId !== $user->id) {
            abort(403, 'Seul le supérieur du collaborateur peut rejeter la liste d’achat.');
        }

        $reason = $request->input('reason'); 
        $purchaseList->status = 'closed';
        $purchaseList->save();
         $this->generateEmail( $purchaseList->status,$purchaseList);


        return response()->json(['message' => 'Liste d’achat rejetée avec succès', 'reason' => $reason]);
    }


         public function generateEmail(string $status, PurchaseList $purchaseList): void
    {
        //log received data
        \Log::info('Generating email for purchase list', ['status' => $status]);
        try{
           $creatorEmail = $purchaseList->creator?->email;


    MailService::sendMail(
            [$creatorEmail,'i.ennajy@fondationzakoura.org'],
            "Décision sur une liste d'achats",
            'emails.purchaseLists.validationDecision',
            [
                'title' => 'Décision sur une liste d\'achats',
                'subject' => 'Décision sur une liste d\'achats',
                'status'=>$status,
               'purchaseList' => $purchaseList,
            ]
        );
        \Log::info("Purchase request decision email sent to: " . $creatorEmail);
    }
    catch(\Exception $e){
        Log::error("Error sending quote decision email: " . $e->getMessage());
}
    }
    public function exportRFQ(Request $request, PurchaseList $purchaseList)
    {
        $content = "%PDF-1.4\n% ... placeholder RFQ PDF for LA {$purchaseList->purchase_list_id} ...";
        return response($content, 200)->header('Content-Type', 'application/pdf');
    }
    public function previewPdf($id)
    {
        $pdfService = new PurchaseListPdfService();
        $path = $pdfService->generatePdf($id);
        return response()->file($path);
    }

    /**
     * Téléchargement PDF
     */
    public function downloadPdf($id)
    {
        $pdfService = new PurchaseListPdfService();
        $path = $pdfService->generatePdf($id);
        return response()->download($path);
    }

    // public function getArticlesByProduct($productId)
    // {
    //     $articles = Article::where('product_id', $productId)
    //         ->select('id', 'article_id', 'name', 'brand', 'specifications', 'reference_price', 'unit', 'product_id')
    //         ->get();
        
    //     return response()->json($articles);
    // }
}

