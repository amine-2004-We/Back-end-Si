<?php

namespace App\Observers;

use App\Models\Group;
use App\Models\ProjectClass;
use Illuminate\Support\Facades\Auth;

/**
 * class GroupObserver
 */
class GroupObserver
{
    /**
     * @param Group $group
     * @return void
     */
    public function creating(Group $group): void
    {
        if (Auth::check() && is_null($group->created_by)) {
            $group->created_by = Auth::id();
        }

        if ($group->class_id && empty($group->group_id)) {
            $class = ProjectClass::find($group->class_id);

            if ($class) {
                $classCode = $class->class_code ?? 'CL001';

                $latestGroup = Group::where('class_id', $group->class_id)
                                    ->orderBy('group_id', 'desc')
                                    ->first();

                $sequence = 1;
                if ($latestGroup) {
                    if (preg_match('/-(\d+)$/', $latestGroup->group_id, $matches)) {
                        $sequence = (int) $matches[1] + 1;
                    }
                }

                $group->group_id = sprintf('GRP-%s-%02d', $classCode, $sequence);
            }
        }

        if ($group->class_id && empty($group->code)) {
            $class = ProjectClass::find($group->class_id);

            if ($class) {
                $classCode = $class->class_code ?? 'CLASS';

                $latestGroup = Group::where('class_id', $group->class_id)
                                    ->orderBy('id', 'desc')
                                    ->first();

                $sequence = 1;
                if ($latestGroup) {
                    if (preg_match('/-(\d+)$/', $latestGroup->code, $matches)) {
                        $sequence = (int) $matches[1] + 1;
                    }
                }

                $group->code = sprintf('GRP-%s-%04d', $classCode, $sequence);
            }
        }
    }

    /**
     * Handle the Group "updating" event.
     */
    public function updating(Group $group): void
    {
        if ($group->isDirty('class_id')) {
            $newClassId = $group->class_id;
            $class = ProjectClass::find($newClassId);

            if ($class) {
                $classCode = $class->class_code ?? 'CL001';

                $latestGroup = Group::where('class_id', $newClassId)
                                    ->orderBy('group_id', 'desc')
                                    ->first();

                $sequence = 1;
                if ($latestGroup) {
                    if (preg_match('/-(\d+)$/', $latestGroup->group_id, $matches)) {
                        $sequence = (int) $matches[1] + 1;
                    }
                }

                $group->group_id = sprintf('GRP-%s-%02d', $classCode, $sequence);
            }
        }
    }
}
