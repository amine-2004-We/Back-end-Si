<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VaccinTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiaire_id',
        'is_up_to_date',
        'missed_vaccine',
        'refer_to_center',
        'observations',
        'consultation_date',
        'created_by',
    ];

    protected $casts = [
        'is_up_to_date' => 'boolean',
        'missed_vaccine' => 'boolean',
        'refer_to_center' => 'boolean',
    ];

    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiaire_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
