<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['session', 'consultation', 'assessment', 'group_class', 'blocked'])->default('session');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('location')->nullable(); // gym, online, client's home
            $table->boolean('is_virtual')->default(false);
            $table->string('meeting_url')->nullable(); // Zoom/Google Meet link
            $table->enum('status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->enum('recurrence', ['none', 'daily', 'weekly', 'biweekly', 'monthly'])->default('none');
            $table->string('recurrence_rule')->nullable(); // RRULE for complex patterns
            $table->foreignId('parent_schedule_id')->nullable()->constrained('schedules')->nullOnDelete(); // recurring parent
            $table->text('notes')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['trainer_id', 'start_at']);
            $table->index(['client_id', 'start_at']);
            $table->index(['facility_id', 'start_at']);
            $table->index(['status', 'start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
