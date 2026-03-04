<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SaaS subscription tracking for trainers/facilities
        Schema::create('saas_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // trainer or facility owner
            $table->foreignId('facility_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('tier', ['free', 'pro', 'premium', 'enterprise'])->default('free');
            $table->enum('billing_cycle', ['monthly', 'annual'])->default('monthly');
            $table->decimal('price', 8, 2)->default(0);
            $table->decimal('setup_fee', 8, 2)->default(0);
            $table->boolean('setup_fee_paid')->default(false);
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->enum('status', ['trialing', 'active', 'past_due', 'cancelled', 'expired'])->default('trialing');
            $table->integer('max_clients')->default(5); // per tier limits
            $table->integer('max_trainers')->default(1);
            $table->integer('storage_mb')->default(500);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // Tenant branding config for white-label
        Schema::create('tenant_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('app_name')->default('Fit Flow');
            $table->string('domain')->nullable(); // custom domain for white-label
            $table->string('subdomain')->nullable(); // facility.fitflow.com
            $table->string('logo_url')->nullable();
            $table->string('favicon_url')->nullable();
            $table->string('primary_color')->default('#6B21A8');
            $table->string('secondary_color')->default('#D4AF37');
            $table->string('accent_color')->default('#FFD700');
            $table->json('theme_config')->nullable(); // fonts, additional colors
            $table->string('support_email')->nullable();
            $table->string('support_phone')->nullable();
            $table->text('custom_css')->nullable();
            $table->json('feature_flags')->nullable(); // enable/disable features per tenant
            $table->timestamps();

            $table->unique('facility_id');
            $table->unique('domain');
            $table->unique('subdomain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_configs');
        Schema::dropIfExists('saas_subscriptions');
    }
};
