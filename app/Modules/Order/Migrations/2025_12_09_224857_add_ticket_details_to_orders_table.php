<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Order tablosuna eksik bilgileri ekliyoruz
            $table->foreignId('event_ticket_type_id')
                ->after('event_id')
                ->constrained('event_ticket_types');

            $table->integer('quantity')
                ->after('event_ticket_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['event_ticket_type_id']);
            $table->dropColumn(['event_ticket_type_id', 'quantity']);
        });
    }
};
