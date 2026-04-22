<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('rate_salary', 12, 2)->nullable()->after('position');
            $table->string('location')->nullable()->after('rate_salary');
            $table->time('schedule_time_in')->nullable()->after('location');
            $table->time('schedule_time_out')->nullable()->after('schedule_time_in');
            $table->json('day_offs')->nullable()->after('schedule_time_out');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'rate_salary',
                'location',
                'schedule_time_in',
                'schedule_time_out',
                'day_offs',
            ]);
        });
    }
};
