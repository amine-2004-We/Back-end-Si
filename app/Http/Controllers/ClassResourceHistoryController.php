<?php

namespace App\Http\Controllers;

use App\Models\ProjectClass;
use App\Models\ClassResourceHistory;
use Illuminate\Http\Request;

class ClassResourceHistoryController extends Controller
{
    /**
     * Get the history of resource changes for a class.
     *
     * @param ProjectClass $class
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClassHistory(ProjectClass $class, Request $request)
    {
        $perPage = $request->query('per_page', 15);
        
        $history = ClassResourceHistory::where('class_id', $class->id)
            ->with(['user', 'classResource.collaborator', 'classResource.project' , 'class'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $history->items(),
            'pagination' => [
                'total' => $history->total(),
                'per_page' => $history->perPage(),
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
            ]
        ]);
    }

    /**
     * Get the history of changes for a specific class resource.
     *
     * @param int $classId
     * @param int $classResourceId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getResourceHistory($classId, $classResourceId, Request $request)
    {
        $class = ProjectClass::findOrFail($classId);
        $perPage = $request->query('per_page', 15);

        $history = ClassResourceHistory::where('class_id', $classId)
            ->where('class_resource_id', $classResourceId)
            ->with(['user'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $history->items(),
            'pagination' => [
                'total' => $history->total(),
                'per_page' => $history->perPage(),
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
            ]
        ]);
    }

    /**
     * Get a detailed view of a single history record.
     *
     * @param ClassResourceHistory $history
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(ClassResourceHistory $history)
    {
        $history->load(['user', 'classResource.collaborator', 'classResource.project' , 'class']);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Get statistics about resource changes for a class.
     *
     * @param ProjectClass $class
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(ProjectClass $class)
    {
        $statistics = [
            'total_changes' => ClassResourceHistory::where('class_id', $class->id)->count(),
            'created_count' => ClassResourceHistory::where('class_id', $class->id)->where('action', 'created')->count(),
            'updated_count' => ClassResourceHistory::where('class_id', $class->id)->where('action', 'updated')->count(),
            'deleted_count' => ClassResourceHistory::where('class_id', $class->id)->where('action', 'deleted')->count(),
            'restored_count' => ClassResourceHistory::where('class_id', $class->id)->where('action', 'restored')->count(),
            'last_change' => ClassResourceHistory::where('class_id', $class->id)->latest('created_at')->first()?->created_at,
        ];

        return response()->json([
            'success' => true,
            'data' => $statistics
        ]);
    }
}
