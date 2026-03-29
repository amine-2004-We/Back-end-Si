<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RequestModel extends Model
{
    use SoftDeletes;
    protected $table = 'request';
    protected $fillable = [
        'pattern',//motif
        'amount',
        'month',
        'request_type_id',
        'collaborator_id',
        'request_status'
    ];

}
