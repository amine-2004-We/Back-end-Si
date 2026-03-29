<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class Participant
 */
class Participant extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'participants';

    /**
     * @var array
     */
    protected $fillable = [
        'attendance_recorded',
        'final_evaluation',
        'insured',
        'created_by_id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'attendance_recorded' => 'boolean',
        'insured'             => 'boolean',
    ];

    /**
     * @var array
     */
    protected $appends = ['display_label'];

    /**
     * @return BelongsTo<User, Participant>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * @return HasOne<TraineeCollaborator, Participant>
     */
    public function traineeCollaborator(): HasOne
    {
        return $this->hasOne(TraineeCollaborator::class, 'participant_id');
    }

    /**
     * @return HasOne<TraineeCollaborator, Participant>
     */
    public function internalParticipant(): HasOne
    {
        return $this->hasOne(TraineeCollaborator::class, 'participant_id');
    }

    /**
     * @return HasOne<External, Participant>
     */
    public function externalTrainee(): HasOne
    {
        return $this->hasOne(External::class, 'participant_id');
    }

    /**
     * @return HasOne<External, Participant>
     */
    public function externalParticipant(): HasOne
    {
        return $this->hasOne(External::class, 'participant_id');
    }

    /** Scopes */

    /**
     * @param mixed $query
     */
    public function scopeWithDisplay($query)
    {
        return $query->with([
            'internalParticipant.collaborator:id,first_name,last_name,collaborator_code',
            'externalParticipant.external:id,first_name,last_name,name',
        ]);
    }

    /**
     * @return string
     */
    public function getDisplayLabelAttribute(): string
    {
        $tc =  $this->traineeCollaborator;
        if ($tc && $tc->relationLoaded('collaborator') && $tc->collaborator) {
            $c = $tc->collaborator;
            $code = $c->collaborator_code ? $c->collaborator_code . ' — ' : '';
            $first = $c->first_name ?? '';
            $last  = $c->last_name ?? '';
            return trim($code . trim($first . ' ' . $last));
        }

        $ext = $this->externalTrainee;
        if ($ext && $ext->relationLoaded('external') && $ext->external) {
            $e = $ext->external;
            if (!empty($e->name)) {
                return (string) $e->name;
            }
            $first = $e->first_name ?? '';
            $last  = $e->last_name ?? '';
            $full  = trim($first . ' ' . $last);
            if ($full !== '') {
                return $full;
            }
        }

        return 'Participant #' . $this->id;
    }

    /**
     * @return bool
     */
    public function isInternal(): bool
    {
        return (bool) ($this->internalParticipant()->exists() ?: $this->traineeCollaborator()->exists());
    }

    /**
     * @return bool
     */
    public function isExternal(): bool
    {
        return (bool) ($this->externalParticipant()->exists() ?: $this->externalTrainee()->exists());
    }
}
