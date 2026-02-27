<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dish_templates', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('tags');
        });

        Schema::table('drink_templates', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('tags');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('tags');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('drink_templates', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        Schema::table('dish_templates', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};

