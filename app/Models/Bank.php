<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class Bank
 */
class Bank extends Model
{
    use SoftDeletes;

    /**
     * @var array
     */
    protected $fillable = [
        'bank_code',
        'bank_id',
        'name',
        'bic_swift',
        'country',
        'currency',
    ];
}
