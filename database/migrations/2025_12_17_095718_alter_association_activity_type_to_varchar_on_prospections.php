<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Drop CHECK constraint (ENUM PostgreSQL)
        DB::statement("
            ALTER TABLE prospections
            DROP CONSTRAINT IF EXISTS prospections_association_activity_type_check
        ");

        // 2️⃣ Alter column to VARCHAR
        Schema::table('prospections', function (Blueprint $table) {
            $table->string('association_activity_type')
                  ->nullable()
                  ->change();
        });

          DB::statement("
            ALTER TABLE prospections
            DROP CONSTRAINT IF EXISTS prospections_douar_access_check
        ");

        // 2️⃣ Convertir la colonne en VARCHAR
        Schema::table('prospections', function (Blueprint $table) {
            $table->string('douar_access')
                  ->nullable()
                  ->change();
        });
    }

    public function down(): void
    {
        // 1️⃣ Revert column type
        Schema::table('prospections', function (Blueprint $table) {
            $table->string('association_activity_type')->nullable(false)->change();
        });

        // 2️⃣ Restore CHECK constraint
        DB::statement("
            ALTER TABLE prospections
            ADD CONSTRAINT prospections_association_activity_type_check
            CHECK (association_activity_type IN (
                'aide_humanitaire_et_assistance',
                'insertion_sociale_et_professionnelle',
                'service_a_la_personne',
                'education_et_formation',
                'activites_socioculturelles_et_sportives'
            ))
        ");

         Schema::table('prospections', function (Blueprint $table) {
            $table->string('douar_access')->nullable(false)->change();
        });

        // 2️⃣ Restaurer le CHECK
        DB::statement("
            ALTER TABLE prospections
            ADD CONSTRAINT prospections_douar_access_check
            CHECK (douar_access IN (
                'route_goudronnee',
                'accessible_en_voiture',
                'accessible_uniquement_en_4x4',
                'accessible_en_moto',
                'accessible_uniquement_a_pied'
            ))
        ");
    }
};
