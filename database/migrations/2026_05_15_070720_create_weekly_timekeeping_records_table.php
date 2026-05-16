<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_timekeeping_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('weekly_cutoff_id')->constrained('weekly_cutoffs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();

            $table->date('attendance_date');

            $table->integer('computed_ot_minutes')->default(0);
            $table->integer('approved_ot_minutes')->default(0);

            $table->enum('ot_status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->boolean('is_finalized')->default(false);
            $table->timestamp('finalized_at')->nullable();

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->unique(
                ['weekly_cutoff_id', 'employee_id', 'attendance_date'],
                'weekly_timekeeping_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_timekeeping_records');
    }
};
