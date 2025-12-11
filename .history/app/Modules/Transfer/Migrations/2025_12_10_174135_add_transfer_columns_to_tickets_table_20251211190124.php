<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // is_transferred & transfer_completed zaten var (ana migration'da eklendi)

            // Sadece eksik kolonları ekle:
            $table->boolean('is_locked')->default(false)->after('transfer_completed'); // Transfer sürecinde kilitli
            $table->string('locked_reason')->nullable()->after('is_locked');

            // Audit Trail (Kimden kime geçti?)
            $table->timestamp('transferred_at')->nullable()->after('locked_reason');
            $table->unsignedBigInteger('transferred_from')->nullable()->after('transferred_at');

            // QR Güvenliği
            $table->timestamp('qr_regenerated_at')->nullable()->after('transferred_from');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'is_locked',
                'locked_reason',
                'transferred_at',
                'transferred_from',
                'qr_regenerated_at'
            ]);
            // is_transferred ve transfer_completed'i DROP ETME (ana migration'a ait)
        });
    }
};
