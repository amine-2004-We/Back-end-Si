<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ExternalAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'file_url' => Storage::url($this->file_path),
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}