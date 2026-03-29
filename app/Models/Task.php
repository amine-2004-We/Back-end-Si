<?php

namespace App\Models;

use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([TaskObserver::class])]
class Task extends Model
{
    use SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'task_id',
        'title',
        'class_id',
        'project_id',
        'program_id',
        'program_type_id',
        'phase_id',
        'previous_phases_id',
        'responsible_collaborator_id',
        'location_site_id',
        'budget_line_id',
        'created_by',
        'type',
        'level_id',
        'activities',
        'expected_start_date',
        'expected_end_date',
        'actual_start_date',
        'actual_end_date',
        'duration_minutes',
        'status',
        'isProject',
        'field_observations',
        'associated_document',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'expected_start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
    ];

    /**
     * @return HasOne
     */
    public function pedagogicalSession(): HasOne { return $this->hasOne(TaskPedagogicalSession::class); }

    /**
     * @return HasOne
     */
    public function visit(): HasOne { return $this->hasOne(TaskVisit::class); }

    /**
     * @return HasOne
     */
    public function meeting(): HasOne { return $this->hasOne(TaskMeeting::class); }

    /**
     * @return HasOne
     */
    public function atelier(): HasOne { return $this->hasOne(TaskAtelier::class); }

    /**
     * @return HasOne
     */
    public function evaluation(): HasOne { return $this->hasOne(TaskEvaluation::class); }


    public function programType(): BelongsTo
    {
        return $this->belongsTo(ProgramType::class, 'program_type_id');
    }

    /**
     * @return mixed|null
     */
    public function getDetailsAttribute()
    {
        return match ($this->type) {
            'Séance pédagogique' => $this->pedagogicalSession,
            'Visite' => $this->visit,
            'Réunion' => $this->meeting,
            'Atelier' => $this->atelier,
            'Évaluation' => $this->evaluation,
            default => null,
        };
    }

    /**
     * @return BelongsTo
     */
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }

    /**
     * @return BelongsTo
     */
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }

    /**
     * @return BelongsTo
     */
    public function phase(): BelongsTo { return $this->belongsTo(Phase::class); }

    /**
     * @return BelongsTo
     */
    public function budgetLine(): BelongsTo { return $this->belongsTo(BudgetLine::class); }

    /**
     * @return BelongsTo
     */
    public function responsibleCollaborator(): BelongsTo { return $this->belongsTo(Collaborator::class, 'responsible_collaborator_id'); }

    /**
     * @return BelongsTo
     */
    public function locationSite(): BelongsTo { return $this->belongsTo(Site::class, 'location_site_id'); }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    /**
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany { return $this->belongsToMany(Group::class, 'task_group'); }

    /**
     * @return HasMany
     */
    public function attachments(): HasMany { return $this->hasMany(TaskAttachment::class); }

    public function class(): BelongsTo
    {
        return $this->belongsTo(ProjectClass::class, 'class_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }
}
