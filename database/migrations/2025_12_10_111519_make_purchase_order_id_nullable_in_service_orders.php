<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // 1. Rendre purchase_order_id nullable
            $table->unsignedBigInteger('purchase_order_id')->nullable()->change();
            
            // 2. S'assurer que calltender_id est bien nullable (au cas où)
            if (Schema::hasColumn('service_orders', 'calltender_id')) {
                $table->unsignedBigInteger('calltender_id')->nullable()->change();
            }
            
            // 3. Ajouter une contrainte CHECK pour garantir l'intégrité
            // (PostgreSQL seulement)
            if (Schema::hasColumn('service_orders', 'source_type') && 
                Schema::hasColumn('service_orders', 'purchase_order_id') && 
                Schema::hasColumn('service_orders', 'calltender_id')) {
                
                // Contrainte: au moins un des deux doit être rempli
                // Mais cette syntaxe ne fonctionne pas avec change(), donc on le fait après
            }
        });
        
        // Ajouter la contrainte CHECK (PostgreSQL)
        $this->addSourceConstraint();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            // 1. Remettre purchase_order_id en NOT NULL
            $table->unsignedBigInteger('purchase_order_id')->nullable(false)->change();
            
            // 2. Remettre calltender_id en NOT NULL si nécessaire
            if (Schema::hasColumn('service_orders', 'calltender_id')) {
                $table->unsignedBigInteger('calltender_id')->nullable(false)->change();
            }
        });
        
        // Supprimer la contrainte CHECK
        $this->dropSourceConstraint();
    }

    /**
     * Ajouter une contrainte CHECK pour garantir l'intégrité des données.
     */
    private function addSourceConstraint(): void
    {
        try {
            if (Schema::hasColumn('service_orders', 'purchase_order_id') && 
                Schema::hasColumn('service_orders', 'calltender_id')) {
                
                // Pour PostgreSQL: vérifier qu'au moins un des deux est rempli
                // Note: Cette contrainte est avancée, vous pouvez la sauter si c'est trop complexe
                \DB::statement("
                    ALTER TABLE service_orders 
                    ADD CONSTRAINT service_orders_source_check 
                    CHECK (
                        (purchase_order_id IS NOT NULL AND calltender_id IS NULL) OR
                        (purchase_order_id IS NULL AND calltender_id IS NOT NULL)
                    )
                ");
            }
        } catch (\Exception $e) {
            \Log::info("Contrainte source_check peut déjà exister ou ne pas être supportée: " . $e->getMessage());
        }
    }

    /**
     * Supprimer la contrainte CHECK.
     */
    private function dropSourceConstraint(): void
    {
        try {
            \DB::statement("ALTER TABLE service_orders DROP CONSTRAINT IF EXISTS service_orders_source_check");
        } catch (\Exception $e) {
            // Ignorer
        }
    }
};