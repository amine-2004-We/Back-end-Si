<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisionTest extends Model
{
    use HasFactory;

    // Par défaut, Eloquent va chercher la table "vision_tests"
    // donc pas besoin de préciser $table sauf si ton nom est différent
    // protected $table = 'vision_tests';

    protected $fillable = [
        'beneficiary_id',
        'right_eye',
        'left_eye',
        'refer_to_center',
        'observations',
        'consultation_date',
        'created_by',
    ];

    // Casts pour transformer automatiquement certains champs
    protected $casts = [
        'refer_to_center' => 'boolean',
    ];

    /**
     * Relation : un test appartient à un bénéficiaire
     */
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiaire_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
