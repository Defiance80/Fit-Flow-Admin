<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->enum('category', [
                'strength', 'cardio', 'flexibility', 'balance',
                'plyometric', 'olympic', 'bodyweight', 'machine', 'other'
            ])->default('strength');
            $table->json('muscle_groups')->nullable(); // ["chest", "triceps", "shoulders"]
            $table->json('equipment')->nullable(); // ["barbell", "bench"]
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('intermediate');
            $table->string('video_url')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_global')->default(false); // true = available to all trainers
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // trainer who created it
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category', 'difficulty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
