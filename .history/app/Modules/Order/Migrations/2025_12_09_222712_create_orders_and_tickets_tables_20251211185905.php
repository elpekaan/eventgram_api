<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ORDERS TABLE
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Buyer
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Event
            $table->foreignId('event_id')->constrained()->onDelete('cascade');

            // Order details
            $table->string('order_number')->unique(); // ORD-20250112-XXXX

            // Pricing
            $table->decimal('subtotal', 10, 2); // Tickets total
            $table->decimal('service_fee', 10, 2)->default(0);
            $table->decimal('total', 10, 2); // Subtotal + fee

            // Discount (optional)
            $table->string('coupon_code')->nullable();
            $table->decimal('discount', 10, 2)->default(0);

            // Payment - ŞİMDİLİK FOREIGN KEY YOK
            $table->unsignedBigInteger('payment_transaction_id')->nullable();  // ✅ DÜZELTME

            // Status
            $table->enum('status', [
                'pending_payment',
                'processing',
                'completed',
                'cancelled',
                'refund_requested',
                'refunded',
                'chargeback',
                'failed'
            ])->default('pending_payment');

            // Timestamps (status changes)
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refund_requested_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('chargeback_at')->nullable();

            // Metadata
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Notifications
            $table->timestamp('ticket_email_sent_at')->nullable();
            $table->boolean('ticket_email_opened')->default(false);

            // Gamification
            $table->integer('points_earned')->default(0);

            // Expiry (for pending orders)
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('order_number');
            $table->index(['user_id', 'status']);
            $table->index(['event_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('payment_transaction_id');  // ✅ Index var ama foreign key yok
        });

        // 2. TICKETS TABLE (aynı kalıyor)
        Schema::create('tickets', function (Blueprint $table) {
            // ... (değişiklik yok)
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('orders');
    }
};
