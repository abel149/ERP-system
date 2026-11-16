<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Packaging types (5 kg bag, 50 kg sack…) price the batch's packing cost by
 * package count: cost = (output kg / capacity) x unit cost — the exact
 * fraction, not rounded-up bags. The resolved type/count/cost is snapshotted
 * on the production so history survives later price changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packaging_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('capacity_kg', 8, 2);
            $table->decimal('unit_cost', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('productions', function (Blueprint $table) {
            $table->foreignId('packaging_type_id')->nullable()->after('packing_cost')
                ->constrained('packaging_types')->nullOnDelete();
            $table->decimal('package_count', 10, 2)->nullable()->after('packaging_type_id');
            $table->foreignId('rework_packaging_type_id')->nullable()->after('rework_packing_cost')
                ->constrained('packaging_types')->nullOnDelete();
            $table->decimal('rework_package_count', 10, 2)->nullable()->after('rework_packaging_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packaging_type_id');
            $table->dropColumn('package_count');
            $table->dropConstrainedForeignId('rework_packaging_type_id');
            $table->dropColumn('rework_package_count');
        });
        Schema::dropIfExists('packaging_types');
    }
};
