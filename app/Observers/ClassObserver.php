<?php

namespace App\Observers;

use App\Models\ProjectClass;
use App\Models\Unit;
use App\Services\TaskService;
use Illuminate\Support\Facades\Auth;

class ClassObserver
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function creating(ProjectClass $class)
    {
        if (Auth::check() && is_null($class->user_id)) {
            $class->user_id = Auth::id();
        }
        $class->class_id = $this->generateNextClassId($class);
        $class->class_code = $this->generateClassCode();
        $class->external_reference_code = $this->generateExternalReferenceCode();
    }

    public function updating(ProjectClass $class)
    {
        $class->loadMissing('unit.site');
        $originalUnitId = $class->getOriginal('unit_id');

        if ($class->isDirty('unit_id')) {
            $originalUnit = Unit::withTrashed()->find($originalUnitId);
            $originalSiteId = $originalUnit?->site_id;
            $newSiteId = $class->unit?->site_id;

            if ($originalSiteId !== $newSiteId) {
                $class->class_id = $this->generateNextClassId($class);
            }
        }
    }

    private function generateNextClassId(ProjectClass $class): ?string
    {
        $class->loadMissing('unit.site');
        $siteCode = $class->unit?->site?->site_id;

        if (!$siteCode) {
            return null;
        }

        $latestClass = ProjectClass::withTrashed()
            ->whereHas('unit.site', function ($query) use ($siteCode) {
                $query->where('site_id', $siteCode);
            })
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $lastNumber = 0;
        if ($latestClass && preg_match("/CLS-{$siteCode}-(\d{3})/", $latestClass->class_id, $matches)) {
            $lastNumber = (int) $matches[1];
        }
        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return "CLS-{$siteCode}-{$nextNumber}";
    }

    private function generateClassCode(): string
    {
        $lastClass = ProjectClass::withTrashed()->orderByDesc('id')->lockForUpdate()->first();
        $lastNumber = 0;

        if ($lastClass && preg_match('/CL(\d{3})/', $lastClass->class_code, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return "CL{$nextNumber}";
    }

    private function generateExternalReferenceCode(): string
    {
        $lastClass = ProjectClass::withTrashed()->orderByDesc('id')->lockForUpdate()->first();
        $lastNumber = 0;

        if ($lastClass && preg_match('/CL(\d{3})/', $lastClass->class_code, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        return "EXT-CL-{$nextNumber}";
    }
}
