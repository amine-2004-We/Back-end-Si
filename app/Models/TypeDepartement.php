<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeDepartement extends Model
{
    //
    use SoftDeletes;
    protected $fillable = ['name','superior_type_id'];

    public function departements()
    {
        return $this->hasMany(Departement::class, 'type_departements_id');
    }
    public function superiorType()
    {
        return $this->belongsTo(TypeDepartement::class, 'superior_type_id');
    }

}
