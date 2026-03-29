<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, PurchaseOrder $po): bool { return true; }
    public function create(User $user): bool { return $user->role === 'admin'; }
    public function update(User $user, PurchaseOrder $po): bool { return $user->role === 'admin'; }
    public function delete(User $user, PurchaseOrder $po): bool { return $user->role === 'admin'; }
    public function restore(User $user, PurchaseOrder $po): bool { return $user->role === 'admin'; }
    public function forceDelete(User $user, PurchaseOrder $po): bool { return $user->role === 'admin'; }



    public function validate(User $user, PurchaseOrder $purchaseOrder, string $level): bool
    {
        return match($level) {
            'n_plus_1' => $user->id === optional($purchaseOrder->purchaseRequests->first()->requester->superior)->user_id
                && $purchaseOrder->status === 'En attente de validation',
            'cg'       => $user->role === 'contrôleur de gestion'
                && $purchaseOrder->status === 'En attente de validation',
            'purchase' => $user->role === 'responsable achats'
                && $purchaseOrder->status === 'Validé par CG',
            'dg'       => $user->role === 'directeur général'
                && $purchaseOrder->status === 'Validé par Achats',
            default    => false,
        };
    }
}
