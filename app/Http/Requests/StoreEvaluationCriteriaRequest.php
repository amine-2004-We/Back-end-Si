<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationCriteriaRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            "name"=>['required','string'],
            "evaluation_grid_id"=>['required','exists:evaluation_grid,id'],
            "object_project"=>['nullable','exists:projects,id'],
            "object_partner"=>['nullable','exists:partners,id'],
            "description"=>['required','string'],
            "grading_scale"=>['required','numeric'],
            "weighting_criterion"=>['nullable','numeric'],
            "order"=>['required','numeric'],
            "comments"=>['nullable','string']
        ];
    }
}

