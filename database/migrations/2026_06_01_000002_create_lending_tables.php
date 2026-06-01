<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrowers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('account_number')->unique();
            $table->string('first_name');
            $table->string('other_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 32)->nullable();
            $table->string('national_id_number')->nullable()->unique();
            $table->string('phone_number')->nullable()->index();
            $table->string('email')->nullable()->index();
            $table->string('postal_address')->nullable();
            $table->string('physical_address')->nullable();
            $table->unsignedSmallInteger('credit_score')->nullable();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('borrower_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('disk')->default('private');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->string('verification_status')->default('pending')->index();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('loan_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('principal_type')->default('range');
            $table->decimal('fixed_principal', 18, 2)->nullable();
            $table->decimal('min_principal', 18, 2)->nullable();
            $table->decimal('max_principal', 18, 2)->nullable();
            $table->string('interest_method')->default('flat');
            $table->decimal('interest_rate', 8, 4);
            $table->string('interest_period')->default('month');
            $table->unsignedInteger('repayment_period')->default(1);
            $table->string('repayment_period_type')->default('month');
            $table->boolean('allows_early_settlement')->default(false);
            $table->decimal('early_settlement_rate', 8, 4)->nullable();
            $table->boolean('rollover_penalty_enabled')->default(false);
            $table->string('rollover_application')->nullable();
            $table->string('status')->default('active')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_attachment_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('allowed_file_types')->nullable();
            $table->boolean('allows_multiple')->default(false);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('loan_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('borrower_id')->constrained()->restrictOnDelete();
            $table->foreignId('loan_product_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 18, 2);
            $table->date('borrow_date');
            $table->string('status')->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('loan_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('borrower_id')->constrained()->restrictOnDelete();
            $table->foreignId('loan_product_id')->constrained()->restrictOnDelete();
            $table->foreignId('organization_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('principal_amount', 18, 2);
            $table->decimal('interest_amount', 18, 2)->default(0);
            $table->decimal('fees_amount', 18, 2)->default(0);
            $table->decimal('outstanding_balance', 18, 2)->default(0);
            $table->date('disbursement_date')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('status')->default('approved')->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('loan_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->string('stage');
            $table->string('action');
            $table->text('comments')->nullable();
            $table->string('otp_challenge_reference')->nullable();
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->timestamp('acted_at');
            $table->timestamps();
        });

        Schema::create('repayment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('installment_number');
            $table->date('due_date');
            $table->decimal('principal_due', 18, 2)->default(0);
            $table->decimal('interest_due', 18, 2)->default(0);
            $table->decimal('fees_due', 18, 2)->default(0);
            $table->decimal('penalty_due', 18, 2)->default(0);
            $table->decimal('amount_paid', 18, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamps();

            $table->unique(['loan_id', 'installment_number']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('borrower_id')->constrained()->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 18, 2);
            $table->string('method')->default('cash');
            $table->timestamp('paid_at');
            $table->string('status')->default('posted')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('repayment_schedules');
        Schema::dropIfExists('loan_approvals');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_applications');
        Schema::dropIfExists('product_attachment_requirements');
        Schema::dropIfExists('loan_products');
        Schema::dropIfExists('borrower_documents');
        Schema::dropIfExists('borrowers');
    }
};
