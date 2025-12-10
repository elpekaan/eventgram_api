<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->boolean('is_transferred')->default(false);
            $table->boolean('is_locked')->default(false); // Transfer sürecinde kilitli
            $table->string('locked_reason')->nullable();

            // Audit Trail (Kimden kime geçti?)
            $table->timestamp('transferred_at')->nullable();
            $table->unsignedBigInteger('transferred_from')->nullable();

            // QR Güvenliği
            $table->timestamp('qr_regenerated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'is_transferred',
                'is_locked',
                'locked_reason',
                'transferred_at',
                'transferred_from',
                'qr_regenerated_at'
            ]);
        });
    }
};
