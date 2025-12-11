<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Venue\Enums\VenueStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venues', function (Blueprint $table) {
            $table->id();

            // Mekan Sahibi (User tablosuna bağlı)
            // cascadeOnDelete: User silinirse mekanı da silinsin.
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique(); // URL için (babylon-istanbul)
            $table->text('description')->nullable();

            // Adres Bilgileri (Basit tutuyoruz MVP için)
            $table->string('city'); // İstanbul, Ankara...
            $table->text('address');

            // Kapasite & İletişim
            $table->unsignedInteger('capacity');
            $table->string('phone')->nullable();
            $table->string('website')->nullable();

            // Durum (Varsayılan: Pending)
            $table->string('status')->default(VenueStatus::PENDING->value);

            // System Fields
            $table->softDeletes(); // Silinirse hemen uçmasın (Güvenlik)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
