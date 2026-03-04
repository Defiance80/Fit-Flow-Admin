<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The core relationship: which clients belong to which trainers
        Schema::create('trainer_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('facility_id')->nullable()->constrained('facilities')->nullOnDelete();
            $table->enum('status', ['pending', 'active', 'paused', 'cancelled'])->default('pending');
            $table->json('permissions')->nullable(); // what data trainer can see
            $table->text('notes')->nullable(); // trainer's private notes about client
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['trainer_id', 'client_id']); // one relationship per pair
            $table->index(['client_id', 'status']); // fast lookups for client's active trainers
            $table->index(['trainer_id', 'status']); // fast lookups for trainer's active clients
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trainer_clients');
    }
};
