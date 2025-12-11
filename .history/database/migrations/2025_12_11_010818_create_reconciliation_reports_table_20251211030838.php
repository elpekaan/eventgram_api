<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconciliation_reports', function (Blueprint $table) {
            $table->id();

            $table->date('date'); // Hangi günün raporu?

            // Özet Rakamlar
            $table->integer('platform_count');
            $table->decimal('platform_total', 12, 2);

            $table->integer('provider_count');
            $table->decimal('provider_total', 12, 2);

            // Sonuç
            $table->string('status'); // matched, mismatch
            $table->decimal('difference', 12, 2)->default(0);

            // Detaylar (JSON olarak tutalım, hangi ID'ler tutmadı?)
            $table->json('discrepancies')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconciliation_reports');
    }
};
