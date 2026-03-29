<?php

namespace App\Http\Requests;

use App\Models\ProgrammePedagogique;
use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammePedagogiqueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowed = implode(',', array_map(function ($s) {
            return str_replace(',', '\\,', $s);
        }, ProgrammePedagogique::subjectsList()));

        return [
            'class_id' => ['required', 'exists:class,id'],
            'user_id' => ['required', 'exists:users,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'date_prevu' => ['nullable', 'date'],
            'date_realisation' => ['nullable', 'date'],
            'subjects' => ['required', 'array', 'min:1', 'max:1'],
            'subjects.*' => ['string', 'in:'.$allowed],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'observation' => ['nullable', 'string'],
        ];
    }

    public function prepareForValidation(): void
    {
        if (! $this->has('project_id') || ! $this->project_id) {
            $user = auth()->user();
            if ($user && $user->collaborator) {
                $firstProject = $user->collaborator->projects()->first();
                if (! $firstProject) {
                    $firstProject = \App\Models\Project::whereNull('deleted_at')->first();
                }

                if ($firstProject) {
                    $this->merge(['project_id' => $firstProject->id]);
                }
            }
        }
    }
}
