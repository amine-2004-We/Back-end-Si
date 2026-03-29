<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuoteItem extends Model
{
    use SoftDeletes;

    protected $table = 'quote_items';

    protected $fillable = [
        'quote_id',
        'article_id',
        'quantity',
        'unit_price_ht',
        'tva_rate',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'unit_price_ht' => 'decimal:2',
    ];

    // --- Relations ---
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    // --- Helper ---
    public function getLineTotalHtAttribute(): float
    {
        return round($this->quantity * $this->unit_price_ht, 2);
    }
}
