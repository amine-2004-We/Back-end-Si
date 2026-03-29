<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationGridRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'title'=>['required','string','max:255'],
            'object_project'=>['nullable','exists:projects,id'],
            'object_partner'=>['nullable','exists:partners,id'],
            'evaluation_frequency'=>['nullable','string'],
            'scoring_method'=>['required','string'],
            'rating_scale'=>['required','string'],
            'weighting_criterion'=>['nullable','boolean','default'=>false],
            'grid_status'=>['required','string'],
            'comment'=>['nullable','string'],
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ];
    }
}

