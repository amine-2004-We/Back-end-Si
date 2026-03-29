<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];
}