<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            // References
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('payment_transaction_id')->constrained();

            // Amounts
            $table->decimal('requested_amount', 10, 2);
            $table->decimal('processing_fee', 10, 2)->default(0);
            $table->decimal('refund_amount', 10, 2); // Final amount

            // Reason & Status
            $table->string('reason'); // 'event_cancelled', 'user_initiated'
            $table->text('reason_description')->nullable();
            $table->enum('status', [
                'pending',
                'approved',
                'processing',
                'completed',
                'failed',
                'rejected'
            ])->default('pending');

            // Approval tracking
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('approval_notes')->nullable();

            // Rejection tracking
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();

            // Completion tracking
            $table->timestamp('completed_at')->nullable();
            $table->string('provider_refund_id')->nullable();

            // Failure tracking
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['order_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['status', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
