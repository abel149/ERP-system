<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_items', function (Blueprint $table) {
            $table->string('unit')->nullable();
        });
        foreach (DB::table('issue_items')->get() as $item) {
            DB::table('issue_items')->where('id', $item->id)->update([
                'unit' => DB::table('raw_materials')->where('id', $item->raw_material_id)->value('unit'),
            ]);
        }
        Schema::table('productions', function (Blueprint $table) {
            // Null means a legacy batch whose historical units were not recorded.
            $table->decimal('input_quantity', 14, 4)->nullable();
            $table->string('input_unit')->nullable();
            $table->decimal('stock_quantity', 14, 2)->nullable();
        });
        Schema::table('non_conformities', function (Blueprint $table) {
            $table->string('unit')->nullable();
            $table->decimal('cost_value', 14, 2)->default(0);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('quantity', 14, 2)->default(0)->change();
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity', 14, 2)->change();
            $table->decimal('quantity_after', 14, 2)->change();
        });
    }

    public function down(): void
    {
        Schema::table('issue_items', fn (Blueprint $table) => $table->dropColumn('unit'));
        Schema::table('productions', fn (Blueprint $table) => $table->dropColumn(['input_quantity', 'input_unit', 'stock_quantity']));
        Schema::table('non_conformities', fn (Blueprint $table) => $table->dropColumn(['unit', 'cost_value']));
        // Keep decimal stock precision: converting existing fractions to integers loses inventory.
    }
};
