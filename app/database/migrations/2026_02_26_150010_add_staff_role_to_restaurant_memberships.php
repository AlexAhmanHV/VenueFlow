<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_memberships', function (Blueprint $table) {
            $table->string('staff_role', 20)->nullable()->after('role');
        });

        DB::table('restaurant_memberships')
            ->where('role', 'STAFF')
            ->update(['staff_role' => 'STAFF']);
    }

    public function down(): void
    {
        Schema::table('restaurant_memberships', function (Blueprint $table) {
            $table->dropColumn('staff_role');
        });
    }
};
