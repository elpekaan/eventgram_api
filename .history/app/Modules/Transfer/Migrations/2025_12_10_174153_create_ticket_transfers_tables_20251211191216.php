<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TICKET TRANSFERS
        Schema::create('ticket_transfers', function (Blueprint $table) {
            $table->id();

            // References
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_user_id')->constrained('users');
            $table->foreignId('to_user_id')->nullable()->constrained('users'); // Nullable for listings

            // Pricing
            $table->decimal('asking_price', 10, 2);
            $table->decimal('platform_commission', 10, 2)->default(0);
            $table->decimal('seller_receives', 10, 2);

            // Status & Escrow
            $table->enum('status', [
                'pending_venue_approval',
                'listed',
                'pending_payment',
                'payment_received',
                'processing',
                'completed',
                'cancelled',
                'expired'
            ])->default('pending_venue_approval');

            $table->enum('escrow_status', [
                'none',
                'held',
                'released',
                'refunded'
            ])->default('none');

            // Venue approval
            $table->timestamp('venue_approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Payment tracking
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->index('payment_transaction_id');

            // Completion & Cancellation
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->text('cancellation_reason')->nullable();

            // Expiry
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['ticket_id', 'status']);
            $table->index(['from_user_id', 'status']);
            $table->index(['to_user_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        // 2. TRANSFER PAYOUTS
        Schema::create('transfer_payouts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transfer_id')->constrained('ticket_transfers');
            $table->foreignId('seller_id')->constrained('users');

            $table->decimal('amount', 10, 2);
            $table->string('iban')->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed'
            ])->default('pending');

            // Timestamps
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users');

            // Provider details
            $table->string('provider_payout_id')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['seller_id', 'status']);
            $table->index(['status', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_payouts');
        Schema::dropIfExists('ticket_transfers');
    }
};
