<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no')->unique();
            $table->string('full_name');
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->string('photo_path')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamp('face_registered_at')->nullable();
            $table->unsignedInteger('face_samples_count')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'employee_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
