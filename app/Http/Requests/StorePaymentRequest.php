<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StorePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'transaction_date' => 'required|date',
            'due_date' => 'nullable|date',
            'project_id' => 'required|exists:projects,id',
            'payment_type' => 'required|in:invoice,expense_report',
            'invoice_ids' => 'required_if:payment_type,invoice|array|min:1',
            'invoice_ids.*' => 'required_if:payment_type,invoice|integer|exists:invoices,id',
            'expense_report_ids' => 'required_if:payment_type,expense_report|array|min:1',
            'expense_report_ids.*' => 'required_if:payment_type,expense_report|integer|exists:expense_reports,id',
            'payment_method' => ['required', new Enum(PaymentMethodEnum::class)],
            'payment_status' => ['required', new Enum(PaymentStatusEnum::class)],
            'note' => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'invoice_ids.required_if' => 'Vous devez sélectionner au moins une facture.',
            'expense_report_ids.required_if' => 'Vous devez sélectionner au moins une note de frais.',
        ];
    }
}
