<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wikidata_businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('website')->nullable();
            $table->string('phone', 60)->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('category', 100)->nullable();
            $table->timestamps();

            $table->index('city');
            $table->index('category');
            // Prevent exact duplicates for same name+website+city
            $table->unique(['name', 'website', 'city'], 'wd_biz_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wikidata_businesses');
    }
};
