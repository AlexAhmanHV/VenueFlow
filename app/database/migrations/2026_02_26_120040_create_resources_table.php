<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['GOLF', 'SHUFFLEBOARD', 'DART', 'BILLIARDS', 'TABLE']);
            $table->string('name');
            $table->integer('capacity_min')->default(1);
            $table->integer('capacity_max')->default(99);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['restaurant_id', 'type']);
            $table->index(['restaurant_id', 'active', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
