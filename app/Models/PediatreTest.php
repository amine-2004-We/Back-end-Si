<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PediatreTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiary_id',
        'weight',
        'height',
        'refer_to_center',
        'observations',
        'consultation_date',
        'created_by',
    ];

    protected $casts = [
        'refer_to_center' => 'boolean',
    ];

    // Relation avec le bénéficiaire
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
