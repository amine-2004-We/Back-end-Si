<?php

namespace App\Models;

use App\Enums\GridType;
use App\Enums\GradingScheme;
use App\Enums\LifecycleStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetencyGrid extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'competency_grids';

    protected $fillable = [
        'code',
        'title',
        'pedagogical_objective',
        'grid_type',
        'grading_scheme',
        'instructions',
        'lifecycle_status',
        'created_by_id',
    ];

    protected $casts = [
        'grid_type'        => GridType::class,
        'grading_scheme'   => GradingScheme::class,
        'lifecycle_status' => LifecycleStatus::class,
    ];

    public function modules()
    {
        return $this->hasMany(Module::class, 'competency_grid_id');
    }

    public function trainings()
    {
        return $this->hasMany(Training::class, 'competency_grid_id');
    }

    public function criteria()
    {
        return $this->belongsToMany(CompetencyCriterion::class, 'competency_grid_criteria', 'competency_grid_id', 'criterion_id')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }


    public function scopeValidated($query)
    {
        return $query->where('lifecycle_status', LifecycleStatus::Validated);
    }

    public function scopeActive($query)
    {
        return $query->where('lifecycle_status', '!=', LifecycleStatus::Archived);
    }

}
