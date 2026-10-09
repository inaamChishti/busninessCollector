<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_places', function (Blueprint $table) {
            $table->id();

            // ── Core identifiers ──────────────────────────────────
            $table->string('place_id')->unique();          // Google unique place ID
            $table->string('name');                        // Business name
            $table->string('search_query')->nullable();    // What was searched
            $table->string('search_location')->nullable(); // Location used in search
            $table->string('search_type')->nullable();     // Type/category searched

            // ── Contact ───────────────────────────────────────────
            $table->string('formatted_phone_number', 30)->nullable();
            $table->string('international_phone_number', 30)->nullable();
            $table->string('website')->nullable();

            // ── Address ───────────────────────────────────────────
            $table->string('formatted_address')->nullable();
            $table->string('street_number', 20)->nullable();
            $table->string('route')->nullable();           // Street name
            $table->string('sublocality')->nullable();
            $table->string('locality')->nullable();        // City/town
            $table->string('administrative_area_level_1')->nullable(); // County/state
            $table->string('administrative_area_level_2')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('postal_town')->nullable();

            // ── Location coordinates ──────────────────────────────
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->string('plus_code', 50)->nullable();
            $table->string('compound_code')->nullable();

            // ── Business details ──────────────────────────────────
            $table->json('types')->nullable();             // e.g. ["restaurant","food"]
            $table->string('primary_type', 100)->nullable();
            $table->string('business_status', 50)->nullable(); // OPERATIONAL/CLOSED_PERMANENTLY etc.
            $table->string('price_level')->nullable();     // 0-4
            $table->decimal('rating', 3, 1)->nullable();   // 1.0 - 5.0
            $table->integer('user_ratings_total')->nullable();
            $table->boolean('permanently_closed')->default(false);

            // ── Hours ─────────────────────────────────────────────
            $table->boolean('open_now')->nullable();
            $table->json('opening_hours')->nullable();     // Full weekday_text array
            $table->json('current_opening_hours')->nullable();
            $table->json('secondary_opening_hours')->nullable();

            // ── Editorial / Content ───────────────────────────────
            $table->text('editorial_summary')->nullable();
            $table->string('icon')->nullable();
            $table->string('icon_background_color', 10)->nullable();
            $table->string('icon_mask_base_uri')->nullable();
            $table->json('photos')->nullable();            // photo references array
            $table->string('url')->nullable();             // Google Maps URL
            $table->string('vicinity')->nullable();        // Simplified address

            // ── Accessibility & Amenities ─────────────────────────
            $table->boolean('wheelchair_accessible_entrance')->nullable();
            $table->boolean('delivery')->nullable();
            $table->boolean('dine_in')->nullable();
            $table->boolean('takeout')->nullable();
            $table->boolean('reservable')->nullable();
            $table->boolean('serves_breakfast')->nullable();
            $table->boolean('serves_lunch')->nullable();
            $table->boolean('serves_dinner')->nullable();
            $table->boolean('serves_beer')->nullable();
            $table->boolean('serves_wine')->nullable();
            $table->boolean('serves_brunch')->nullable();
            $table->boolean('serves_vegetarian_food')->nullable();

            // ── Reviews ───────────────────────────────────────────
            $table->json('reviews')->nullable();           // Top 5 reviews from API

            // ── Metadata ─────────────────────────────────────────
            $table->string('utc_offset')->nullable();
            $table->integer('utc_offset_minutes')->nullable();
            $table->json('address_components')->nullable(); // Full raw components
            $table->json('plus_code_full')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->index('place_id');
            $table->index('locality');
            $table->index('postal_code');
            $table->index('business_status');
            $table->index('search_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_places');
    }
};
