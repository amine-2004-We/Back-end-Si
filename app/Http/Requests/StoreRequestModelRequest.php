<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequestModelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'pattern' => 'nullable|string',
            'amount' => 'nullable|numeric',
            'month' => 'nullable|numeric',
            'collaborator_id' => 'required|exists:collaborators,id',
            'request_type_id' => 'required|exists:request_type,id',
            'request_status' => 'required|string'
        ];
    }

}
