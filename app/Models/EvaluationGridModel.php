<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationGridModel extends Model
{
    use SoftDeletes;
    protected $table = 'evaluation_grid';
    protected $fillable = [
        "grid_code" ,
        "title",
        "object_project",
        "object_partner",
        "evaluation_frequency",
        "scoring_method",
        "rating_scale",
        "weighting_criterion",
        "grid_status",
        "comment",
        "created_by",
        "attachment"
    ];
}
