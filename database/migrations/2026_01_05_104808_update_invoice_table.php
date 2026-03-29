<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $tableName = 'invoices';
    protected string $columnName = 'purchase_order_id';
    protected string $foreignKey = 'invoices_purchase_order_id_foreign';
    protected string $indexName = 'invoices_purchase_order_id_index';

    public function up(): void
    {
        if (!Schema::hasColumn($this->tableName, $this->columnName)) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->unsignedBigInteger($this->columnName)
                    ->nullable()
                    ->after('receipt_id');
            });
        }

        $this->ensureIndexExists();

        if (Schema::hasTable('purchase_orders') && !$this->foreignKeyExists()) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->foreign($this->columnName)
                    ->references('id')
                    ->on('purchase_orders')
                    ->onDelete('set null');
            });
        }
    }

    private function ensureIndexExists(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $exists = DB::selectOne("
                SELECT 1
                FROM pg_indexes
                WHERE schemaname = 'public'
                AND tablename = ?
                AND indexname = ?
            ", [$this->tableName, $this->indexName]);
        } elseif ($driver === 'mysql') {
            $exists = DB::selectOne("
                SHOW INDEX FROM {$this->tableName}
                WHERE Key_name = ?
            ", [$this->indexName]);
        } else {
            $exists = false;
        }

        if (!$exists) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->index($this->columnName, $this->indexName);
            });
        }
    }

    private function foreignKeyExists(): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            $check = DB::selectOne("
                SELECT 1
                FROM information_schema.table_constraints
                WHERE constraint_schema = 'public'
                AND table_name = ?
                AND constraint_name = ?
            ", [$this->tableName, $this->foreignKey]);

            return $check !== null;
        } elseif ($driver === 'mysql') {
            $check = DB::selectOne("
                SELECT 1
                FROM information_schema.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = ?
                AND CONSTRAINT_NAME = ?
            ", [$this->tableName, $this->foreignKey]);

            return $check !== null;
        }

        return false;
    }

    public function down(): void
    {
        if ($this->foreignKeyExists()) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropForeign($this->foreignKey);
            });
        }

        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement("DROP INDEX IF EXISTS {$this->indexName}");
        } elseif ($driver === 'mysql') {
            DB::statement("DROP INDEX {$this->indexName} ON {$this->tableName}");
        }

        if (Schema::hasColumn($this->tableName, $this->columnName)) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropColumn($this->columnName);
            });
        }
    }
};
