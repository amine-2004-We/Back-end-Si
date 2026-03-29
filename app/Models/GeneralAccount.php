<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneralAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'class',
        'account',
        'sub_account',
    ];

    /**
     * Get the formatted general account code.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function code(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->class . $this->account . $this->sub_account . '0000',
        );
    }

    public function thirdPartyAccounts(): HasMany
    {
        return $this->hasMany(ThirdPartyAccount::class);
    }
}

