<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class ExternalTrainer
 */
class ExternalTrainer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'trainer_id',
        'trainer_identifier',
        'full_name',
        'affiliation_type',
        'cabinet_id',
        'phone',
        'email',
        'cv_path',
        'statut_actif',
        'date_enregistrement',
        'created_by',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'statut_actif' => 'boolean',
        'date_enregistrement' => 'datetime',
    ];

    /**
     * @return BelongsTo<Cabinet, ExternalTrainer>
     */
    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }


    /**
     * @return BelongsTo<Trainer, ExternalTrainer>
     */
    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    /**
     * @return BelongsTo<User, ExternalTrainer>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
