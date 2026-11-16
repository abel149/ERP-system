<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            // Cost carried by the NC while it waits for rework (weight share of batch cost)
            $table->decimal('nc_value', 14, 2)->default(0);
            $table->decimal('rework_output', 14, 2)->nullable();
            $table->decimal('rework_nc', 14, 2)->nullable();
            $table->decimal('rework_packing_cost', 14, 2)->default(0);
            $table->decimal('rework_cost_per_unit', 14, 4)->nullable();
            $table->decimal('rework_stock_quantity', 14, 2)->nullable();
            $table->unsignedBigInteger('nc_product_id')->nullable();
            $table->text('rework_notes')->nullable();
            $table->timestamp('fg_posted_at')->nullable();
            $table->timestamp('rework_completed_at')->nullable();
        });

        // Status flow is now: recorded -> in_rework -> completed
        DB::table('productions')->where('status', 'in_progress')->update(['status' => 'recorded']);
    }

    public function down(): void
    {
        DB::table('productions')->whereIn('status', ['recorded', 'in_rework'])->update(['status' => 'in_progress']);
        Schema::table('productions', function (Blueprint $table) {
            $table->dropColumn([
                'nc_value', 'rework_output', 'rework_nc', 'rework_packing_cost', 'rework_cost_per_unit',
                'rework_stock_quantity', 'nc_product_id', 'rework_notes', 'fg_posted_at', 'rework_completed_at',
            ]);
        });
    }
};
