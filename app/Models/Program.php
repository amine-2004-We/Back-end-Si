<?php

namespace App\Models;

use App\Observers\ProgramObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


#[ObservedBy([ProgramObserver::class])]
class Program extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'programs';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'program_id',
        'title',
        'code',
        'main_objective',
        'start_date',
        'end_date',
       
        'status',
        'operational_manager_id',
        'pedagogical_manager_id',
        'regional_manager_id',
        'supervisor_id',
        'observations',
        'created_by',
      'program_types',
        'intervention_axis_id',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * @return BelongsTo
     */
    public function operationalManager(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'operational_manager_id');
    }

    /**
     * @return BelongsTo
     */
    public function pedagogicalManager(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'pedagogical_manager_id');
    }

    /**
     * @return BelongsTo
     */
    public function regionalManager(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'regional_manager_id');
    }

    /**
     * @return BelongsTo
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'supervisor_id');
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function programType(): BelongsTo
    {
        return $this->belongsTo(ProgramType::class, 'program_type_id');
    }

    public function interventionAxis(): BelongsTo
    {
        return $this->belongsTo(InterventionAxis::class, 'intervention_axis_id');


    }
    public function programTypes(): HasMany
    {
        return $this->hasMany(ProgramType::class, 'program_id');
    }

   
}
