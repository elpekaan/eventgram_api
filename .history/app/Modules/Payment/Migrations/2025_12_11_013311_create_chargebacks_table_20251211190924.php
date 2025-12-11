<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chargebacks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_transaction_id')->constrained();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('user_id')->constrained();

            $table->decimal('amount', 10, 2);
            $table->string('reason');
            $table->string('reason_code')->nullable();
            $table->string('case_id')->nullable(); // Provider's case ID
            $table->timestamp('chargeback_date');

            $table->enum('status', [
                'received',
                'under_review',
                'evidence_submitted',
                'won',
                'lost',
                'accepted'
            ])->default('received');

            // Dispute
            $table->json('dispute_evidence')->nullable();
            $table->timestamp('dispute_submitted_at')->nullable();
            $table->text('dispute_notes')->nullable();

            // Resolution
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolution')->nullable(); // 'won', 'lost'
            $table->text('resolution_notes')->nullable();

            $table->timestamps();

            $table->index(['status', 'chargeback_date']);
            $table->index('case_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chargebacks');
    }
};
