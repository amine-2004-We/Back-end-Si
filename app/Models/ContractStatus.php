<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractStatus extends Model
{
    use SoftDeletes;
    protected $table = 'contract_status';
    protected $fillable = [
        'status',
    ];
}
