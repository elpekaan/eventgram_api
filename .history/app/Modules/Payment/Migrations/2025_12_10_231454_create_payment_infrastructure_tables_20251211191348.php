<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. PAYMENT TRANSACTIONS (Ana Ödeme Kaydı)
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // Hangi işlem için?
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('transfer_id')->nullable()->constrained('ticket_transfers');
            $table->foreignId('user_id')->constrained('users'); // Ödeyen kişi

            // Ödeme Detayları
            $table->string('provider')->default('iyzico'); // iyzico, stripe, mock
            $table->string('transaction_id')->nullable()->index(); // İyzico'nun verdiği ID
            $table->string('idempotency_key')->unique(); // Çifte ödemeyi önlemek için

            // Tutar
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('TRY');

            // Durum
            $table->string('status'); // pending, success, failed, refunded
            $table->string('payment_status')->nullable(); // İyzico'dan gelen ham statü
            $table->integer('fraud_status')->default(0); // 1 ise şüpheli

            // Hata Detayları
            $table->string('error_code')->nullable();
            $table->string('error_message')->nullable();
            $table->string('error_group')->nullable();

            // Meta
            $table->text('raw_response')->nullable(); // İyzico cevabı (Debug için)
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->timestamps();
        });

        // 2. WEBHOOK LOGS
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')->nullable()->constrained('payment_transactions');

            $table->string('provider'); // iyzico
            $table->string('event'); // payment.success, payment.failure
            $table->text('payload'); // Gelen JSON
            $table->string('payload_hash', 32); // MD5
            $table->string('status'); // received, processed, failed
            $table->text('error_message')->nullable();
            $table->ipAddress('source_ip')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Performans için index
            $table->unique(['transaction_id', 'payload_hash']);
        });

        // 3. FOREIGN KEY CONSTRAINTS EKLE
        // NOT: Kolonlar zaten var (order & transfer migration'larında eklendi)
        // Şimdi sadece foreign key constraint ekleyeceğiz

        Schema::table('orders', function (Blueprint $table) {
            // Kolon zaten var, sadece foreign key ekle
            $table->foreign('payment_transaction_id')
                ->references('id')
                ->on('payment_transactions')
                ->nullOnDelete();
        });

        Schema::table('ticket_transfers', function (Blueprint $table) {
            // Kolon zaten var, sadece foreign key ekle
            $table->foreign('payment_transaction_id')
                ->references('id')
                ->on('payment_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_transfers', function (Blueprint $table) {
            $table->dropForeign(['payment_transaction_id']);
            // Kolonu DROP ETME! Transfer migration'ına ait
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['payment_transaction_id']);
            // Kolonu DROP ETME! Order migration'ına ait
        });

        Schema::dropIfExists('webhook_logs');
        Schema::dropIfExists('payment_transactions');
    }
};
