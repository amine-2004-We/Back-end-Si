<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'title'=>['required','string','max:255'],
            'department_id'=>['required','exists:departements,id'],
            'description'=>['nullable','string','max:255'],
            'main_mission'=>['nullable','string','max:255'],
            'key_activities'=>['nullable','string','max:255'],
            'required_skills'=>['nullable','string','max:255'],
            'link_with_function'=>['nullable','string','max:255'],
            'version'=>['nullable','string','max:255'],
        ];
    }
}

