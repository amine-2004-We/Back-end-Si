<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Country; 

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropUnique(['code']);
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->foreignId('country_id')
                  ->nullable()
                  ->constrained('countries')
                  ->onDelete('cascade')
                  ->after('id');
        });

        $morocco = Country::firstOrCreate(
            ['code' => 'MA'],
            ['name' => 'Morocco']
        );
        DB::table('regions')->update(['country_id' => $morocco->id]);

        Schema::table('regions', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable(false)->change();
            $table->unique(['code', 'country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropUnique(['code', 'country_id']);
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn('country_id');
            $table->unique('code');
        });
    }
};
