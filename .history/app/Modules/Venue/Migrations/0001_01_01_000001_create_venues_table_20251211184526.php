<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();

            // Owner
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Basic info
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description');

            // Contact
            $table->string('email');
            $table->string('phone');
            $table->string('website')->nullable();

            // Address
            $table->text('address');
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('TR');

            // Geo
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Legal
            $table->string('tax_office')->nullable();
            $table->string('tax_number')->nullable()->unique();
            $table->string('trade_registry_number')->nullable();

            // Media
            $table->string('logo_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->json('gallery')->nullable(); // Array of image URLs

            // Capacity
            $table->integer('capacity')->nullable();

            // Status & Verification
            $table->enum('status', ['pending', 'verified', 'rejected', 'suspended'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->text('approval_notes')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->foreignId('suspended_by')->nullable()->constrained('users');
            $table->text('suspension_reason')->nullable();

            // Commission
            $table->decimal('commission_rate', 5, 4)->nullable(); // 0.1500 = 15%

            // Social
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('facebook')->nullable();

            // Stats (denormalized for performance)
            $table->integer('total_events')->default(0);
            $table->integer('total_followers')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index(['status', 'created_at']);
            $table->index('tax_number');
            $table->index(['city', 'status']);
            $table->fullText(['name', 'description']); // MySQL 8.0+
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
