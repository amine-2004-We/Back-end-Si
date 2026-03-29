<?php

namespace App\Models;

use App\Enums\AvenantModificationNatureEnum;
use App\Enums\AvenantStatusEnum;
use App\Models\Calltender;
use App\Models\Collaborator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Avenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'avenant_id',
        'marche_id',
        'subject',
        'modification_nature',
        'additional_amount',
        'new_end_date',
        'document_path',
        'responsible_id',
        'status',
        'signature_date',
        'observations'
    ];

    protected $casts = [
        'modification_nature' => AvenantModificationNatureEnum::class,
        'status' => AvenantStatusEnum::class,
        'additional_amount' => 'decimal:2',
        'signature_date' => 'date',
        'new_end_date' => 'date',
    ];

    public function responsible()
    {
        return $this->belongsTo(Collaborator::class, 'responsible_id');
    }

    public function marche()
    {
        return $this->belongsTo(Calltender::class, 'marche_id');
    }
}

