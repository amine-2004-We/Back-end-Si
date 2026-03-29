<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveTypeStore extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules(){
        return [
            'name' => 'required|unique:leave_types,name',

        ];
    }
}
