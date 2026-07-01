<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_bookings', function (Blueprint $table) {
            $table->string('recurrence_rule')->nullable()->after('note');
            $table->foreignId('parent_booking_id')
                ->nullable()
                ->after('recurrence_rule')
                ->constrained('guest_bookings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guest_bookings', function (Blueprint $table) {
            $table->dropForeign(['parent_booking_id']);
            $table->dropColumn(['recurrence_rule', 'parent_booking_id']);
        });
    }
};
