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

        if (Schema::hasColumn('menu_items', 'drink_template_id')) {
            return;
        }

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreignId('drink_template_id')
                ->nullable()
                ->after('dish_template_id')
                ->constrained('drink_templates')
                ->nullOnDelete();

            $table->unique(['restaurant_id', 'drink_template_id'], 'menu_items_restaurant_drink_template_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('menu_items') || ! Schema::hasColumn('menu_items', 'drink_template_id')) {
            return;
        }

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropUnique('menu_items_restaurant_drink_template_unique');
            $table->dropConstrainedForeignId('drink_template_id');
        });
    }
};

