<?php

namespace App\Models;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseOrder;

class Calltender extends Model
{
    use SoftDeletes;
    //
    protected $fillable = [
        'calltender_id',
        'supplier',
        'subject',
        'purchase_order_refs',
        'calltender_type',
        'start_date',
        'end_date',
        'total_amount',
        'currency',
        'conditions_path',
        'responsible_id',
        'status',
        'signature_date',
        'notes'
    ];
    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }
}
