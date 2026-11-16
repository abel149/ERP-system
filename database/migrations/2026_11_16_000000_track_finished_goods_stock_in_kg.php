<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Finished goods are now always stocked in kilograms. Products that were
 * created with a pack size used to count their stock in packs, so their
 * on-hand quantity, unit cost, stock movements and posted production figures
 * are converted back to kg here (kg = packs x pack size / 1000).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->convert(true);
    }

    public function down(): void
    {
        $this->convert(false);
    }

    private function convert(bool $toKg): void
    {
        $products = DB::table('products')
            ->whereNotNull('pack_size_grams')
            ->where('pack_size_grams', '>', 0)
            ->get();

        foreach ($products as $product) {
            $kgPerPack = (float) $product->pack_size_grams / 1000;
            if ($kgPerPack <= 0) {
                continue;
            }

            $quantityFactor = $toKg ? $kgPerPack : 1 / $kgPerPack;
            $costFactor = $toKg ? 1 / $kgPerPack : $kgPerPack;

            foreach (DB::table('stock_movements')->where('product_id', $product->id)->get() as $movement) {
                DB::table('stock_movements')->where('id', $movement->id)->update([
                    'quantity' => round((float) $movement->quantity * $quantityFactor, 2),
                    'quantity_after' => round((float) $movement->quantity_after * $quantityFactor, 2),
                ]);
            }

            foreach (DB::table('productions')->where('product_id', $product->id)->get() as $production) {
                DB::table('productions')->where('id', $production->id)->update([
                    'stock_quantity' => $production->stock_quantity === null
                        ? null : round((float) $production->stock_quantity * $quantityFactor, 2),
                    'rework_stock_quantity' => $production->rework_stock_quantity === null
                        ? null : round((float) $production->rework_stock_quantity * $quantityFactor, 2),
                    'cost_per_unit' => $production->cost_per_unit === null
                        ? null : round((float) $production->cost_per_unit * $costFactor, 4),
                    'rework_cost_per_unit' => $production->rework_cost_per_unit === null
                        ? null : round((float) $production->rework_cost_per_unit * $costFactor, 4),
                ]);
            }

            DB::table('products')->where('id', $product->id)->update([
                'quantity' => round((float) $product->quantity * $quantityFactor, 2),
                'cost_price' => round((float) $product->cost_price * $costFactor, 2),
            ]);
        }
    }
};