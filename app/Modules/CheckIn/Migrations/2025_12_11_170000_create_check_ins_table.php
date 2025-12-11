<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            
            // References
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained(); // Ticket owner
            $table->foreignId('checked_in_by')->constrained('users'); // Staff who scanned
            
            // Check-in details
            $table->timestamp('checked_in_at');
            
            // Device info
            $table->string('device_id')->nullable();
            $table->text('device_info')->nullable(); // User agent, app version
            
            // Location (geo-fencing)
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('location_verified')->default(false);
            
            // Validation
            $table->boolean('is_valid')->default(true);
            $table->enum('validation_status', [
                'valid',
                'duplicate',
                'invalid_ticket',
                'wrong_event',
                'outside_window',
                'outside_geofence'
            ])->default('valid');
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['event_id', 'checked_in_at']);
            $table->index(['ticket_id', 'is_valid']);
            $table->index(['user_id', 'event_id']);
            $table->index('checked_in_at');
            
            // Unique constraint: one valid check-in per ticket
            $table->unique(['ticket_id', 'is_valid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
