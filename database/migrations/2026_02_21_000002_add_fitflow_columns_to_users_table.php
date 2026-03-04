<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_role', ['admin', 'facility_owner', 'trainer', 'client'])->default('client')->after('type');
            $table->foreignId('facility_id')->nullable()->constrained('facilities')->nullOnDelete()->after('user_role');
            $table->boolean('is_independent')->default(false)->after('facility_id'); // trainer-only: true = solo, false = under facility
            $table->string('invite_code')->nullable()->unique()->after('is_independent');
            $table->text('bio')->nullable()->after('invite_code');
            $table->json('specializations')->nullable()->after('bio'); // trainer: ["strength", "yoga", "nutrition"]
            $table->json('certifications')->nullable()->after('specializations'); // trainer: ["NASM-CPT", "ACE"]
            // Client-specific
            $table->json('fitness_goals')->nullable()->after('certifications');
            $table->text('medical_notes')->nullable()->after('fitness_goals');
            $table->string('emergency_contact_name')->nullable()->after('medical_notes');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->date('date_of_birth')->nullable()->after('emergency_contact_phone');
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->after('date_of_birth');
            $table->decimal('height_cm', 5, 1)->nullable()->after('gender');
            $table->decimal('weight_kg', 5, 1)->nullable()->after('height_cm');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['facility_id']);
            $table->dropColumn([
                'user_role', 'facility_id', 'is_independent', 'invite_code', 'bio',
                'specializations', 'certifications', 'fitness_goals', 'medical_notes',
                'emergency_contact_name', 'emergency_contact_phone', 'date_of_birth',
                'gender', 'height_cm', 'weight_kg'
            ]);
        });
    }
};
