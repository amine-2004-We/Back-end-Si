<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class PurchaseRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id',
        'code',
        'department_id',
        'project_id',
        'user_id',
        'status',
        'observations',
        'priority',
    ];

    public function department() { return $this->belongsTo(Departement::class); }
    public function project() { return $this->belongsTo(Project::class); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function products() { return $this->hasMany(PurchaseRequestLine::class); }
}
