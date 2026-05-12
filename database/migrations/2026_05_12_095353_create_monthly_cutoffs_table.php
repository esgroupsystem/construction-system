<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->string('cutoff_name')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->enum('status', ['open', 'finalized'])->default('open');
            $table->timestamps();

            $table->index(['date_from', 'date_to']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_cutoffs');
    }
};
