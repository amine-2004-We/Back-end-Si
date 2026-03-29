<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPartner extends Pivot
{
    protected  $table = "project_partner";

    public const ROLE_PRINCIPAL = 'principal';
    public const ROLE_SECONDAIRE = 'secondaire';

    public const PARTNER_ROLES = [self::ROLE_PRINCIPAL, self::ROLE_SECONDAIRE];

    protected $fillable = [
        'project_id',
        'partner_id',
        'partner_role',
        'partner_contribution',
        'partner_amount'
    ];

    protected $casts = [
        'partner_contribution' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
