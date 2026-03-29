<?php

namespace App\Http\Controllers\Api;

use App\Enums\CandidateSourcesEnum;
use App\Enums\CandidateStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCandidateRequest;
use App\Http\Requests\UpdateCandidateRequest;
use App\Http\Resources\CandidateResource;
use App\Models\Candidate;
use App\Services\CandidateService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class CandidateController extends Controller
{
    //
     protected CandidateService $candidateService;

    public function __construct(CandidateService $candidateService)
    {
        $this->candidateService = $candidateService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->all();
           $candidate= $this->candidateService->getFilteredCandidates($params);

            return response()->json([
                'data' => CandidateResource::collection($candidate),
                'pagination' => [
                    'total' => $candidate->total(),
                    'count' => $candidate->count(),
                    'per_page' => $candidate->perPage(),
                    'current_page' => $candidate->currentPage(),
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

      public function store(StoreCandidateRequest $request): JsonResponse
    {
        try {
            return response()->json(
                new CandidateResource($this->candidateService->create($request)),
                Response::HTTP_CREATED
            );
        } catch (Exception $e) {
            return response()->json(
                ['error' => 'Server error: ' . $e->getMessage()

                ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function update(UpdateCandidateRequest $request, Candidate $candidate): JsonResponse
    {
        try {
            $candidate = $this->candidateService->update($candidate->id, $request);
            return response()->json(
                new CandidateResource($candidate),
                Response::HTTP_OK
            );
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'messages' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Collaborator not found'], Response::HTTP_NOT_FOUND);
        } catch (NotFoundHttpException $e) {
            return response()->json(['error' => 'Resource not found'], Response::HTTP_NOT_FOUND);
        } catch (HttpException $e) {
            return response()->json(['error' => 'HTTP error: ' . $e->getMessage()], $e->getStatusCode());
        } catch (Throwable $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function destroy(Candidate $candidate): JsonResponse
    {
        try {
            $this->candidateService->delete($candidate->id);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Candidate not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function show(Candidate $candidate): JsonResponse
    {
        try {
            return response()->json(new CandidateResource($this->candidateService->getCandidate($candidate->id)), Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Candidate not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        try {
            $this->candidateService->bulkDelete($request->ids);
            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Candidates not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function restore(Candidate $candidate): JsonResponse
    {
        try {
            $this->candidateService->restore($candidate->id);
            return response()->json(['message' => 'Candidate restored successfully'], Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Candidate not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getCandidateStatus(): JsonResponse
    {
        try {
           
            return response()->json([
                'candidate_statuses'=>CandidateStatusEnum::values(),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getCandidateSources(): JsonResponse
    {
        try {
            return response()->json([
                'candidate_sources'=>CandidateSourcesEnum::values(),
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

public function changeStatus(Request $request, Candidate $candidate): JsonResponse
{
    $request->validate([
        'status' => 'required|in:,Nouveau,En entretien,Rejeté,Sélectionné',
    ]);
    
    $candidate->status = $request->status;
    $candidate->save();
    return response()->json([
        'message' => 'Statut du candidat mis à jour avec succès',
        'candidate' => $candidate
    ]);
}
    

}
