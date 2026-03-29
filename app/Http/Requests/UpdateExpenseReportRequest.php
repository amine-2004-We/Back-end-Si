<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseReportRequest extends FormRequest
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
            'project_id' => 'sometimes|exists:projects,id',
            'mission_order_id' => 'nullable|exists:mission_orders,id',
            'budget_line_id' => 'sometimes|exists:budget_lines,id',
            'status' => 'sometimes|in:created,submitted,validated_manager,validated_treasury,validated_accounting,rejected,paid',
            'total_amount' => 'sometimes|numeric|min:0',
        ];
    }
}
