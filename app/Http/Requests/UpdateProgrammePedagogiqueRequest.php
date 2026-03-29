<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ProgrammePedagogique;

class UpdateProgrammePedagogiqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowed = implode(',', array_map(function ($s) { return str_replace(',', '\\,', $s); }, ProgrammePedagogique::subjectsList()));

        return [
            'class_id' => ['nullable', 'exists:class,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'date_prevu' => ['nullable', 'date'],
            'date_realisation' => ['nullable', 'date'],
            'subjects' => ['sometimes', 'array', 'min:1', 'max:1'],
            'subjects.*' => ['string', 'in:' . $allowed],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'observation' => ['nullable', 'string'],
        ];
    }
}
