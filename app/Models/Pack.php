<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


class Pack extends Model
{
    use SoftDeletes;

    protected $fillable = ['pack_id', 'name', 'description','created_by', 'deleted_at'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_pack')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
