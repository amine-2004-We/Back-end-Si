<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes;

    protected $table = 'position';
    protected $fillable=
        [
            'position_code',
            'title',
            'department_id',
            'description',
            'main_mission',
            'key_activities',
            'required_skills',
            'link_with_function',
            'version'
        ];
    public function collaborators()
    {
        return $this->hasMany(Collaborator::class, 'position_id');
    }

}

