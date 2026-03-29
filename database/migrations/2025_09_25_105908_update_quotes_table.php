<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop check constraint manually by its name
        DB::statement('ALTER TABLE quotes DROP CONSTRAINT IF EXISTS quotes_status_check');

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn('purchase_request_id');
            $table->dropColumn('pack_id');
            $table->foreignId('purchase_list_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('En Attente')->change();
            $table->dropColumn('vat_rate');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('purchase_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pack_id')->nullable()->constrained()->nullOnDelete();
            $table->dropColumn('purchase_list_id');
        });

        // Optional: re-add the check constraint in down() if needed
        // DB::statement("ALTER TABLE quotes ADD CONSTRAINT quotes_status_check CHECK (status IN ('pending', 'accepted', 'rejected'))");
    }
};
