<?php

namespace App\Http\Controllers\Api;

use App\Enums\EvaluationTypeCriteriaOperationsEnum;
use App\Enums\GradeLevelEnum;
use App\Enums\GridStatusEnum;
use App\Enums\NiveauAppreciationEnum;
use Illuminate\Http\JsonResponse;

class EvaluationOperationsMetaController
{
    public function enums(): JsonResponse
    {
        return response()->json([
            'grid_status' => GridStatusEnum::options(),
            'appreciation_level' => NiveauAppreciationEnum::options(),
        ]);
    }
    public function enumsEvaluationCriteria(): JsonResponse
    {
        return response()->json([
            'grade_levels' => GradeLevelEnum::options(),
            'evaluation_types' => EvaluationTypeCriteriaOperationsEnum::options(),
        ]);
    }
}
