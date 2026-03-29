<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollaboratorStatus extends Model
{
    use SoftDeletes;
    protected $table = 'collaborator_status';
    protected $fillable = [
        'id',
        'type',
    ];
}
