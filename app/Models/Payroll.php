<?php


namespace App\Models;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Payroll extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'payrolls';


    /**
     * @var array
     */
    protected $fillable = [
        'payroll_code',
        'collaborator_id',
        'period',
        'unjustified_absences_days',
        'justified_absences_days',
        'maternity_days',
        'backpay_days',
        'contract_start_date',
        'contract_end_date',
        'exit_date',
        'benefits',
        'benefits_total',
        'deductions',
        'deductions_total',
        'net_amount',
        'status',
    ];


    /**
     * @var array
     */
    protected $casts = [
        'period'                 => 'date',
        'contract_start_date'    => 'date',
        'contract_end_date'      => 'date',
        'exit_date'              => 'date',
        'benefits'               => 'array',
        'deductions'             => 'array',
        'benefits_total'         => 'decimal:2',
        'deductions_total'       => 'decimal:2',
        'net_amount'             => 'decimal:2',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Collaborator, Payroll>
     */
    public function collaborator()
    {
        return $this->belongsTo(Collaborator::class);
    }


    /**
     * @return string
     */
    public function getFormattedCodeAttribute(): string
    {
        return strtoupper($this->payroll_code);
    }


    /**
     * Scope: filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }


    /**
     * Scope: filter by year/month
     */
    public function scopeForPeriod($query, string $year, string $month)
    {
        return $query->whereYear('period', $year)->whereMonth('period', $month);
    }


    // =========================================================================
    // HR DATA ACCESSORS - Get data from RH tables
    // =========================================================================

    /**
     * Get the contract start date (Date entrée) from collaborator
     */
    public function getContractStartDateFromHrAttribute(): ?Carbon
    {
        return $this->collaborator?->entry_date ? Carbon::parse($this->collaborator->entry_date) : null;
    }

    /**
     * Get the contract end date from collaborator (if applicable)
     */
    public function getContractEndDateFromHrAttribute(): ?Carbon
    {
        // This could be based on contract type or a specific field
        return null; // Override based on your contract logic
    }

    /**
     * Get the exit date (Date sortie) from collaborator
     */
    public function getExitDateFromHrAttribute(): ?Carbon
    {
        return $this->collaborator?->exit_date ? Carbon::parse($this->collaborator->exit_date) : null;
    }

    /**
     * Get presence sheets for the payroll period
     * Uses PresenceSheet table - participants are stored as JSON array
     */
    public function getPresenceSheetsForPeriod()
    {
        if (!$this->collaborator_id || !$this->period) {
            return collect();
        }

        $startOfMonth = Carbon::parse($this->period)->startOfMonth();
        $endOfMonth = Carbon::parse($this->period)->endOfMonth();

        return PresenceSheet::whereBetween('event_date', [$startOfMonth, $endOfMonth])
            ->get();
    }

    /**
     * Get all presence entries for this collaborator from PresenceSheet
     * Filters the participants array to find entries for this collaborator
     */
    public function getCollaboratorPresencesForPeriod(): array
    {
        $presenceSheets = $this->getPresenceSheetsForPeriod();
        $collaboratorPresences = [];

        foreach ($presenceSheets as $sheet) {
            $participants = is_array($sheet->participants) ? $sheet->participants : [];
            
            foreach ($participants as $participant) {
                if (!is_array($participant)) {
                    continue;
                }
                
                // Check if this participant is the collaborator
                // Match by personable_id AND personable_type = Collaborator
                $personableId = $participant['personable_id'] ?? null;
                $personableType = $participant['personable_type'] ?? null;
                
                $isCollaborator = $personableType === 'App\\Models\\Collaborator' 
                    || $personableType === Collaborator::class;
                
                if ($isCollaborator && $personableId == $this->collaborator_id) {
                    $collaboratorPresences[] = [
                        'event_date' => $sheet->event_date,
                        'status' => $participant['status'] ?? null,
                        'justification' => $participant['justification'] ?? null,
                        'arrival_time' => $participant['arrival_time'] ?? null,
                    ];
                }
            }
        }

        return $collaboratorPresences;
    }

    /**
     * Calculate unjustified absences days from PresenceSheet table
     * Count days where status is 'Absent' without justification
     */
    public function getUnjustifiedAbsencesDaysFromHrAttribute(): int
    {
        $presences = $this->getCollaboratorPresencesForPeriod();
        
        // Count absences without justification
        return collect($presences)->filter(function ($presence) {
            return $presence['status'] === 'Absent' && empty($presence['justification']);
        })->count();
    }

    /**
     * Calculate justified absences days from PresenceSheet table
     * Count days where status is 'Absent' with justification
     */
    public function getJustifiedAbsencesDaysFromHrAttribute(): int
    {
        $presences = $this->getCollaboratorPresencesForPeriod();
        
        // Count absences with justification
        return collect($presences)->filter(function ($presence) {
            return $presence['status'] === 'Absent' && !empty($presence['justification']);
        })->count();
    }

    /**
     * Get leaves (congés) for the payroll period
     */
    public function getLeavesForPeriod()
    {
        if (!$this->collaborator_id || !$this->period) {
            return collect();
        }

        $startOfMonth = Carbon::parse($this->period)->startOfMonth();
        $endOfMonth = Carbon::parse($this->period)->endOfMonth();

        return Leave::where('collaborator_id', $this->collaborator_id)
            ->where('status', 'approved') // Only approved leaves
            // ->where(function ($query) use ($startOfMonth, $endOfMonth) {
            //     $query->whereBetween('start_date', [$startOfMonth, $endOfMonth])
            //         ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
            //         ->orWhere(function ($q) use ($startOfMonth, $endOfMonth) {
            //             $q->where('start_date', '<=', $startOfMonth)
            //               ->where('end_date', '>=', $endOfMonth);
            //         });
            // })
            ->with('leaveType')
            ->get();
    }

    /**
     * Calculate maternity leave days from Leave table
     */
    public function getMaternityDaysFromHrAttribute(): int
    {
        $leaves = $this->getLeavesForPeriod();
        
        // Filter leaves by maternity type
        $maternityLeaves = $leaves->filter(function ($leave) {
            $typeName = strtolower($leave->leaveType?->name ?? '');
            return str_contains($typeName, 'maternité') || str_contains($typeName, 'maternite');
        });

        return $this->calculateLeaveDaysInPeriod($maternityLeaves);
    }

    /**
     * Get all leave types and their days for the period
     */
    public function getLeavesSummaryAttribute(): array
    {
        $leaves = $this->getLeavesForPeriod();
        $summary = [];

        foreach ($leaves as $leave) {
            $typeName = $leave->leaveType?->name ?? 'Autre';
            $days = $this->calculateLeaveDaysInPeriodForLeave($leave);
            
            if (!isset($summary[$typeName])) {
                $summary[$typeName] = 0;
            }
            $summary[$typeName] += $days;
        }

        return $summary;
    }

    /**
     * Calculate leave days within the payroll period
     */
    protected function calculateLeaveDaysInPeriod($leaves): int
    {
        $totalDays = 0;
        
        foreach ($leaves as $leave) {
            $totalDays += $this->calculateLeaveDaysInPeriodForLeave($leave);
        }

        return $totalDays;
    }

    /**
     * Calculate leave days for a single leave within the payroll period
     */
    protected function calculateLeaveDaysInPeriodForLeave($leave): int
    {
        $periodStart = Carbon::parse($this->period)->startOfMonth();
        $periodEnd = Carbon::parse($this->period)->endOfMonth();

        $leaveStart = Carbon::parse($leave->start_date);
        $leaveEnd = Carbon::parse($leave->end_date);

        // Clamp to period boundaries
        $effectiveStart = $leaveStart->max($periodStart);
        $effectiveEnd = $leaveEnd->min($periodEnd);

        if ($effectiveStart > $effectiveEnd) {
            return 0;
        }

        // Use nbr_days if the leave is entirely within the period
        if ($leaveStart >= $periodStart && $leaveEnd <= $periodEnd) {
            return (int) ($leave->nbr_days ?? $effectiveStart->diffInDays($effectiveEnd) + 1);
        }

        // Otherwise, calculate the overlapping days
        return $effectiveStart->diffInDays($effectiveEnd) + 1;
    }

    /**
     * Populate payroll with HR data
     * Call this method to auto-fill fields from HR tables
     */
    public function populateFromHrData(): self
    {
        // Get dates from collaborator (contrat)
        $this->contract_start_date = $this->contract_start_date_from_hr;
        $this->exit_date = $this->exit_date_from_hr;

        // Get absences from Presence table
        $this->unjustified_absences_days = $this->unjustified_absences_days_from_hr;
        $this->justified_absences_days = $this->justified_absences_days_from_hr;

        // Get maternity days from Leave table
        $this->maternity_days = $this->maternity_days_from_hr;

        return $this;
    }
}

