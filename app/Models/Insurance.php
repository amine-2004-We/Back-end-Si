<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insurance extends Model
{
    //
    use SoftDeletes;
    protected $table = 'insurances';
    protected $fillable = [
        'insurance_id',
        'collaborator_id',
        'insurance_type',
        'insurance_organization',
        'affiliation_date',
        'termination_date',
        'comments',
    ];

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }
}
