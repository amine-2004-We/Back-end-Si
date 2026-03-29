<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherEvaluation extends Model
{
    //
    use SoftDeletes;
    protected $table = 'teacher_evaluations';
    protected $fillable = [
        'teacher_id',
        'program_id',
        'program_type_id',
        'unit_id',
        'general_appearance',
        'cleanliness',
        'punctuality',
        'respect_session_schedule',
        'preparation',
        'relationship_with_beneficiaries',
        'treatment_of_objectives',
        'assessment',
        'innovation',
        'pedagogical_approach',
        'participation',
        'error_correction',
        'comprehension',
        'knowledge_ritualization',
        'total_score',
        'percentage',
        'low_score_reason',
        'observation',
        'status',
        'created_by',
    ];

    public function teacher()
    {
        return $this->belongsTo(Collaborator::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function programType()
    {
        return $this->belongsTo(ProgramType::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
