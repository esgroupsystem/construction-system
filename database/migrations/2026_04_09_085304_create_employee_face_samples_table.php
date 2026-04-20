<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_face_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->string('image_path');
            $table->boolean('is_primary')->default(false);

            $table->string('model_name')->default('insightface');
            $table->string('model_version')->nullable();

            $table->decimal('det_score', 8, 4)->nullable();
            $table->decimal('quality_score', 8, 4)->nullable();

            $table->decimal('yaw', 8, 4)->nullable();
            $table->decimal('pitch', 8, 4)->nullable();
            $table->decimal('roll', 8, 4)->nullable();

            $table->json('landmarks_json')->nullable();
            $table->json('embedding_json');

            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'is_primary']);
            $table->index(['employee_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_face_embeddings');
    }
};
