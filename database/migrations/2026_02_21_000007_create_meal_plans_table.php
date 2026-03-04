<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('goal', ['weight_loss', 'muscle_gain', 'maintenance', 'performance', 'general_health'])->default('general_health');
            $table->integer('daily_calories')->nullable();
            $table->integer('protein_g')->nullable();
            $table->integer('carbs_g')->nullable();
            $table->integer('fats_g')->nullable();
            $table->integer('fiber_g')->nullable();
            $table->json('dietary_restrictions')->nullable(); // ["gluten_free", "dairy_free", "vegan"]
            $table->json('allergies')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_template')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['client_id', 'is_active']);
            $table->index(['trainer_id']);
        });

        Schema::create('meal_plan_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_plan_id')->constrained()->cascadeOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->enum('day_type', ['training', 'rest', 'active_recovery'])->default('training');
            $table->integer('adjusted_calories')->nullable(); // may differ from plan default on rest days
            $table->timestamps();
        });

        Schema::create('meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meal_plan_day_id')->constrained()->cascadeOnDelete();
            $table->enum('meal_type', ['breakfast', 'morning_snack', 'lunch', 'afternoon_snack', 'dinner', 'evening_snack', 'pre_workout', 'post_workout']);
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('calories')->nullable();
            $table->integer('protein_g')->nullable();
            $table->integer('carbs_g')->nullable();
            $table->integer('fats_g')->nullable();
            $table->string('recipe_url')->nullable();
            $table->text('preparation_notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meals');
        Schema::dropIfExists('meal_plan_days');
        Schema::dropIfExists('meal_plans');
    }
};
