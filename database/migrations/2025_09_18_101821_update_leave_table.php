<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Supprimer l'ancienne contrainte check si elle existe
        DB::statement("ALTER TABLE leave DROP CONSTRAINT IF EXISTS leave_status_check");

        // Modifier le type de la colonne en VARCHAR
        DB::statement("ALTER TABLE leave ALTER COLUMN status TYPE VARCHAR(255)");

        // Ajouter la contrainte CHECK pour les statuts anglais
        DB::statement("
            ALTER TABLE leave
            ADD CONSTRAINT leave_status_check
            CHECK (status IN (
                'pending',
                'approved_by_manager',
                'approved_by_hr'
            ))
        ");

        // Définir la valeur par défaut
        DB::statement("ALTER TABLE leave ALTER COLUMN status SET DEFAULT 'pending'");
        DB::statement("ALTER TABLE leave ALTER COLUMN status SET NOT NULL");
    }

    public function down(): void
    {
        // Supprimer la contrainte
        DB::statement("ALTER TABLE leave DROP CONSTRAINT IF EXISTS leave_status_check");

        // Revenir à un VARCHAR simple
        DB::statement("ALTER TABLE leave ALTER COLUMN status TYPE VARCHAR(255)");
        DB::statement("ALTER TABLE leave ALTER COLUMN status DROP DEFAULT");
    }
};
