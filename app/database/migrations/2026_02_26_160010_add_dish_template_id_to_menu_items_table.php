<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menu_items')) {
            return;
        }

        if (Schema::hasColumn('menu_items', 'dish_template_id')) {
            return;
        }

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('dish_template_id')
                ->nullable()
                ->after('restaurant_id')
                ->constrained('dish_templates')
                ->nullOnDelete();

            $table->unique(['restaurant_id', 'dish_template_id'], 'menu_items_restaurant_template_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('menu_items') || ! Schema::hasColumn('menu_items', 'dish_template_id')) {
            return;
        }

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique('menu_items_restaurant_template_unique');
            $table->dropConstrainedForeignId('dish_template_id');
        });
    }
};
