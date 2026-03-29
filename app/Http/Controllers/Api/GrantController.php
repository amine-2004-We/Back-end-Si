<?php

namespace App\Http\Controllers\Api;

use App\Enums\CurrencyEnum;
use App\Enums\GrantStatusesEnum;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGrantRequest;
use App\Http\Requests\UpdateGrantRequest;
use App\Http\Resources\GrantResource;
use App\Models\Grant;
use App\Services\GrantService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class GrantController extends Controller
{

    private GrantService $grantService;

    /**
     * Display a listing of the resource.
     * 
     * @return \Illuminate\Http\Response
     * 
     */

    public function __construct(GrantService $grantService)
    {
        $this->grantService = $grantService;
    }

    public function index(Request $request)
    {
        try{
            $params = $request->all();
            $grants = $this->grantService->getGrants($params);

           return response()->json([
            'data'=>GrantResource::collection($grants),
            'pagination'=>[
                'total'=>$grants->total(),
                'count'=>$grants->count(),
                'per_page'=>$grants->perPage(),
                'current_page'=>$grants->currentPage(),
            ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching grants'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGrantRequest $request)
    {

        $validatedData = $request->validated();

        try {
            $grant = $this->grantService->create($validatedData);
            return response()->json(new GrantResource($grant), 201);
        } 
        catch (\Exception $e) {
            Log::error('Error creating grant: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while creating the grant'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

     /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $grant = $this->grantService->show($id);
            return response()->json(new GrantResource($grant), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching the grant'], 500);
        }
    }

     /**
     * @param UpdateGrantRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateGrantRequest $request, int $id): JsonResponse
    {
        try {
            $grant = $this->grantService->update($id, $request->validated());
            return response()->json(new GrantResource($grant), 200);
        }
        catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Grant not found'], 404);
        }
         catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the grant'], 500);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->grantService->delete($id);
            return response()->json(['message' => 'Grant deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the grant'], 500);
        }
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $grant = $this->grantService->restore($id);
            return response()->json(new GrantResource($grant), 200);
        }
        catch( ModelNotFoundException $e) {
            return response()->json(['error' => 'Grant not found'], 404);
        }
         catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while restoring the grant'], 500);
        }
    }


    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        try {
            $deletedCount = $this->grantService->bulkDelete($ids);
            return response()->json(['message' => "$deletedCount grants deleted successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while deleting the grants'], 500);
        }
    }
    //get currency enums

     /**
     * Get enums for dropdowns (currency, type, etc).
     */
    public function enums(): JsonResponse
    {
        return response()->json([
            'currency' => CurrencyEnum::options(),
            'status'=>GrantStatusesEnum::options(),
            
        ]);
    }


    







    
}
