<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskPedagogicalSession extends Model
{
    use HasFactory;

    /**
     * @var bool
     */
    public $timestamps = true;
    /**
     * @var string[]
     */
    protected $fillable = ['task_id', 'expected_beneficiaries_count'];

     
}
