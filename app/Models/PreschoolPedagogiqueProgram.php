<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreschoolPedagogiqueProgram extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'pedagogical_project',
        'pedagogical_project_arabe',
        'subcomponent',
        'duration'
    ];
    public function phase()
    {
        return $this->belongsTo(Phase::class, 'subcomponent');
    }
}
