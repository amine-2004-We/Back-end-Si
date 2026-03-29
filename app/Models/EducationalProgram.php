<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationalProgram extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'pedagogical_project',
        'pedagogical_project_arabe',
        'subcomponent',
        'level',
        'duration',
    ];
    public function phase()
    {
        return $this->belongsTo(Phase::class, 'subcomponent');
    }
}
