<?php

namespace App\Models;

use App\Enums\FinalAcceptanceStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinalAcceptance extends Model
{
    use SoftDeletes;

    protected $table = 'final_acceptances';

    protected $fillable = [
        'identifier',
        'calltender_id',
        'final_acceptance_date',
        'observations',
        'status',
        'auto_close_contract',
    ];

    protected $casts = [
        'final_acceptance_date' => 'date',
        'status' => FinalAcceptanceStatusEnum::class,
        'auto_close_contract' => 'boolean',
    ];

    /**
     * Le Marché/Contrat associé.
     */
    public function calltender(): BelongsTo
    {
        return $this->belongsTo(Calltender::class, 'calltender_id');
    }

    /**
     * Les articles concernés (Liste).
     */
    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'final_acceptance_article', 'final_acceptance_id', 'article_id');
    }

    /**
     * Le comité de réception (Liste d'utilisateurs).
     */
    public function committeeMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'final_acceptance_user', 'final_acceptance_id', 'user_id');
    }

    /**
     * Les PV Provisoires associés (relation many-to-many).
     */
    public function provisionalAcceptances(): BelongsToMany
    {
        return $this->belongsToMany(
            ProvisionalAcceptance::class,
            'final_acceptance_provisional_acceptance',
            'final_acceptance_id',
            'provisional_acceptance_id'
        );
    }
}
