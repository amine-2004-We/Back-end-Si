<?php

namespace App\Http\Controllers\Api;

use App\Constants\Department;
use App\Constants\Role;
use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestLine;
use App\Services\Notification\MailService;
use App\Services\PurchaseRequestService;
use http\Env\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Enums\PurchaseRequestStatus;
use Illuminate\Support\Facades\Log;

class PurchaseRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // 1. Initialize the base query builder once, handling the "withTrashed" option first.
        if ($request->filled('withTrashed') && $request->input('withTrashed') === 'true') {
            Log::info('Fetching all purchase requests including trashed items.');
            $query = PurchaseRequest::onlyTrashed();
        } else {
            $query = PurchaseRequest::query();
        }

        // 2. Eager load all relationships to avoid N+1 query problems.
        $query->with([
            'products' => function ($query) {
                $query->with([
                    'product' => fn ($q) => $q->select('id', 'name'),
                    'budgetCategory' => fn ($q) => $q->select('id', 'label'),
                    'budgetLine' => fn ($q) => $q->select('id', 'code'),
                ]);
            },
            'department' => fn ($q) => $q->select('id', 'name'),
            'project' => fn ($q) => $q->select('id', 'project_name'),

            'user' => function ($q) {
                $q->select('id', 'name')
                ->with([
                    'collaborator' => function ($q) {
                        $q->select('id', 'user_id', 'hierarchical_superior')
                            ->with([
                                'superior' => function ($q) {
                                    $q->select('id', 'user_id', 'email', 'first_name', 'last_name');
                                }
                            ]);
                    }
                ]);
            },
        ]);

        // 3. Handle specific filters and search terms.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('user', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    })
                    ->orWhereHas('department', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        if ($request->filled('filter')) {
            $filters = $request->input('filter');
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $query->where($key, $value);
                }
            }
        }

        // 4. Handle sorting.
        if ($request->filled('sort_by') && $request->filled('sort_direction')) {
            $query->orderBy($request->input('sort_by'), $request->input('sort_direction'));
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // 5. Check if the 'all' parameter is present and return all results if it is.
        if ($request->boolean('all')) {
            $purchaseRequests = $query->get();

            $purchaseRequests->transform(function ($item) {
                $item->status_label = PurchaseRequestStatus::tryFrom($item->status)?->label() ?? $item->status;
                return $item;
            });

            return response()->json([
                'data' => $purchaseRequests
            ], 200);
        }

        // 6. Otherwise, paginate the results.
        $perPage = $request->input('perPage', 10);
        $paginated = $query->paginate($perPage);

        $transformedData = $paginated->getCollection()->transform(function ($item) {
            $item->status_label = PurchaseRequestStatus::tryFrom($item->status)?->label() ?? $item->status;
            return $item;
        });

        return response()->json([
            'data' => $transformedData,
            'pagination' => [
                'total' => $paginated->total(),
                'count' => $paginated->count(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'total_pages' => $paginated->lastPage(),
            ]
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'department_id' => 'required|exists:departements,id',
                'project_id' => 'nullable|exists:projects,id',
                'priority'=>'required|in:Haute,Moyenne,Basse',
                'observations' => 'nullable|string',
                'products' => ['required', 'array', 'min:1'],
                'products.*.product_id' => ['required', 'exists:products,id'],
                'products.*.quantity' => ['required', 'numeric'],
                'products.*.budget_category_id' => 'nullable|exists:budget_categories,id',
                'products.*.budget_line_id' => 'nullable|exists:budget_lines,id',
                'products.*.technical_justification' => ['nullable', 'string'],
            ]);

            $error = $this->validateBudgetConsistency($validated['products']);
            if ($error) {
                return response()->json(['error' => $error], 422);
            }

            $purchaseRequest = null;

            DB::transaction(function () use ($validated, &$purchaseRequest) {
                $user = auth()->user();

                $purchaseRequest = PurchaseRequest::create([
                    'department_id' => $validated['department_id'],
                    'project_id' => $validated['project_id'],
                    'user_id' => $user?->id ?? $validated['requester_id'],
                    'priority' => $validated['priority'],
                    'observations' => $validated['observations'] ?? null,
                ]);

                foreach ($validated['products'] as $prod) {
                    $purchaseRequest->products()->create([
                        'product_id' => $prod['product_id'],
                        'quantity' => $prod['quantity'],
                        'budget_category_id' => $prod['budget_category_id'] ?? null,
                        'budget_line_id' => $prod['budget_line_id'] ?? null,
                        'technical_justification' => $prod['technical_justification'] ?? null,

                    ]);
                }
            });

            $purchaseRequest->load('products');

            return response()->json([
                'message' => 'Demande créée avec succès',
                'demande_achat' => $purchaseRequest,
            ], 200);

        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        try {
            $validated = $request->validate([
                'department_id' => 'required|exists:departements,id',
                'project_id' => 'nullable|exists:projects,id',
                'status' => 'nullable|string',
                'observations' => 'nullable|string',
                'products' => ['required', 'array', 'min:1'],
                'products.*.id' => ['sometimes', 'exists:purchase_request_lines,id'],
                'products.*.product_id' => ['required', 'exists:products,id'],
                'priority'=>'required|sometimes|in:Haute,Moyenne,Basse',
                'products.*.quantity' => ['required', 'numeric'],
                'products.*.budget_category_id' => 'nullable|exists:budget_categories,id',
                'products.*.budget_line_id' => 'nullable|exists:budget_lines,id',
                'products.*.technical_justification' => ['nullable', 'string'],
            ]);

            $error = $this->validateBudgetConsistency($validated['products']);
            if ($error) {
                return response()->json(['error' => $error], 422);
            }

            DB::transaction(function () use ($validated, $purchaseRequest) {
                $purchaseRequest->update([
                    'department_id' => $validated['department_id'],
                    'project_id' => $validated['project_id'],
                    'status' => $validated['status'] ?? $purchaseRequest->status,
                    'priority' => $validated['priority'] ?? $purchaseRequest->priority,
                    'observations' => $validated['observations'] ?? $purchaseRequest->observations,
                ]);

                $productLineIds = collect($validated['products'])
                    ->pluck('id')
                    ->filter()
                    ->all();

                $purchaseRequest->products()
                    ->whereNotIn('id', $productLineIds)
                    ->delete();

                foreach ($validated['products'] as $prod) {
                    if (isset($prod['id'])) {
                        $line = $purchaseRequest->products()->find($prod['id']);
                        if ($line) {
                            $line->update([
                                'product_id' => $prod['product_id'],
                                'quantity' => $prod['quantity'],
                                'budget_category_id' => $prod['budget_category_id'] ?? null,
                                'budget_line_id' => $prod['budget_line_id'] ?? null,
                                'technical_justification' => $prod['technical_justification'] ?? null,
                            ]);
                        }
                    } else {
                        $purchaseRequest->products()->create([
                            'product_id' => $prod['product_id'],
                            'quantity' => $prod['quantity'],
                            'budget_category_id' => $prod['budget_category_id'] ?? null,
                            'budget_line_id' => $prod['budget_line_id'] ?? null,
                            'technical_justification' => $prod['technical_justification'] ?? null,
                        ]);
                    }
                }
            });

            $purchaseRequest->load('products');

            return response()->json([
                'message' => 'Demande mise à jour avec succès',
                'demande_achat' => $purchaseRequest,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $purchaseRequest = PurchaseRequest::withTrashed()->with([
                'products' => function ($query) {
                    $query->with([
                        'product' => fn ($q) => $q->select('id', 'name'),
                        'budgetCategory' => fn ($q) => $q->select('id', 'label'),
                        'budgetLine' => fn ($q) => $q->select('id', 'code'),
                    ]);
                },
                'department' => fn ($q) => $q->select('id', 'name'),
                'project' => fn ($q) => $q->select('id', 'project_name'),
                'user' => fn ($q) => $q->select('id', 'name'),
            ])->findOrFail($id);

            $purchaseRequest->status_label = PurchaseRequestStatus::tryFrom($purchaseRequest->status)?->label() ?? $purchaseRequest->status;

            return response()->json([
                'demande_achat' => $purchaseRequest,
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Demande d\'achat non trouvée'], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $purchaseRequest = PurchaseRequest::findOrFail($id);
            $purchaseRequest->delete();

            return response()->json(['message' => 'Demande d\'achat supprimée avec succès'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Demande d\'achat non trouvée'], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['error' => 'Aucun ID fourni ou format incorrect'], 422);
        }

        try {
            DB::transaction(function () use ($ids) {
                // PurchaseRequestLine::whereIn('purchase_request_id', $ids)->delete();
                PurchaseRequest::whereIn('id', $ids)->delete();
            });

            return response()->json(['message' => 'Demandes d\'achat supprimées avec succès'], 200);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function restore($id)
    {
        try {
            $purchaseRequest = PurchaseRequest::onlyTrashed()->where('id', $id)->first();

            if (!$purchaseRequest->trashed()) {
                return response()->json(['message' => 'Demande d\'achat déjà active'], 400);
            }

            $purchaseRequest->restore();

            return response()->json(['message' => 'Demande d\'achat restaurée avec succès'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Demande d\'achat non trouvée'], 404);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function validateBudgetConsistency(array $products): ?string
    {
        foreach ($products as $prod) {
            if (!empty($prod['budget_line_id']) && !empty($prod['budget_category_id'])) {
                $belongs = DB::table('budget_lines')
                    ->where('id', $prod['budget_line_id'])
                    ->where('budget_category_id', $prod['budget_category_id'])
                    ->exists();
                if (!$belongs) {
                    return "La ligne budgétaire {$prod['budget_line_id']} n’appartient pas à la rubrique {$prod['budget_category_id']}.";
                }
            }
        }
        return null;
    }

    /**
     * @param Request $request
     * @param PurchaseRequest $purchaseRequest
     * @return JsonResponse
     */
    public function validatePurchaseRequest(Request $request, PurchaseRequest $purchaseRequest)
{
    $user = $request->user();

    $validated = $request->validate([
        'action' => ['required', 'in:validate,reject'],
    ]);

    if ($purchaseRequest->status !== PurchaseRequestStatus::Pending->value) {
        return response()->json([
            'error' => 'Cette demande n’est plus en attente.'
        ], 409);
    }

    $collaborator = $purchaseRequest->user?->collaborator;

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
    $purchaseRequest->status =
        $validated['action'] === 'reject'
            ? PurchaseRequestStatus::Rejected->value
            : PurchaseRequestStatus::Approved->value;

    $purchaseRequest->save();
 
   \Log::info('Purchase request data', ['purchase_request' => $purchaseRequest->status]);

    $this->generateEmail( $purchaseRequest->status,$purchaseRequest);

    return response()->json([
        'message' => 'Décision enregistrée avec succès.',
        'purchase_request' => $purchaseRequest,
    ]);
}

    public function generateEmail(string $status,PurchaseRequest $purchaseRequest)
    {
        //log received data
        \Log::info('Generating email for purchase request', ['status' => $status]);
        try{
           $creatorEmail = $purchaseRequest->user?->email;


    MailService::sendMail(
            [$creatorEmail],
            "Décision sur une demande d'achat",
            'emails.purchaseRequestStatus',
            [
                'title' => 'Décision sur la demande d’achat',
                'subject' => 'Décision sur la demande d’achat',
                'status'=>$status,
               'purchaseRequest' => $purchaseRequest,
            ]
        );
        \Log::info("Purchase request decision email sent to: " . $creatorEmail);
    }
    catch(\Exception $e){
        Log::error("Error sending purchase request decision email: " . $e->getMessage());
}

    }
    // Génération PDF Demande d'achat
    public function pdfPreview($id, PurchaseRequestService $service)
    {
        $path = $service->generatePdf($id);
        return response()->file($path);
    }
    public function pdfDownload($id, PurchaseRequestService $service)
    {
        $path = $service->generatePdf($id);
        return response()->download($path, "purchase-request-{$id}.pdf");
    }
}


