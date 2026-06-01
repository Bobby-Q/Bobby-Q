<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('module')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('organization_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedSmallInteger('depth')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('organization_levels')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('organization_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_level_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('service_radius_km', 8, 2)->nullable();
            $table->decimal('disbursement_limit', 18, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_unit_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->boolean('can_initiate')->default(false)->after('password');
            $table->boolean('can_authorize')->default(false)->after('can_initiate');
            $table->boolean('can_validate')->default(false)->after('can_authorize');
            $table->boolean('is_locked')->default(false)->after('can_validate');
            $table->timestamp('last_login_at')->nullable()->after('is_locked');
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organization_unit_id');
            $table->dropColumn([
                'can_initiate',
                'can_authorize',
                'can_validate',
                'is_locked',
                'last_login_at',
            ]);
        });

        Schema::dropIfExists('organization_units');
        Schema::dropIfExists('organization_levels');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
