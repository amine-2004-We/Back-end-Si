<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectNote extends Model
{
    use HasFactory;

    protected $table = 'project_notes'; // Sécurité

    protected $fillable = [
        'project_id',
        'user_id',
        'content'
    ];

    /**
     * Le projet concerné par la note.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * L'auteur de la note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
