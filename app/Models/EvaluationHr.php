<?php

namespace App\Models;

use App\Enums\EvaluationHrValueEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationHr extends Model
{
    use SoftDeletes;

    protected $table = 'evaluations_hr';

    protected $fillable = [
        "collaborator_id",
        "name",
        "description",
        "objectif_nature",
        "measurement_indicators",
        "weight",
        "skill",
        "indicators",
        "value",
    ];
    protected $casts = [
        "value" => EvaluationHrValueEnum::class,
    ];

    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }
}
