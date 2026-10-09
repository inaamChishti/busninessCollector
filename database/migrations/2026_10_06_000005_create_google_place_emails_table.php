<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_place_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('google_place_id')
                  ->constrained('google_places')
                  ->cascadeOnDelete();
            $table->string('email', 255);
            $table->string('source_url')->nullable();
            $table->timestamps();

            $table->unique(['google_place_id', 'email'], 'gpe_unique');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_place_emails');
    }
};
