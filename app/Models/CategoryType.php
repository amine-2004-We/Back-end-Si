<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoryType extends Model
{
    use SoftDeletes;
    protected $table = 'category_types';

    protected $fillable = [
        'name',
    ];
}
