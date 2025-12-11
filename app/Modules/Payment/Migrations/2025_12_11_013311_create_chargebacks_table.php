<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chargebacks', function (Blueprint $table) {
            $table->id();

            // Hangi işlem itiraz edildi?
            $table->foreignId('payment_transaction_id')->constrained('payment_transactions');
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('user_id')->constrained('users'); // İtiraz eden

            $table->decimal('amount', 10, 2);
            $table->string('reason_code')->nullable(); // Banka hata kodu
            $table->text('reason_description')->nullable(); // "Kart sahibi işlemi reddetti"

            // Süreç durumu
            $table->string('status')->default('received'); // received, lost, won

            // Kanıtlar (Dokümanda "Evidence" olarak geçiyor)
            $table->json('evidence_snapshot')->nullable(); // O anki loglar, IP adresi vb.

            $table->timestamp('dispute_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chargebacks');
    }
};
