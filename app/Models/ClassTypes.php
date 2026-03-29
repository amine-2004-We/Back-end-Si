<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassTypes extends Model
{
    use SoftDeletes;
    protected $table = 'class_types';
    protected $fillable = [
        'name',
    ];
}
