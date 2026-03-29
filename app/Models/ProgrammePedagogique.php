<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgrammePedagogique extends Model
{
    use SoftDeletes;

    protected $table = 'programme_pedagogique';

    protected $fillable = [
        'class_id',
        'user_id',
        'project_id',
        'title',
        'start_date',
        'end_date',
        'date_prevu',
        'date_realisation',
        'subjects',
        'start_time',
        'end_time',
        'observation',
    ];

    protected $casts = [
        'subjects' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'date_prevu' => 'date',
        'date_realisation' => 'date',
    ];

    public const SUBJECTS = [
        'Arabe',
        'Français',
        'Anglais',
        'Éducation islamique',
        'Mathématiques',
        'Physique et Chimie',
        'Sciences de la vie et de la Terre (SVT)',
        'Histoire et Géographie',
        'Activités parascolaires',
    ];

    public static function subjectsList(): array
    {
        return self::SUBJECTS;
    }

    public function class()
    {
        return $this->belongsTo(ProjectClass::class, 'class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
