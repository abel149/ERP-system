<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // --- 1. Add intended_product_id to issues ---
        Schema::table('issues', function (Blueprint $table) {
            $table->unsignedBigInteger('intended_product_id')->nullable()->after('description');
        });

        // --- 2. Create issue_items (multi-ingredient lines per issue) ---
        Schema::create('issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained('raw_materials')->restrictOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 12, 2)->default(0); // snapshot at issue time
            $table->timestamps();
        });

        // --- 3. Backfill issue_items from existing issues ---
        $issues = DB::table('issues')->whereNotNull('raw_material_id')->get();
        foreach ($issues as $issue) {
            $rm = DB::table('raw_materials')->where('id', $issue->raw_material_id)->first();
            DB::table('issue_items')->insert([
                'issue_id'        => $issue->id,
                'raw_material_id' => $issue->raw_material_id,
                'quantity'        => $issue->quantity,
                'unit_cost'       => $rm ? (float) $rm->cost_price : 0,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // --- 4. Enhance productions ---
        Schema::table('productions', function (Blueprint $table) {
            $table->string('batch_type')->default('normal')->after('status'); // normal | rework
            $table->unsignedBigInteger('parent_nc_id')->nullable()->after('batch_type');
            $table->decimal('material_cost', 12, 2)->default(0)->after('packing_cost');
            $table->decimal('cost_per_unit', 12, 4)->nullable()->after('material_cost');
        });

        // --- 5. Create non_conformities ---
        Schema::create('non_conformities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained('productions')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('status')->default('pending'); // pending | reworked | final
            $table->unsignedBigInteger('reworked_by_production_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable(); // set when registered as sellable
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // --- 6. Add is_nc and pack_size_grams to products ---
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_nc')->default(false)->after('is_active');
            $table->decimal('pack_size_grams', 10, 2)->nullable()->after('is_nc');
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn(['batch_type', 'parent_nc_id', 'material_cost', 'cost_per_unit']);
        });
        Schema::dropIfExists('non_conformities');
        Schema::dropIfExists('issue_items');
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('intended_product_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_nc', 'pack_size_grams']);
        });
    }
};
