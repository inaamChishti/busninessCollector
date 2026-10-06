<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wikidata_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wikidata_business_id')
                  ->constrained('wikidata_businesses')
                  ->cascadeOnDelete();
            $table->string('email', 255);
            $table->string('source_url')->nullable();   // page URL where email was found
            $table->timestamps();

            $table->unique(['wikidata_business_id', 'email'], 'wd_email_unique');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wikidata_emails');
    }
};
