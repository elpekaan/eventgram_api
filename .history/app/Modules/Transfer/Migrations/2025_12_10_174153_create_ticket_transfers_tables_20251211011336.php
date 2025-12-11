<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Transfer\Enums\TransferStatus;

return new class extends Migration
{
    public function up(): void
    {
        // 1. TRANSFER İŞLEMLERİ
        Schema::create('ticket_transfers', function (Blueprint $table) {
            $table->id();

            // İlişkiler
            $table->foreignId('ticket_id')->constrained('tickets');
            $table->foreignId('from_user_id')->constrained('users');
            $table->foreignId('to_user_id')->constrained('users'); // Alıcı

            // Finansal
            $table->decimal('asking_price', 10, 2); // Satıcının istediği fiyat
            $table->decimal('platform_commission', 10, 2)->default(0); // Bizim payımız
            $table->decimal('seller_receives', 10, 2); // Satıcının cebine girecek net

            // Durum
            $table->string('status')->default(TransferStatus::PENDING_VENUE_APPROVAL->value);
            $table->timestamp('expires_at')->nullable(); // Zaman aşımı için

            // Onay/Red detayları
            $table->timestamp('venue_approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Tamamlanma
            $table->timestamp('completed_at')->nullable();

            // Constraint: Aynı bilet için aynı anda tek aktif transfer olabilir
            // (Bunu kod tarafında pessimistic lock ile de koruyacağız)

            $table->timestamps();
        });

        // 2. SATICI ÖDEMELERİ (PAYOUTS) - Manuel süreç için
        Schema::create('transfer_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('ticket_transfers');
            $table->foreignId('seller_id')->constrained('users');

            $table->decimal('amount', 10, 2);
            $table->string('iban')->nullable();
            $table->string('status')->default('pending'); // pending, processed

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_payouts');
        Schema::dropIfExists('ticket_transfers');
    }
};
