<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrlTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiaire_id',
        'ear_pain_regularly',
        'hearing_problem',
        'refer_to_center',
        'observations',
        'consultation_date',
        'created_by',
    ];

    protected $casts = [
        'ear_pain_regularly' => 'boolean',
        'hearing_problem' => 'boolean',
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
