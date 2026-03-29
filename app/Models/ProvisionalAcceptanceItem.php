<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProvisionalAcceptanceItem extends Model
{
    protected $table = 'provisional_acceptance_items';

    public $timestamps = true;

    protected $fillable = [
        'provisional_acceptance_id',
        'article_id',
        'quantity_received',
    ];

    /**
     * Le PV parent.
     */
    public function provisionalAcceptance(): BelongsTo
    {
        return $this->belongsTo(ProvisionalAcceptance::class, 'provisional_acceptance_id');
    }

    /**
     * L'article concerné.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
