<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grant extends Model
{
    //
    protected $table = 'grants';
    use SoftDeletes;

    protected $fillable = [
        'grant_id',
        'partner_id',
        'convention_id',
        'project_id',
        'bank_account_id',
        'committed_amount',
        'received_amount',
        'currency',
        'agreement_date',
        'received_dates',
        'reception_method',
        'intended_use',
        'status',
        'comments',
        'proof_document_attachment_path',
        'payment_schedule_attachment_path',
        'remove_payment_schedule',
        'remove_proof_document',
        'created_by',
    ];
    protected $casts = [
    'received_dates' => 'array',
];


    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(ProjectBankAccount::class);
    }

    public function convention()
    {
        return $this->belongsTo(Convention::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
