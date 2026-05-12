<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_cutoffs', function (Blueprint $table) {
            $table->id();
            $table->string('cutoff_name')->nullable();
            $table->date('date_from');
            $table->date('date_to');
            $table->enum('status', ['open', 'finalized'])->default('open');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_cutoffs');
    }
};
