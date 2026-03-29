<?php

namespace App\Models;

use App\Observers\PurchaseListObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\PurchaseRequest;
use App\Models\Article;
use App\Models\Quote;
use App\Models\User;
use App\Models\Department;

#[ObservedBy([PurchaseListObserver::class])]
class PurchaseList extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'purchase_list_id',
        'request_reference_id',
        'created_by',
        'emitting_department',
        'articles_list',
        'total_quantity_requested',
        'priority',
        'observations',
        'status',
        'deleted_at'
    ];

    protected $casts = [
        'articles_list' => 'array',
    ];

    // Créateur
    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Demandes d'achat liées
    public function requests() {
        return $this->belongsToMany(
            PurchaseRequest::class,
            'purchase_list_request',
            'purchase_list_id',
            'purchase_request_id'
        )->withTimestamps();
    }

    // Articles liés avec quantité
    public function items() {
        return $this->belongsToMany(
            Article::class,
            'purchase_list_items',
            'purchase_list_id',
            'article_id'
        )->withPivot('quantity')->withTimestamps();
    }

    /**
     * @return HasMany<Quote, PurchaseList>
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * @return BelongsTo<User, PurchaseList>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
     /**
     * Relation avec le département émetteur (emitting_department)
     */
    public function department()
    {
        return $this->belongsTo(Departement::class, 'emitting_department');
    }
}

