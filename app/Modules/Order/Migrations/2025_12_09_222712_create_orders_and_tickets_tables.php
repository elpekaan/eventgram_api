<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Ticket\Enums\TicketStatus;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ORDERS (Siparişler)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Kim aldı?
            $table->foreignId('user_id')->constrained('users');

            // Hangi Etkinlik?
            $table->foreignId('event_id')->constrained('events');

            // Referans Kodu (Örn: ORD-2025-XYZ)
            $table->string('reference_code')->unique();

            // Finansal Bilgiler
            $table->decimal('total_amount', 10, 2); // Toplam Tutar
            $table->string('status')->default(OrderStatus::PENDING->value);

            // Zaman Aşımı (Locking için kritik)
            // Kullanıcı "Öde" dediğinde biletleri 10dk rezerve edeceğiz.
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });

        // 2. TICKETS (Tekil Biletler)
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users'); // Sahibi
            $table->foreignId('event_id')->constrained('events');

            // Hangi Bilet Tipi? (VIP, Normal)
            $table->foreignId('event_ticket_type_id')->constrained('event_ticket_types');

            // Benzersiz Kod (QR Kodun içindeki veri)
            $table->string('code')->unique();

            $table->string('status')->default(TicketStatus::ACTIVE->value);

            // Kapıda okutulma zamanı
            $table->timestamp('used_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('orders');
    }
};
