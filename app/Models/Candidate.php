<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    //
    protected $table = 'candidates';
    use SoftDeletes;
    protected $fillable = [
        'job_posting_id',
        'number_of_children',
        'title', // civilite
        'last_name', // nom
        'first_name', // prenom
        'last_name_ar', // nomArabe
        'first_name_ar', // prenomArabe
        'phone', // telephone
        'email',
        'cin',
        'cnss',
        'rib',
        'birth_date', // date_naissance
        'birth_region_id',
        'birth_province_id', // province_naissance
        'residence_address', // adresse_residence
        'residence_region_id',
        'residence_province_id', // province_residence
        'source',
        'status',
        'total_experience', // experience_totale
        'educational_experience', // experience_education
        'marital_status', // situation_familiale
        'education_level', // formation
        'discipline',
        'institution', // etablissement
        'graduation_date', // date_obtention
        'family_member', // membre_famille
        'photo',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function assurances(): MorphMany
    {
        return $this->morphMany(Assurance::class, 'personne_assuree');
    }
}
