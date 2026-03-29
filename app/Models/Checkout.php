<?php

namespace App\Models;

use App\Enums\OperationTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkout extends Model
{
    use SoftDeletes;
    protected $table = 'checkouts';
    protected $fillable = [
        'code',
        'operation_type',
        'project_id',
        'initiale_amount',
        'used_amount',
        'available_balance',
        'collaborator_id',
        'created_by',
    ];
    protected $casts=[
        'operation_type'=>OperationTypeEnum::class,
    ];
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class);
    }

}
