<?php
namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateTaskRequest extends StoreTaskRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        
        $taskId = $this->route('task')->id;
        $rules['title'] = ['required', 'string', 'max:255', Rule::unique('tasks')->where('project_id', $this->project_id)->ignore($taskId)];
        $rules['implementation_method'] = ['nullable', 'string','in:Résultat attendu,livrable'];
        $rules['attachments_to_delete'] = ['nullable', 'array'];

        $rules['attachments_to_delete.*'] = ['integer', 'exists:task_attachments,id'];
        
        return $rules;
    }
}