<?php

namespace App\Http\Controllers\Api;

use App\Enums\EvaluationTypeEnum;
use App\Enums\EvaluationStatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class EvaluationMetaController extends Controller
{
    public function enums(): JsonResponse
    {
        return response()->json([
            'evaluation_types' => EvaluationTypeEnum::options(),
            'evaluation_statuses' => EvaluationStatusEnum::options(),
        ]);
    }
}
