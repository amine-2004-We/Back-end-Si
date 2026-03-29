<?php

namespace App\Models;

use App\Enums\ModuleEvaluationStatus;
use App\Enums\ModuleEvaluationType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModuleEvaluation extends Model
{
    use SoftDeletes;

    protected $table = 'module_evaluations';

    protected $fillable = [
        'evaluation_id',
        'module_id',
        'participant_id',
        'trainer_id',
        'evaluated_at',
        'evaluation_type',
        'competency_grid_id',
        'score_value',
        'score_label',
        'trainer_comments',
        'attachments',
        'status',
        'created_by_id',
    ];

    protected $casts = [
        'evaluated_at'    => 'date',
        'evaluation_type' => ModuleEvaluationType::class,
        'status'          => ModuleEvaluationStatus::class,
        'attachments'     => 'array',
        'score_value'     => 'decimal:2',
    ];

    protected $appends = [
        'trainer_display_name',
        'participant_display_name',
    ];

    /**
     * Return a base code for an evaluation (Observer will make it unique).
     * Example base: MEV-20240903
     */
    public static function generateEvaluationIdBase(): string
    {
        return 'MEV-' . now()->format('Ymd');
    }

    /* ---------------- Relationships ---------------- */

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function competencyGrid(): BelongsTo
    {
        return $this->belongsTo(CompetencyGrid::class, 'competency_grid_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Eager-load all relations the UI typically needs.
     */
    public function scopeWithDisplayRelations($query)
    {
        return $query->with([
            'module:id,title,module_id,total_duration,formation_type,status,competency_grid_id,created_by_id',
            'trainer.internalTrainer.collaborator:id,first_name,last_name,collaborator_code',
            'trainer.externalTrainer',
            'participant.traineeCollaborator.collaborator:id,first_name,last_name,collaborator_code',
            'participant.externalTrainee:id,full_name',
            // 'participant.candidate:id,first_name,last_name,code',
            'competencyGrid:id,title,code',
            'createdBy:id,name',
        ]);
    }

    /* ---------------- UI accessors ---------------- */

    public function trainerDisplayName(): Attribute
    {
        return Attribute::get(function () {
            $t = $this->getRelationValue('trainer');
            if (!$t) return null;

            $collab = optional(optional($t->internalTrainer)->collaborator);
            if ($collab) {
                $code = $collab->collaborator_code ?? null;
                $full = trim(($collab->first_name ?? '') . ' ' . ($collab->last_name ?? ''));
                if ($code && $full !== '') return $code . ' — ' . $full;
                if ($full !== '') return $full;
            }

            $ext = optional($t->externalTrainer);
            if ($ext) {
                $code = $ext->external_code ?? $ext->trainer_code ?? null;
                $base = $ext->full_name ?? $ext->name ?? null;
                if ($code && $base) return $code . ' — ' . $base;
                if ($base) return $base;
            }

            return "Formateur #{$t->id}";
        });
    }

    public function participantDisplayName(): Attribute
    {
        return Attribute::get(function () {
            $p = $this->getRelationValue('participant');
            if (!$p) return null;

            $tc = optional($p->traineeCollaborator);
            $collab = optional($tc->collaborator);
            if ($collab) {
                $code = $collab->collaborator_code ?? null;
                $full = trim(($collab->first_name ?? '') . ' ' . ($collab->last_name ?? ''));
                if ($code && $full !== '') return $code . ' — ' . $full;
                if ($full !== '') return $full;
            }

            $ext = optional($p->externalTrainee);
            if ($ext) {
                $code = $ext->external_code ?? null;
                $base = $ext->full_name ?? $ext->name ?? null;
                if ($code && $base) return $code . ' — ' . $base;
                if ($base) return $base;
            }

            $cand = optional($p->candidate);
            if ($cand) {
                $code = $cand->code ?? null;
                $full = trim(($cand->first_name ?? '') . ' ' . ($cand->last_name ?? ''));
                if ($code && $full !== '') return $code . ' — ' . $full;
                if ($full !== '') return $full;
            }

            return "Participant #{$p->id}";
        });
    }
}
