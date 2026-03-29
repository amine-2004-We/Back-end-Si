<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentaireTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'beneficiaire_id',
        'has_six_year_molar',
        'six_year_molar_cariee',
        'refer_to_center',
        'observations',
        'consultation_date',
        'created_by',
    ];

    protected $casts = [
        'has_six_year_molar' => 'boolean',
        'six_year_molar_cariee' => 'boolean',
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
