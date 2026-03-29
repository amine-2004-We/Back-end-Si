<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentRequest extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'transaction_date' => $this->transaction_date,
            'due_date' => $this->due_date,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type,
            'note' => $this->note,
            'project_id' => $this->project_id,
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'project_name' => $this->project->project_name,
                    'project_code' => $this->project->project_code,
                    'bank_account' => $this->project->projectBankAccount ? [
                        'id' => $this->project->projectBankAccount->id,
                        'account_number' => $this->project->projectBankAccount->account_number ?? null,
                        'iban' => $this->project->projectBankAccount->iban ?? null,
                        'bic' => $this->project->projectBankAccount->bic ?? null,
                        'bank_name' => $this->project->projectBankAccount->bank_name ?? null,
                    ] : null,
                ];
            }),
            'invoices' => $this->whenLoaded('invoices', function () {
                return $this->invoices->map(function ($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date,
                        'due_date' => $invoice->due_date,
                        'total' => $invoice->total,
                        'status' => $invoice->status,
                    ];
                });
            }),
            'expense_reports' => $this->whenLoaded('expenseReports', function () {
                return $this->expenseReports->map(function ($expenseReport) {
                    return [
                        'id' => $expenseReport->id,
                        'total_amount' => $expenseReport->total_amount,
                        'status' => $expenseReport->status,
                        'created_at' => $expenseReport->created_at,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
