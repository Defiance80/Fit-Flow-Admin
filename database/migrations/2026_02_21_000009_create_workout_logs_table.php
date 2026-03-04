<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Client logs actual performance
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('workout_session_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->date('logged_date');
            $table->integer('duration_minutes')->nullable();
            $table->integer('calories_burned')->nullable();
            $table->integer('avg_heart_rate')->nullable();
            $table->integer('max_heart_rate')->nullable();
            $table->integer('overall_rpe')->nullable(); // 1-10
            $table->text('notes')->nullable();
            $table->enum('mood', ['great', 'good', 'okay', 'tired', 'exhausted'])->nullable();
            $table->timestamps();

            $table->index(['client_id', 'logged_date']);
        });

        // Individual exercise performance within a workout log
        Schema::create('exercise_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_log_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            $table->integer('set_number');
            $table->integer('reps_completed')->nullable();
            $table->decimal('weight_used', 7, 2)->nullable();
            $table->string('weight_unit', 5)->default('lbs'); // lbs or kg
            $table->integer('duration_seconds')->nullable(); // for timed exercises
            $table->decimal('distance', 7, 2)->nullable(); // for cardio
            $table->string('distance_unit', 5)->nullable(); // miles, km, meters
            $table->integer('rpe')->nullable();
            $table->boolean('completed')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['workout_log_id']);
            $table->index(['exercise_id']);
        });

        // Progress photos
        Schema::create('progress_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('photo_path');
            $table->enum('pose', ['front', 'side', 'back', 'other'])->default('front');
            $table->date('taken_date');
            $table->text('notes')->nullable();
            $table->boolean('shared_with_trainer')->default(false);
            $table->timestamps();

            $table->index(['client_id', 'taken_date']);
        });

        // Body measurements over time
        Schema::create('body_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->date('measured_date');
            $table->decimal('weight', 5, 1)->nullable();
            $table->string('weight_unit', 5)->default('lbs');
            $table->decimal('body_fat_pct', 4, 1)->nullable();
            $table->decimal('chest_cm', 5, 1)->nullable();
            $table->decimal('waist_cm', 5, 1)->nullable();
            $table->decimal('hips_cm', 5, 1)->nullable();
            $table->decimal('left_arm_cm', 5, 1)->nullable();
            $table->decimal('right_arm_cm', 5, 1)->nullable();
            $table->decimal('left_thigh_cm', 5, 1)->nullable();
            $table->decimal('right_thigh_cm', 5, 1)->nullable();
            $table->decimal('left_calf_cm', 5, 1)->nullable();
            $table->decimal('right_calf_cm', 5, 1)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'measured_date']);
        });

        // AI-generated insights
        Schema::create('insight_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->string('insight_type'); // recovery_recommendation, intensity_adjustment, nutrition_flag, progress_milestone
            $table->json('data_snapshot')->nullable(); // metrics used to generate insight
            $table->text('recommendation');
            $table->boolean('applied')->default(false); // did trainer act on it?
            $table->text('trainer_response')->nullable();
            $table->timestamps();

            $table->index(['trainer_id', 'created_at']);
            $table->index(['client_id', 'insight_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insight_logs');
        Schema::dropIfExists('body_measurements');
        Schema::dropIfExists('progress_photos');
        Schema::dropIfExists('exercise_logs');
        Schema::dropIfExists('workout_logs');
    }
};
