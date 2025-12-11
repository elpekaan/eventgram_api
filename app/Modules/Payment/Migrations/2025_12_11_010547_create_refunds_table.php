<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('payment_transaction_id')->constrained('payment_transactions');
            $table->foreignId('user_id')->constrained('users'); // İade alan kişi

            // Parasal Detaylar
            $table->decimal('amount', 10, 2); // İade edilen tutar
            $table->decimal('processing_fee', 10, 2)->default(0); // Kesinti varsa

            // Durum ve Sebep
            $table->string('status'); // pending, processing, completed, failed
            $table->string('reason'); // user_request, event_cancelled, fraud
            $table->text('description')->nullable(); // Admin notu

            // İyzico Detayları
            $table->string('provider_refund_id')->nullable(); // İyzico'dan dönen ID

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
