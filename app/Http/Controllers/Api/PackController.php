<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PackResource;
use App\Models\Pack;
use App\Services\PackService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Http\Requests\StorePackRequest;


class PackController extends Controller
{
    public function __construct(protected PackService $service)
    {
    }

    /**
     * Display a paginated listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Pass all request parameters to the service's filtered method
            if ($request->boolean('all')) {
                $packs = Pack::all();

                return response()->json([
                    'data' => PackResource::collection($packs)
                ], Response::HTTP_OK);
            }
            $packs = $this->service->getFilteredPacks($request->all());

            return response()->json([
                'data' => PackResource::collection($packs),
                'pagination' => [
                    'total' => $packs->total(),
                    'count' => $packs->count(),
                    'per_page' => $packs->perPage(),
                    'current_page' => $packs->currentPage(),
                    'total_pages' => $packs->lastPage(),
                ]
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $pack = $this->service->getById($id);
            if (!$pack) {
                return response()->json(['message' => 'Pack non trouvé'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(new PackResource($pack));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(StorePackRequest $request): JsonResponse
{
    try {
        $validatedData = $request->validated();

        $pack = $this->service->create($validatedData);

        return response()->json(new PackResource($pack), Response::HTTP_CREATED);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

 /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {

        $data = $request->validate([
            'name' => ['required', Rule::unique('packs', 'name')->ignore($id, 'id')],
            'description' => 'nullable|string',
            'products' => 'sometimes|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $pack = $this->service->update($id, $data);
            return response()->json(new PackResource($pack));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pack non trouvé'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    /**
     * Restore a soft-deleted resource.
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $pack = $this->service->restore($id);
            return response()->json([
                'message' => 'Pack restauré avec succès.',
                'data' => new PackResource($pack)
            ], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pack non trouvé ou déjà actif.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);
            return response()->json(['message' => 'Pack supprimé avec succès.'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pack non trouvé.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur serveur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove multiple resources from storage.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        try {
            $deletedCount = $this->service->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount pack(s) supprimé(s) avec succès."]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la suppression des packs: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @param Pack $pack
     * @return JsonResponse
     */
    public function getProducts(Pack $pack)
    {
        $products = $pack->products()->get()->map(function ($product) {
            return [
                'product_id' => $product->product_id,
                'name' => $product->name,
                'quantity' => $product->pivot->quantity,
                'id' => $product->id
            ];
        });

        return response()->json([
            'products' => $products,
        ]);
    }
}
