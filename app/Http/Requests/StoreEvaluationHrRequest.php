<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationHrRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            "name" => "required|string|max:255",
            "collaborator_id" => "required|exists:collaborators,id",
            "description" => "required|string|max:255",
            "objectif_nature" => "required|string|max:255",
            "measurement_indicators" => "required|string|max:255",
            "weight" => "required|numeric",
            "skill" => "required|string|max:255",
            "indicators" => "required|string|max:255",
            "value" => "required|string|max:255",
        ];
    }
}
