<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'type' => 'required|string|max:255',
            'task_id' => 'required|exists:tasks,id',
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'author_id' => 'required|exists:collaborators,id',
            'summary' => 'required|string',
            'positive_points' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'attachment_path.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048', // Adjust file types and size as needed
            'status' => 'required|string|in:Brouillon,Validé,Archivé',
            'remove_existing_file' => 'sometimes|string|in:true,false',

        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type est requis.',
            'task_id.required' => 'L\'ID de la tâche est requis.',
            'title.required' => 'Le titre du rapport est requis.',
            'event_date.required' => 'La date de l\'événement est requise.',
            'author_id.required' => 'L\'ID de l\'auteur est requis.',
            'summary.required' => 'Le résumé est requis.',
            'status.required' => 'Le statut du rapport est requis.', 
            'attachment_path.*.file' => 'Chaque pièce jointe doit être un fichier valide.',
            'attachment_path.*.mimes' => 'Chaque pièce jointe doit être un fichier de type : jpg, jpeg, png, pdf, doc, docx.',
            'attachment_path.*.max' => 'Chaque pièce jointe ne doit pas dépasser 2 Mo.',
        ];
    }
}
