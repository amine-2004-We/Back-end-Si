<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'collaborator_id' => 'nullable|exists:collaborators,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'reason'=>'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'start_time' => 'nullable|string',
            'end_time' => 'nullable|string',
            'nbr_days' => 'required|numeric',
            'is_full_day' => 'nullable|boolean',
            'status' => 'required|string',
        ];
    }


}
