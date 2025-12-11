<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. EVENTS TABLE
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Ownership
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users');

            // Basic info
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description');

            // Date & Time
            $table->timestamp('date'); // Event start
            $table->timestamp('doors_open')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('timezone')->default('Europe/Istanbul');

            // Location (override venue if needed)
            $table->text('location_address')->nullable();
            $table->decimal('location_latitude', 10, 7)->nullable();
            $table->decimal('location_longitude', 10, 7)->nullable();

            // Media
            $table->string('poster_url')->nullable();
            $table->string('banner_url')->nullable();
            $table->json('gallery')->nullable();

            // Ticket sales
            $table->timestamp('sales_start')->nullable();
            $table->timestamp('sales_end')->nullable();
            $table->integer('max_tickets_per_order')->default(10);

            // Check-in configuration
            $table->integer('check_in_opens_hours')->default(2); // Opens 2h before
            $table->integer('late_entry_hours')->default(2); // Allow 2h late entry
            $table->boolean('allow_late_entry')->default(true);
            $table->enum('check_in_status', ['open', 'closed'])->default('open');

            // Status
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed', 'blocked'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users');
            $table->text('block_reason')->nullable();

            // Stats (denormalized)
            $table->integer('total_capacity')->default(0);
            $table->integer('tickets_sold')->default(0);
            $table->integer('checked_in_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('shares_count')->default(0);

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index(['status', 'date']);
            $table->index(['venue_id', 'status']);
            $table->index(['category_id', 'status', 'date']);
            $table->index('date');
            $table->index('published_at');
            $table->fullText(['name', 'description']);
        });

        // 2. TICKET TYPES
        Schema::create('event_ticket_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')->constrained()->onDelete('cascade');

            // Type info
            $table->string('name'); // 'VIP', 'Normal', 'Öğrenci'
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('service_fee', 10, 2)->default(0);

            // Capacity
            $table->integer('quantity'); // Total available
            $table->integer('sold')->default(0); // Already sold
            $table->integer('reserved')->default(0); // In checkout (temp)

            // Sales period
            $table->timestamp('sales_start')->nullable();
            $table->timestamp('sales_end')->nullable();

            // Limits
            $table->integer('min_per_order')->default(1);
            $table->integer('max_per_order')->nullable();

            // Visibility
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['event_id', 'is_visible', 'sort_order']);
            $table->index(['event_id', 'sold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ticket_types');
        Schema::dropIfExists('events');
    }
};
