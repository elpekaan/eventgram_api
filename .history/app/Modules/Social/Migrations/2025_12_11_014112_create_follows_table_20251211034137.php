<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();

            // Takip Eden (Her zaman bir User)
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete();

            // Takip Edilen (User veya Venue olabilir)
            $table->morphs('followable'); // followable_id, followable_type

            $table->timestamps();

            // Aynı kişiyi 2 kere takip edemesin
            $table->unique(['follower_id', 'followable_id', 'followable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
