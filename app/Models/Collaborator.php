<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collaborator extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'collaborator_code',
        'last_name',
        'first_name',
        'last_name_ar',
        'first_name_ar',
        'email',
        'cin',
        'cnss',
        'cimr',
        'insurance_membership_number',
        'title',
        'phone',
        'rib',
        'birth_date',
        'birth_region_id',
        'birth_province_id',
        'residence_address',
        'residence_region_id',
        'residence_province_id',
        'source',
        'department_id',
        'position_id',
        'entry_date',
        'exit_date',
        'gross_salary',
        'net_salary',
        'trial_period',
        'notice_period',
        'total_experience',
        'educational_experience',
        'cart',
        'transportation',
        'presentation',
        'movement',
        'annual_leave_days',
        'assigned_region_id',
        'assigned_province_id',
        'education_level',
        'discipline',
        'institution',
        'graduation_date',
        'family_member',
        'family_relationship',
        'hierarchical_superior',
        'marital_status',
        'collaborator_status_id',
        'contract_type_id',
        'contract_status_id',
        'photo',
        'user_id',
    ];

    public function collaboratorStatus()
    {
        return $this->belongsTo(CollaboratorStatus::class, 'collaborator_status_id');
    }

    public function contractStatus()
    {
        return $this->belongsTo(ContractStatus::class, 'contract_status_id');
    }

    public function contractType()
    {
        return $this->belongsTo(ContractTypes::class, 'contract_type_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_collaborator');
    }
    public function department()
    {
        return $this->belongsTo(Departement::class, 'department_id');
    }
    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function superior(): BelongsTo
    {
        return $this->belongsTo(Collaborator::class, 'hierarchical_superior');
    }

    public function subordinates()
    {
        return $this->hasMany(Collaborator::class, 'hierarchical_superior');
    }


    public function assignedRegion()
    {
        return $this->belongsTo(Region::class, 'assigned_region_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class,'residence_region_id');
    }


}
