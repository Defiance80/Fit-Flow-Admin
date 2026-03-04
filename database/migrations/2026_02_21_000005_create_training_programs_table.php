<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Training Program = the old "Course"
        Schema::create('training_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('program_type', ['strength', 'cardio', 'flexibility', 'hybrid', 'sport_specific', 'rehabilitation', 'weight_loss', 'muscle_gain', 'general_fitness'])->default('general_fitness');
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced', 'all_levels'])->default('all_levels');
            $table->integer('duration_weeks')->nullable();
            $table->integer('sessions_per_week')->nullable();
            $table->string('thumbnail')->nullable();
            $table->boolean('is_template')->default(false); // reusable template vs assigned program
            $table->boolean('is_active')->default(true);
            $table->decimal('price', 8, 2)->nullable(); // if selling programs
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trainer_id', 'is_active']);
            $table->index(['facility_id']);
        });

        // Program phases/weeks
        Schema::create('program_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Week 1", "Phase 1: Foundation"
            $table->integer('sort_order')->default(0);
            $table->integer('duration_days')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Individual workout sessions within a phase
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_phase_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // "Day 1: Upper Body", "Chest & Triceps"
            $table->integer('sort_order')->default(0);
            $table->integer('estimated_duration_min')->nullable();
            $table->text('warmup_notes')->nullable();
            $table->text('cooldown_notes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Exercises within a workout session
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->integer('sets')->nullable();
            $table->string('reps')->nullable(); // "12" or "8-12" or "AMRAP"
            $table->string('weight')->nullable(); // "135lbs" or "70% 1RM" or "bodyweight"
            $table->integer('rest_seconds')->nullable();
            $table->string('tempo')->nullable(); // "3-1-2-0" (eccentric-pause-concentric-pause)
            $table->integer('rpe_target')->nullable(); // Rate of Perceived Exertion 1-10
            $table->text('notes')->nullable(); // "superset with next exercise"
            $table->timestamps();
        });

        // Assign programs to clients
        Schema::create('client_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['assigned', 'active', 'completed', 'paused', 'cancelled'])->default('assigned');
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['trainer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_programs');
        Schema::dropIfExists('workout_exercises');
        Schema::dropIfExists('workout_sessions');
        Schema::dropIfExists('program_phases');
        Schema::dropIfExists('training_programs');
    }
};
