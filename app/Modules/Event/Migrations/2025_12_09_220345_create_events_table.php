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

            // Hangi Mekan? (Venue)
            $table->foreignId('venue_id')
                ->constrained('venues')
                ->cascadeOnDelete();

            // Temel Bilgiler
            $table->string('name');
            $table->string('slug')->unique(); // URL dostu
            $table->text('description');
            $table->string('poster_image')->nullable(); // S3 path

            // Tarih & Zaman
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();

            // Kategori & Durum
            $table->string('category')->default(EventCategory::CONCERT->value);
            $table->string('status')->default(EventStatus::DRAFT->value);

            // Performans için Indexler
            $table->index(['venue_id', 'status']); // Mekan sayfasında hızlı listeleme
            $table->index('start_time'); // Tarihe göre sıralama

            $table->timestamps();
            $table->softDeletes();
        });

        // 2. TICKET TYPES (Bilet Tipleri)
        Schema::create('event_ticket_types', function (Blueprint $table) {
            $table->id();

            // Hangi Event?
            $table->foreignId('event_id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->string('name'); // Örn: "VIP", "Sahne Önü", "Öğrenci"
            $table->decimal('price', 10, 2); // Para birimi (199.90)
            $table->unsignedInteger('capacity'); // Bu biletten kaç tane var?
            $table->unsignedInteger('sold_count')->default(0); // Satılan miktar (Performans cache)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ticket_types');
        Schema::dropIfExists('events');
    }
};
