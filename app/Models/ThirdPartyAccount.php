<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThirdPartyAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'third_party_accounts';

    protected $fillable = ['name','general_account_id','subdivision'];

    protected $appends = ['code'];

    public function getCodeAttribute(): string
    {
        if (!$this->relationLoaded('generalAccount')) {
            $this->load('generalAccount');
        }
        if ($this->generalAccount) {
            return $this->generalAccount->class
                 . $this->generalAccount->account
                 . $this->generalAccount->sub_account
                 . $this->subdivision;
        }
        return 'Error-MissingParent-' . $this->subdivision;
    }

    public function generalAccount(): BelongsTo
    {
        return $this->belongsTo(GeneralAccount::class);
    }
}
