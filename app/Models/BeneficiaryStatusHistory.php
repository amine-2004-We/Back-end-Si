<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiaryStatusHistory extends Model
{
    //
    protected $table = 'beneficiary_status_history';
    protected $fillable = [
        'beneficiary_id',
        'status',
        'change_date',
        'reason',
        'created_by',
        'previous_group_id',
        'destination_group_id',
        'transfer_commune_id',
        'transfer_class_id',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function PreviousGroup()
    {
        return $this->belongsTo(Group::class, 'previous_group_id');
    }

    public function TransferCommune()
    {
        return $this->belongsTo(Commune::class, 'transfer_commune_id');
    }

    public function TransferClass()
    {
        return $this->belongsTo(ProjectClass::class, 'transfer_class_id');
    }

    public function DestinationGroup()
    {
        return $this->belongsTo(Group::class, 'destination_group_id');
    }
}
