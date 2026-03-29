<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class ProjectStatus
 */
class ProjectStatus extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'project_statuses';

    /**
     * @var string[]
     */
    protected $fillable = ['name'];

    /**
     * @return HasMany
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_status_id');
    }

}
