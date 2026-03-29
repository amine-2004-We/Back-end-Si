<?php

namespace App\Models;

use App\Enums\ContactStatusEnum;
use App\Enums\OriginalChannelEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactPerson extends Model
{
    use SoftDeletes;
    protected $table = 'contact_people';

    protected $fillable = [
        'partner_id',
        'supplier_id',
        'last_name',
        'first_name',
        'last_name_arabic',
        'first_name_arabic',
        'position',
        'email',
        'phone',
        'address',
        'contact_code',
        'organisation',
        'original_channel',
        'companion',
        'acquisition_date',
        'contact_status',
        'option',
        'consent_date',
        'tags',
        'api',
    ];

    /***
     * @var string[]
     */
    protected $casts = [
        'original_channel'=>OriginalChannelEnum::class,
        'contact_status'=>ContactStatusEnum::class,
    ];

    /**
     * Get the partner for this contact person.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }
    /**
     * Get the supplier for this contact person.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
