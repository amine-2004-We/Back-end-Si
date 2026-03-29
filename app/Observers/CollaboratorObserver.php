<?php

namespace App\Observers;

use App\Models\Collaborator;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class CollaboratorObserver
{
    /**
     * Génération du code collaborateur si vide
     */
    public function creating(Collaborator $collaborator)
    {
        if (empty($collaborator->collaborator_code)) {
            $lastCollaborator = Collaborator::withTrashed()
                ->whereNotNull('collaborator_code')
                ->orderByRaw("CAST(collaborator_code AS INTEGER) DESC")
                ->first();

            $nextNumber = $lastCollaborator ? (int) $lastCollaborator->collaborator_code + 1 : 1;
            $collaborator->collaborator_code = str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Mise à jour des champs du User lié et du rôle si besoin
     */
    public function updated(Collaborator $collaborator)
    {
        if (!$collaborator->user_id || !$collaborator->user) {
            return;
        }

        DB::transaction(function () use ($collaborator) {

            $user = $collaborator->user;
            $dirty = [];

            $dirty['name'] = $collaborator->first_name . ' ' . $collaborator->last_name;

            if ($collaborator->wasChanged('email')) {
                $dirty['email'] = $collaborator->email;
            }

            if ($collaborator->wasChanged('password') && !empty($collaborator->password)) {
                $dirty['password'] = $collaborator->password;
            }

            if (!empty($dirty)) {
                $user->update($dirty);
            }

            // Mise à jour du rôle si la position a changé
            if ($collaborator->wasChanged('position_id')) {
                if ($collaborator->position) {
                    $roleName = substr($collaborator->position->title ?? 'Collaborateur', 0, 255);
                    $role = Role::firstOrCreate(['name' => $roleName]);
                    $user->syncRoles([$role]);
                } else {
                    // Si plus de position, retirer tous les rôles
                    $user->syncRoles([]);
                }
            }
        });
    }
}
