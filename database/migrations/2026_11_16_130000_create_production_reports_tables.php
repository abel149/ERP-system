<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('production_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->string('week_no', 20)->nullable();
            $table->string('coordinator_name', 200)->nullable();
            $table->string('coordinator_position', 200)->nullable();
            $table->string('prepared_by', 200)->nullable();
            $table->date('prepared_date')->nullable();
            $table->string('checked_by', 200)->nullable();
            $table->date('checked_date')->nullable();
            $table->unsignedInteger('total_workers')->default(0);
            $table->decimal('working_hours', 5, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('report_date');
        });

        Schema::create('production_report_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group_code', 20)->nullable();
            $table->string('employee_code', 50)->nullable();
            $table->string('employee_name', 200)->nullable();
            $table->foreignId('raw_material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_code', 100)->nullable();
            $table->string('material_name_am', 200)->nullable();
            $table->string('material_name_en', 200)->nullable();
            $table->string('work_type', 100)->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('impurity', 12, 2)->default(0);
            $table->decimal('remaining', 12, 2)->default(0);
            $table->decimal('working_hours', 6, 2)->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_report_lines');
        Schema::dropIfExists('production_reports');
    }
};
