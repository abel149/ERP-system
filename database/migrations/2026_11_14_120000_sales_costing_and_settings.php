<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 2)->default(0)->after('unit_price');
            $table->decimal('rent_cost', 12, 2)->default(0);
            $table->decimal('utility_cost', 12, 2)->default(0);
            $table->decimal('machine_cost', 12, 2)->default(0);
            $table->decimal('other_cost', 12, 2)->default(0);
            $table->decimal('margin_percent', 8, 2)->default(0);
            $table->decimal('vat_percent', 8, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
        });

        // Selling spices by weight needs fractional quantities (e.g. 0.5 kg).
        Schema::table('sale_items', fn (Blueprint $table) => $table->decimal('quantity', 12, 2)->change());
        Schema::table('products', fn (Blueprint $table) => $table->decimal('quantity', 12, 2)->change());
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->change();
            $table->decimal('quantity_after', 12, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn([
                'unit_cost', 'rent_cost', 'utility_cost', 'machine_cost',
                'other_cost', 'margin_percent', 'vat_percent', 'vat_amount',
            ]);
        });
        Schema::dropIfExists('settings');
    }
};
