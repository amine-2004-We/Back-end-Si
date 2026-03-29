<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassStatusHistory extends Model
{
    protected $table = 'class_status_histories';

    protected $fillable = [
        'class_id',
        'change_type', // 'state' or 'status'
        'old_value',
        'new_value',
        'change_date',
        'reason',
        'transfer_to_project_id',
        'relocate_to_class_id',
        'perpetuation_project_id',
        'user_id',
    ];

    protected $casts = [
        'change_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the class associated with this history record.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ProjectClass::class, 'class_id');
    }

    /**
     * Get the user who made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the project for transfer (if applicable).
     */
    public function transferToProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'transfer_to_project_id');
    }

    /**
     * Get the class for relocation (if applicable).
     */
    public function relocateToClass(): BelongsTo
    {
        return $this->belongsTo(ProjectClass::class, 'relocate_to_class_id');
    }

    /**
     * Get the project for perpetuation (if applicable).
     */
    public function perpetuationProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'perpetuation_project_id');
    }
}
