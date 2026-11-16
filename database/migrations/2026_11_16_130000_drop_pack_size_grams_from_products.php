<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * All finished goods are stocked in kilograms — the pack concept is removed
 * entirely. The column is dropped; every product is a bulk kg item.
 * Note: rolling back past 2026_11_16_000000_track_finished_goods_stock_in_kg
 * is unsupported once this column is gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('pack_size_grams');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('pack_size_grams', 10, 2)->nullable()->after('is_nc');
        });
    }
};
