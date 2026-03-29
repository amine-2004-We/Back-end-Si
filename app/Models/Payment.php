<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    const TYPE_INVOICE = 'invoice';
    const TYPE_EXPENSE_REPORT = 'expense_report';

    protected $fillable = [
        'payment_number',
        'transaction_date',
        'due_date',
        'amount',
        'payment_method',
        'payment_status',
        'note',
        'created_by',
        'project_id',
        'payment_type',
    ];
    protected $casts = [
        'payment_method' => PaymentMethodEnum::class,
        'payment_status' => PaymentStatusEnum::class,
    ];

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_payment')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function expenseReports(): BelongsToMany
    {
        return $this->belongsToMany(ExpenseReport::class, 'expense_report_payment')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this payment is for invoices
     */
    public function isInvoicePayment(): bool
    {
        return $this->payment_type === self::TYPE_INVOICE;
    }

    /**
     * Check if this payment is for expense reports
     */
    public function isExpenseReportPayment(): bool
    {
        return $this->payment_type === self::TYPE_EXPENSE_REPORT;
    }
}
