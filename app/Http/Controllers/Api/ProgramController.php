<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProgramRequest;
use App\Http\Requests\UpdateProgramRequest;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\ProgramTypeResource;
use App\Models\Program;
use App\Services\ProgramService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * class ProgramController
 */
class ProgramController extends Controller
{
    /**
     * The service layer for handling program business logic.
     *
     * @var ProgramService
     */
    protected ProgramService $programService;

    /**
     * ProgramController constructor.
     *
     * @param ProgramService $programService The program service instance.
     */
    public function __construct(ProgramService $programService)
    {
        $this->programService = $programService;
        //$this->authorizeResource(Program::class, 'program');
    }

    /**
     *
     *
     * @param Request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $programs = $this->programService->getPaginatedPrograms(
                $request->all(),
                $request->input('per_page', 15)
            );
            return ProgramResource::collection($programs)->response();
        } catch (Exception $e) {
            Log::error('Error fetching programs: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des programmes.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProgramsByInterventionAxis(int $interventionAxisId): JsonResponse
    {
        //$this->authorize('viewAny', Program::class);

        try {
            $programs = $this->programService->getProgramsByInterventionAxis($interventionAxisId);
            return ProgramResource::collection($programs)->response();
        } catch (Exception $e) {
            Log::error('Error fetching programs by intervention axis: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des programmes pour cet axe.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retrieve the options required for the program creation form.
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        try {

            return response()->json($this->programService->getFormOptions());
        } catch (Exception $e) {
            Log::error('Error getting program form options: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des options du formulaire.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created program in storage.
     *
     * @param StoreProgramRequest
     * @return JsonResponse
     */
    public function store(StoreProgramRequest $request): JsonResponse
    {
        try {

            $programTypes = $request->input('program_types', []);

            $program = $this->programService->createProgram($request->validated(), $programTypes);
              if (!$program) {
            \Log::error('ProgramController@store: program creation returned null', [
                'payload' => $request->validated()
            ]);
            return response()->json([
                'message' => 'Erreur lors de la création du programme.',
                'error' => 'Création échouée (vérifier les logs pour plus de détails).'
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
            $program->load($this->programService->programRepository->defaultWith);
            return response()->json([
                'message' => 'Programme créé avec succès.',
                'program' => ProgramResource::make($program)
            ], HttpResponse::HTTP_CREATED);
        } catch (Exception $e) {

            Log::error('Error creating program: ' . $e->getMessage());

            return response()->json(['message' => 'Erreur lors de la création du programme.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified program.
     *
     * @param Program
     * @return JsonResponse A JSON response containing the specified program.
     */
    public function show(Program $program): JsonResponse
    {
        $program->load($this->programService->programRepository->defaultWith);
        return response()->json(ProgramResource::make($program));
    }

    /**
     *
     * @param UpdateProgramRequest $request The request object containing validated data for the update.
     * @param Program $program The program model instance to update.
     * @return JsonResponse A JSON response containing the updated program.
     */
    public function update(UpdateProgramRequest $request, Program $program): JsonResponse
    {
        try {
            $programTypes = $request->input('program_types', []);
            $updatedProgram = $this->programService->updateProgram($program, $request->validated(), $programTypes);
            $updatedProgram->load($this->programService->programRepository->defaultWith);

            return response()->json([
                'message' => 'Programme mis à jour avec succès.',
                'program' => ProgramResource::make($updatedProgram)
            ]);
        } catch (Exception $e) {
            Log::error("Error updating program {$program->id}: " . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du programme.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     *
     * @param Request $request The request object containing an array of program IDs.
     * @return JsonResponse A JSON response with the results of the toggle operation.
     */
    public function toggleActivation(Request $request): JsonResponse
    {
        //$this->authorize('massUpdate', Program::class);

        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:programs,id']);
        try {
            $results = $this->programService->toggleProgramActivation($request->input('ids'));
            return response()->json($results);
        } catch (Exception $e) {
            Log::error('Error toggling program activation: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du changement de statut des programmes.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getProgramTypes(int $programId): JsonResponse
    {
        try {
            $program = $this->programService->findProgram($programId);
            //$this->authorize('view', $program);

            $programTypes = $this->programService->getProgramTypesForProgram($programId);
            return ProgramTypeResource::collection($programTypes)->response();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Programme introuvable.'], HttpResponse::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            Log::error('Error fetching program types by program: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des types de programme.'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
