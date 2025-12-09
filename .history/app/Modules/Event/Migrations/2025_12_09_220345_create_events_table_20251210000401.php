<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Event\Enums\EventCategory;
use App\Modules\Event\Enums\EventStatus;

return new class extends Migration
{
    public function up(): void
    {
        // 1. EVENTS TABLOSU
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // Hangi Mekanda?
            $table->foreignId('venue_id')
                ->constrained('venues')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');

            // Görsel (MVP için opsiyonel değil ama nullable bırakalım şimdilik)
            $table->string('poster_image')->nullable();

            // Tarih ve Kategori
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->string('category')->default(EventCategory::CONCERT->value);
            $table->string('status')->default(EventStatus::DRAFT->value);

            // Arama/Filtreleme için indexler
            $table->index(['venue_id', 'status']);
            $table->index('start_time');

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. TICKET TYPES TABLOSU (Fiyatlandırma)
        Schema::create('event_ticket_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->string('name'); // Örn: VIP, General Admission
            $table->decimal('price', 10, 2); // 10.000.000,00
            $table->integer('capacity'); // Bu bilet tipinden kaç tane var?
            $table->integer('sold_count')->default(0); // Kaç tane satıldı? (Performans için burada tutuyoruz)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ticket_types');
        Schema::dropIfExists('events');
    }
};
