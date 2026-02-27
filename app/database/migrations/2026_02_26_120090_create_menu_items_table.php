<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('active')->default(true);
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
