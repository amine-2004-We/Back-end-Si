<?php

namespace App\Observers;

use App\Models\RecruitmentRequest;

use App\Models\Collaborator;

use App\Models\Position;
use Illuminate\Support\Facades\Log;
use App\Services\Notification\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Services\NotificationService;

class RecruitmentRequestObserver
{
    /**
     * Handle the RecruitmentRequest "creating" event.
     */
    public function creating(RecruitmentRequest $recruitmentRequest): void
    {
        if (empty($recruitmentRequest->request_id)) {
            $this->generateRequestId($recruitmentRequest);
        }
    }

    /**
     * Handle the RecruitmentRequest "updating" event.
     * Prevents manually setting a duplicate ID.
     */
    public function updating(RecruitmentRequest $recruitmentRequest): void
    {
        if ($recruitmentRequest->isDirty('request_id') && RecruitmentRequest::where('request_id', $recruitmentRequest->request_id)->exists()) {
            $this->generateRequestId($recruitmentRequest);
        }
    }

    /**
     * Handle the RecruitmentRequest "restoring" event.
     * Prevents restoring a model with an ID that now exists.
     */
    public function restoring(RecruitmentRequest $recruitmentRequest): void
    {
        $existingRequest = RecruitmentRequest::where('request_id', $recruitmentRequest->request_id)->exists();
        if ($existingRequest) {
            $this->generateRequestId($recruitmentRequest);
        }
    }

    /**
     * Logic to generate the unique request ID in the format DRQ-[Poste]-[Date]-[Sequence].
     * Uses a transaction and row locking to prevent race conditions.
     */
   private function generateRequestId(RecruitmentRequest $recruitmentRequest): void
{
    DB::transaction(function () use ($recruitmentRequest) {
        
      
        
        if ($recruitmentRequest->position_id) {
            $position = $recruitmentRequest->position()->firstOrFail();
            $posteCode = Str::upper(trim($position->position_code));
        } else {
            $posteCode = 'NEW'; 
        }
        
  
        $dateCode = Carbon::now()->format('dmY'); // e.g., 20231027

        $baseIdPattern = 'DRQ-' . $posteCode . '-' . $dateCode . '%';

        $lastRequest = RecruitmentRequest::withTrashed()
            ->where('request_id', 'like', $baseIdPattern)
            ->orderByDesc('request_id')
            ->lockForUpdate()
            ->first();

        $nextSequence = 1;
        if ($lastRequest) {
            $lastSequence = (int) Str::afterLast($lastRequest->request_id, '-');
            $nextSequence = $lastSequence + 1;
        }

        $formattedSequence = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

        $recruitmentRequest->request_id = 'DRQ-' . $posteCode . '-' . $dateCode . '-' . $formattedSequence;
    });

}
public function updated(RecruitmentRequest $recruitmentRequest)
{
    if ($recruitmentRequest->wasChanged('status') && $recruitmentRequest->status === 'Validée') {
       $position = Position::where('title', 'Responsable de recrutement')->first();

                if (!$position) {
                    Log::warning("Position 'Responsable de recrutement' non trouvée");
                    return;
                }

                $collaborators = Collaborator::where('position_id', $position->id)->get();


        if ($collaborators->isEmpty()) return;

        $emails = $collaborators->pluck('email')->filter()->toArray();
        if (empty($emails)) return;

        // Ids Notif Service
        $recipientIds = $collaborators->pluck('id')->toArray();

        $position = $recruitmentRequest->position;
        $department = $recruitmentRequest->department;

        MailService::sendMail(
            $emails,
            "Validation d'une demande de recrutement - {$recruitmentRequest->request_id}",
            'emails.recruitment-validation',
            [
                'title' => 'Demande de recrutement validée',
                'recruitment_request' => $recruitmentRequest,
                'position_title' => $position->title ?? 'Non spécifié',
                'position_code' => $position->position_code ?? 'N/A',
                'department_name' => $department->name ?? 'Non spécifié',
                'number_of_positions' => $recruitmentRequest->number_of_positions ?? 1,
                'recruitment_reason' => $recruitmentRequest->recruitment_reason ?? 'Non spécifié',
                'desired_start_date' => $recruitmentRequest->desired_start_date ?? 'Non spécifié',
                'request_id' => $recruitmentRequest->request_id,
            ]
        );
    }

    // SYSTEM NOTIF
    $senderId = optional($recruitmentRequest->creator?->collaborator)->id;
    if (!$senderId) {
        return; 
    }
    if (empty($recipientIds)) {
        return;
    }
    try {
        $notification = app(NotificationService::class)->save(
            "Demande de recrutement validée",
            "La demande {$recruitmentRequest->request_id} a été validée.",
            $senderId,
            [
                'type' => 'open_modal',
                'name' => 'view_recruitment_request',
                'id'   => $recruitmentRequest->id,
            ],
            $recipientIds
        );  
    } catch (\Throwable $e) {
        Log::error('RecruitmentRequestObserver: system notification failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'recruitment_request_id' => $recruitmentRequest->id,
        ]);
    }

}

}