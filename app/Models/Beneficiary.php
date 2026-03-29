<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\BeneficiaryObserver;

/**
 *class BeneficiaryObserver
 */
#[ObservedBy([BeneficiaryObserver::class])]
class Beneficiary extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string
     */
    protected $table = 'beneficiaires';

    /**
     * @var string[]
     */
    protected $fillable = [
        'beneficiary_id',
        'last_name',
        'first_name',
        'gender',
        'date_of_birth',
        'place_of_residence',
        'massar_code',
        'nationality',
        'address',
        'current_school_level_id',
        'group_id',
        'status',
        'enrollment_date',
        'radiation_date',
        'radiation_reason',
        'observations',
        'created_by',
        'country_id',
        'fr_grade_s1',
        'fr_grade_s2_minus_1',
        'fr_grade_s2',
        'maths_grade_s2_minus_1',
        'maths_grade_s1',
        'maths_grade_s2',
        'insurance_status',
        'validation_status',
        'validated_by_1',
        'validated_at_1',
        'validated_by_2',
        'validated_at_2',
        'new_status_data',
        'insurance_start_date',
        'insurance_end_date',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'radiation_date' => 'date',
    ];

    /**
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * @return BelongsTo
     */
    public function currentSchoolLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_school_level_id');
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo
     */
    public function validatorLevel1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_1');
    }

    /**
     * @return BelongsTo
     */
    public function validatorLevel2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_2');
    }

    /**
     * @return BelongsToMany
     */
    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentModel::class, 'beneficiary_parent', 'beneficiary_id', 'parent_id')
            ->withPivot('legal_role')
            ->withTimestamps();
    }


    /**
     * @return HasMany
     */
    public function orlTests()
    {
        return $this->hasMany(\App\Models\OrlTest::class, 'beneficiaire_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function latestOrlTest()
    {
        return $this->hasOne(\App\Models\OrlTest::class, 'beneficiaire_id', 'id')
            ->latestOfMany('consultation_date');
    }

    /**
     * @return HasMany
     */
    public function pediatreTests()
    {
        return $this->hasMany(\App\Models\PediatreTest::class, 'beneficiary_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function latestPediatreTest()
    {
        return $this->hasOne(\App\Models\PediatreTest::class, 'beneficiary_id', 'id')
            ->latestOfMany('consultation_date');
    }

    /**
     * @return HasMany
     */
    public function visionTests()
    {
        return $this->hasMany(\App\Models\VisionTest::class, 'beneficiary_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function latestVisionTest()
    {
        return $this->hasOne(\App\Models\VisionTest::class, 'beneficiary_id', 'id')
            ->latestOfMany('consultation_date');
    }

    /**
     * @return HasMany
     */
    public function dentaireTests()
    {
        return $this->hasMany(\App\Models\DentaireTest::class, 'beneficiaire_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function latestDentaireTest()
    {
        return $this->hasOne(\App\Models\DentaireTest::class, 'beneficiaire_id', 'id')
            ->latestOfMany('consultation_date');
    }

    /**
     * @return HasMany
     */
    public function vaccinTests()
    {
        return $this->hasMany(\App\Models\VaccinTest::class, 'beneficiary_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function latestVaccinTest()
    {
        return $this->hasOne(\App\Models\VaccinTest::class, 'beneficiary_id', 'id')
            ->latestOfMany('consultation_date');
    }

    /**
     * @return null
     */
    public function getClassAttribute()
    {
        return $this->group?->class;
    }

    /**
     * @return null
     */
    public function getCycleAttribute()
    {
        return $this->group?->class?->level?->cycle;
    }

    /**
     * @return bool
     */
    public function getHasOrlTestAttribute()
    {
        return $this->orlTests()->exists();
    }

    /**
     * @return bool
     */
    public function getHasPediatreTestAttribute()
    {
        return $this->pediatreTests()->exists();
    }

    /**
     * @return bool
     */
    public function getHasVisionTestAttribute()
    {
        return $this->visionTests()->exists();
    }

    /**
     * @return bool
     */
    public function getHasDentaireTestAttribute()
    {
        return $this->dentaireTests()->exists();
    }

    /**
     * @return string
     */
    public function getConsultationStatusAttribute()
    {
        $hasOrl = $this->has_orl_test;
        $hasPediatre = $this->has_pediatre_test;
        $hasVision = $this->has_vision_test;
        $hasDentaire = $this->has_dentaire_test;

        if ($hasOrl && $hasPediatre && $hasVision && $hasDentaire) {
            return 'completed';
        } elseif ($hasOrl || $hasPediatre || $hasVision || $hasDentaire) {
            return 'partial';
        } else {
            return 'none';
        }
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(BeneficiaryStatusHistory::class, 'beneficiary_id');
    }

    /**
     * @return HasMany
     */
    public function evaluationOperations(): HasMany
    {
        return $this->hasMany(EvaluationOperation::class, 'beneficiary_id');
    }

    /**
     * Relationship for parents with the 'father' legal role.
     *
     * @return BelongsToMany
     */
    public function father(): BelongsToMany
    {
        return $this->belongsToMany(ParentModel::class, 'beneficiary_parent', 'beneficiary_id', 'parent_id')
            ->wherePivot('legal_role', 'father')
            ->withPivot('legal_role')
            ->withTimestamps();
    }

    /**
     * Accessor that returns the primary parent model (father, else mother, else legal_guardian).
     *
     * @return \App\Models\ParentModel|null
     */
    public function getFatherAttribute()
    {
        $father = $this->father()->first();

        if (empty($father)) {
            $mother = $this->parents()
                ->wherePivot('legal_role', 'mother')
                ->first();

            if (empty($mother)) {
                return $this->parents()
                    ->wherePivot('legal_role', 'legal_guardian')
                    ->first();
            }

            return $mother;
        }

        return $father;
    }

    public function getLegalGuardianAttribute(): ?\App\Models\ParentModel
{
    // use the loaded collection if available
    $parents = $this->relationLoaded('parents') ? $this->parents : $this->parents()->get();

    return $parents->firstWhere('pivot.legal_role', 'father')
        ?? $parents->firstWhere('pivot.legal_role', 'mother')
        ?? $parents->firstWhere('pivot.legal_role', 'legal_guardian')
        ?? null;
}

/** convenience accessor for CIN */
public function getLegalGuardianCinAttribute(): string
{
    return $this->legal_guardian?->cin ?? '';
}
}


