<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_id' => $this->report_id, 
            'type' => $this->type,
            'task_id' => $this->task_id,
            'title' => $this->title,
            'event_date' => $this->event_date,
            'author_id' => $this->author_id,
            'summary' => $this->summary,
            'positive_points' => $this->positive_points,
            'recommendations' => $this->recommendations,
            'attachment_path' => $this->attachment_path, 
            'status' => $this->status,
            'creator_id' => $this->creator_id,
            'creator_name' => $this->creator ? $this->creator->name : null, 
            'author_name' => $this->author ? $this->author->first_name : null, 
            'task_name' => $this->task ? $this->task->title : null, 
            'created_at' => $this->created_at,
            'deleted_at' => $this->deleted_at, 
            
        ];
    }
}
