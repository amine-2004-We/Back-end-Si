<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * class ProjectType
 */
class ProjectType extends Model
{
    use SoftDeletes;
    /**
     * @var string
     */
    protected $table = 'project_types';

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Project>
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_type_id');
    }

}
