<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassStatus extends Model
{
    use SoftDeletes;
    protected $table = 'class_status';
    protected $fillable=[
        'name'
    ];
}
