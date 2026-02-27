<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->uuid('public_id')->unique();
            $table->enum('status', ['CONFIRMED', 'CANCELLED', 'NO_SHOW', 'CHECKED_IN'])->default('CONFIRMED');
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->integer('party_size');
            $table->text('note')->nullable();
            $table->string('cancel_token_hash');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_bookings');
    }
};
