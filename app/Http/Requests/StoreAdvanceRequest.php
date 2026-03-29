<?php

namespace App\Http\Requests;

use App\Enums\AdvanceTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreAdvanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'collaborator_id' => ['required','exists:collaborators,id'],
            'project_id' => ['required','exists:projects,id'],
            'advance_type' => ['required',new Enum(AdvanceTypeEnum::class)],
            'advance_amount'=>['required','numeric'],
            'proof_expected' => ['required','boolean'],
            'proof_status' => 'required',
        ];
    }

}
