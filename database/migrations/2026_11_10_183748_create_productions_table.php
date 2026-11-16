<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('production_number')->unique();
            $table->string('status')->default('in_progress');
            // Manager enters ACTUAL measured values:
            $table->decimal('non_conforming', 12, 2)->default(0);  // waste found (measured)
            $table->decimal('output_quantity', 12, 2)->default(0); // clean output (computed: total_input - nc)
            // Computed from actual measurements (stored for reporting):
            $table->decimal('impurity', 5, 4)->default(0);  // nc / total_input
            $table->decimal('purity',   5, 4)->default(0);  // output / total_input
            $table->decimal('packing_cost', 12, 2)->default(0);
            $table->enum('pad_type', ['GRV','GRH','GRS'])->default('GRV');
            $table->string('pad_number');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Junction table: one production can consume multiple issues (ingredients/blend)
        Schema::create('production_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issue_id')->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_items');
        Schema::dropIfExists('productions');
    }
};
