<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * class ProductController
 */
class ProductType extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'name', 'deleted_at'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
