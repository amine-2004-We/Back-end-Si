<?php

namespace App\Models;

use App\Enums\TrainingModuleFormat;
use App\Enums\TrainingModuleStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'modules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module_id',
        'title',
        'pedagogical_objectives',
        'training_id',
        'trainer_id',
        'competency_grid_id',
        'total_duration',
        'formation_type',
        'pedagogical_supports',
        'evaluation_planned',
        'status',
        'created_by_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pedagogical_supports' => 'array',
        'evaluation_planned'   => 'boolean',
        'total_duration'       => 'decimal:2',
        'formation_type'       => TrainingModuleFormat::class, // Updated to use Enum
        'status'               => TrainingModuleStatus::class, // Updated to use Enum
    ];

    /**
     * Get the training that this module belongs to.
     */
    public function training()
    {
        return $this->belongsTo(Training::class, 'training_id');
    }

    /**
     * Get the trainer assigned to this module.
     */
    public function trainer()
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    /**
     * Get the competency grid associated with this module.
     */
    public function competencyGrid()
    {
        return $this->belongsTo(CompetencyGrid::class, 'competency_grid_id');
    }

    /**
     * Get the user who created the module.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return HasMany<TrainingSession, Module>
     */
    public function sessions():HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }
}

