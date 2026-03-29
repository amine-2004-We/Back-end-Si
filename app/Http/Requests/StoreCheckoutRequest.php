<?php

namespace App\Http\Requests;

use App\Enums\OperationTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'operation_type'=>['required',new Enum(OperationTypeEnum::class)],
            'project_id'=>['required','exists:projects,id'],
            'initiale_amount'=>['required','numeric'],
            'used_amount'=>['required','numeric'],
            'collaborator_id'=>['required','exists:collaborators,id'],
        ];
    }

}
