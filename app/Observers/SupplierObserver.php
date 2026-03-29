<?php

namespace App\Observers;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierObserver
{
    /**
     * Handle the Supplier "creating" event.
     */
    public function creating(Supplier $supplier): void
    {
        if (empty($supplier->supplier_id)) {
            $this->generateSupplierId($supplier);
        }
    }

    /**
     * Handle the Supplier "updating" event.
     */
    public function updating(Supplier $supplier): void
    {
        // Vérifie si l'ID a été modifié et si l'ID modifié existe déjà
        if ($supplier->isDirty('supplier_id') && Supplier::where('supplier_id', $supplier->supplier_id)->exists()) {
            $this->generateSupplierId($supplier);
        }
    }

    /**
     * Handle the Supplier "restoring" event.
     */
    public function restoring(Supplier $supplier): void
    {
        // Vérifie si l'ID existe déjà dans la base de données
        $existingSupplier = Supplier::where('supplier_id', $supplier->supplier_id)->exists();
        if ($existingSupplier) {
            $this->generateSupplierId($supplier);
        }
    }

    /**
     * Logique de génération de l'ID unique du fournisseur au format FRN-[Numéro].
     */
    private function generateSupplierId(Supplier $supplier): void
    {
        DB::transaction(function () use ($supplier) {
            // Trouve le dernier fournisseur créé en verrouillant la ligne pour la transaction
            $lastSupplier = Supplier::withTrashed()
                ->where('supplier_id', 'like', 'FRN-%')
                ->orderByDesc('supplier_id')
                ->lockForUpdate()
                ->first();

            $nextNumber = 1;

            if ($lastSupplier) {
                // Extrait le numéro séquentiel du dernier ID trouvé
                $lastSuffix = (int) Str::afterLast($lastSupplier->supplier_id, '-');
                $nextNumber = $lastSuffix + 1;
            }

            // Formate le numéro avec un padding de 3 zéros
            $formattedNextNumber = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            $supplier->supplier_id = 'FRN-' . $formattedNextNumber;
        });
    }
}