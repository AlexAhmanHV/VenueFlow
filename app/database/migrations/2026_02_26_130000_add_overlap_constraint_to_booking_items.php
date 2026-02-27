<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_items', function (Blueprint $table) {
            $table->softDeletes();
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');

            // DB-level overlap protection for PostgreSQL.
            DB::statement(
                <<<'SQL'
                ALTER TABLE booking_items
                ADD CONSTRAINT booking_items_no_overlap
                EXCLUDE USING gist (
                    resource_id WITH =,
                    tsrange(
                        start_time - make_interval(mins => buffer_before_min),
                        end_time + make_interval(mins => buffer_after_min),
                        '[)'
                    ) WITH &&
                )
                WHERE (deleted_at IS NULL)
                SQL
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE booking_items DROP CONSTRAINT IF EXISTS booking_items_no_overlap');
        }

        Schema::table('booking_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
