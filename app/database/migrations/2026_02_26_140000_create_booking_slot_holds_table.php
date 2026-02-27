<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_slot_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->string('session_id', 120);
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['restaurant_id', 'resource_id', 'expires_at'], 'holds_rest_res_exp_idx');
            $table->index(['session_id', 'expires_at'], 'holds_session_exp_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_slot_holds');
    }
};
