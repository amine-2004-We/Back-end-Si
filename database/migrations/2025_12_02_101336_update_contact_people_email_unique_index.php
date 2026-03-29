<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE contact_people DROP CONSTRAINT IF EXISTS contact_people_email_unique;');

        DB::statement('CREATE UNIQUE INDEX contact_people_email_unique ON contact_people(email) WHERE email IS NOT NULL;');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS contact_people_email_unique;');

        DB::statement('ALTER TABLE contact_people ADD CONSTRAINT contact_people_email_unique UNIQUE (email);');
    }
};
