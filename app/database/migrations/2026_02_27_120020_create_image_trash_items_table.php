<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_trash_items', function (Blueprint $table) {
            $table->id();
            $table->string('token', 80)->unique();
            $table->string('disk', 40)->default('public');
            $table->string('original_path');
            $table->string('trash_path');
            $table->string('model_type', 150)->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('column_name', 80)->default('image_path');
            $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at');
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_trash_items');
    }
};
