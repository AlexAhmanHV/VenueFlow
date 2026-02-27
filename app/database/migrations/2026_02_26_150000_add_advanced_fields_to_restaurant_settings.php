<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->integer('slot_interval_minutes')->default(15)->after('default_buffer_minutes');
            $table->integer('max_simultaneous_bookings')->nullable()->after('slot_interval_minutes');
            $table->json('default_durations')->nullable()->after('max_simultaneous_bookings');
        });
    }

    public function down(): void
    {
        Schema::table('restaurant_settings', function (Blueprint $table) {
            $table->dropColumn([
                'slot_interval_minutes',
                'max_simultaneous_bookings',
                'default_durations',
            ]);
        });
    }
};
