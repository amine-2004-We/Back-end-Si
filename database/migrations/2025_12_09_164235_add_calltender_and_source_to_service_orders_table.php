<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('service_orders', 'calltender_id')) {
                $table->unsignedBigInteger('calltender_id')->nullable()->after('purchase_order_id');
            }
            
            if (!Schema::hasColumn('service_orders', 'source_type')) {
                $table->string('source_type', 20)->nullable()->after('calltender_id');
            }
        });
        
        $this->addSourceTypeConstraint();
        
        $this->updateExistingDataSafely();
        
        $this->addConstraintsSafely();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
           
            $this->dropCheckConstraintIfExists();
            
            $this->dropForeignKeyIfExists();
            
            $this->dropIndexIfExists('service_orders_source_type_index');
            
            $columns = ['calltender_id', 'source_type'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('service_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Ajouter une contrainte CHECK pour source_type (PostgreSQL).
     */
    private function addSourceTypeConstraint(): void
    {
        try {
            if (Schema::hasColumn('service_orders', 'source_type')) {
                DB::statement("
                    ALTER TABLE service_orders 
                    ADD CONSTRAINT service_orders_source_type_check 
                    CHECK (source_type IN ('purchase_order', 'calltender') OR source_type IS NULL)
                ");
            }
        } catch (\Exception $e) {
            \Log::info("Contrainte source_type peut déjà exister: " . $e->getMessage());
        }
    }

    /**
     * Mettre à jour les données existantes de manière safe.
     */
    private function updateExistingDataSafely(): void
    {
        try {
            if (!Schema::hasTable('service_orders') || !Schema::hasColumn('service_orders', 'source_type')) {
                return;
            }
            
            DB::table('service_orders')
                ->whereNotNull('purchase_order_id')
                ->where(function($query) {
                    $query->whereNull('source_type')
                          ->orWhere('source_type', '');
                })
                ->update(['source_type' => 'purchase_order']);
            
        } catch (\Exception $e) {
            \Log::error("Erreur lors de la mise à jour des données ServiceOrder: " . $e->getMessage());
        }
    }

    /**
     * Ajouter les contraintes de manière sécurisée.
     */
    private function addConstraintsSafely(): void
    {
        try {
            if (Schema::hasTable('calltenders') && Schema::hasColumn('service_orders', 'calltender_id')) {
                $hasConstraint = DB::selectOne("
                    SELECT conname as constraint_name
                    FROM pg_constraint 
                    WHERE conrelid = 'service_orders'::regclass
                    AND conname LIKE '%calltender_id%'
                ");
                
                if (!$hasConstraint) {
                    $invalidCalltenders = DB::table('service_orders')
                        ->whereNotNull('calltender_id')
                        ->whereNotExists(function($query) {
                            $query->select(DB::raw(1))
                                  ->from('calltenders')
                                  ->whereColumn('calltenders.id', 'service_orders.calltender_id');
                        })
                        ->count();
                    
                    if ($invalidCalltenders == 0) {
                        Schema::table('service_orders', function (Blueprint $table) {
                            $table->foreign('calltender_id')
                                  ->references('id')
                                  ->on('calltenders')
                                  ->onDelete('set null');
                        });
                    }
                }
            }
            
            if (Schema::hasColumn('service_orders', 'source_type')) {
                $hasIndex = DB::selectOne("
                    SELECT indexname 
                    FROM pg_indexes 
                    WHERE tablename = 'service_orders' 
                    AND indexname LIKE '%source_type%'
                ");
                
                if (!$hasIndex) {
                    Schema::table('service_orders', function (Blueprint $table) {
                        $table->index('source_type', 'service_orders_source_type_index');
                    });
                }
            }
            
        } catch (\Exception $e) {
            \Log::warning("Migration ServiceOrder: Impossible d'ajouter certaines contraintes: " . $e->getMessage());
        }
    }

    /**
     * Supprimer la contrainte CHECK si elle existe.
     */
    private function dropCheckConstraintIfExists(): void
    {
        try {
            $constraint = DB::selectOne("
                SELECT conname 
                FROM pg_constraint 
                WHERE conrelid = 'service_orders'::regclass
                AND conname = 'service_orders_source_type_check'
            ");
            
            if ($constraint) {
                DB::statement("ALTER TABLE service_orders DROP CONSTRAINT {$constraint->conname}");
            }
        } catch (\Exception $e) {
            // Ignorer
        }
    }

    /**
     * Supprimer la clé étrangère si elle existe (PostgreSQL).
     */
    private function dropForeignKeyIfExists(): void
    {
        try {
            $foreignKey = DB::selectOne("
                SELECT conname 
                FROM pg_constraint 
                WHERE conrelid = 'service_orders'::regclass
                AND contype = 'f'
                AND conname LIKE '%calltender_id%'
            ");
            
            if ($foreignKey) {
                Schema::table('service_orders', function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey->conname);
                });
            }
        } catch (\Exception $e) {
            // Ignorer
        }
    }

    /**
     * Supprimer un index si il existe (PostgreSQL).
     */
    private function dropIndexIfExists(string $indexName): void
    {
        try {
            $index = DB::selectOne("
                SELECT indexname 
                FROM pg_indexes 
                WHERE tablename = 'service_orders' 
                AND indexname = '{$indexName}'
            ");
            
            if ($index) {
                Schema::table('service_orders', function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }
        } catch (\Exception $e) {
            // Ignorer
        }
    }
};