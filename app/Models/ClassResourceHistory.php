<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassResourceHistory extends Model
{
    protected $table = 'class_resource_histories';

    protected $fillable = [
        'class_resource_id',
        'class_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'change_description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the class resource this history record belongs to.
     *
     * @return BelongsTo
     */
    public function classResource(): BelongsTo
    {
        return $this->belongsTo(ClassResource::class);
    }

    /**
     * Get the class this history record belongs to.
     *
     * @return BelongsTo
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ProjectClass::class, 'class_id');
    }

    /**
     * Get the user who made the change.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
}
