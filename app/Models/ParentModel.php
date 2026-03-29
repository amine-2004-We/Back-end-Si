<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class ParentModel
 */
class ParentModel extends Model
{
    use SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'parents';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id',
        'last_name',
        'first_name',
        'gender',
        'legal_role',
        'primary_phone',
        'secondary_phone',
        'address',
        'cin',
        'remarks',
        'created_by',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'gender' => 'string',
        'legal_role' => 'string',
    ];

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Summary of meetings
     * @return BelongsToMany<TaskMeeting, ParentModel, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function meetings(): BelongsToMany
    {
        return $this->belongsToMany(TaskMeeting::class, 'task_meeting_parent', 'parent_id', 'task_meeting_id');
    }

    /**
     * @return BelongsToMany<Beneficiary>
     */
    public function beneficiaries()
    {
        return $this->belongsToMany(
            Beneficiary::class,
             'beneficiary_parent',
             'parent_id',
             'beneficiary_id'
             )
            ->withPivot('legal_role')
            ->withTimestamps();
    }

    /**
     * @return string
     * @property string $first_name
     * @property string $last_name
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }


    public function getLegalGuardianCin(): string
    {
        $fatherCin = $this->beneficiaries()->wherePivot('legal_role', 'father')->first()?->pivot->cin ?? '';
        if (empty($fatherCin)) {
            $motherCin = $this->beneficiaries()->wherePivot('legal_role', 'mother')->first()?->pivot->cin ?? '';
            if (empty($motherCin)) {
                return $this->beneficiaries()->wherePivot('legal_role', 'legal_guardian')->first()?->pivot->cin ?? '';
            }
            return $motherCin;
        }
        return $fatherCin;
    }
}
