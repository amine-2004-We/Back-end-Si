<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    //
    use SoftDeletes;
    protected $fillable = ['name','type_departement_id', 'parent_departement_id'];

    public function typeDepartement()
    {
        return $this->belongsTo(TypeDepartement::class, 'type_departement_id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'parent_departement_id');
    }
}
