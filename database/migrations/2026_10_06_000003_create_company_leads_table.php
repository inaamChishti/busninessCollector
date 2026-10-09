<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_leads', function (Blueprint $table) {
            $table->id();
            $table->string('company_number', 20)->unique();   // CH stable identifier
            $table->string('company_name');
            $table->string('company_status', 50)->nullable();
            $table->string('company_type', 80)->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('locality', 150)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('sic_codes', 255)->nullable();
            $table->date('date_of_creation')->nullable();
            $table->string('category', 100)->nullable();      // friendly label
            $table->string('search_location', 150)->nullable();
            $table->string('website')->nullable();            // optional enrichment
            $table->string('email')->nullable();              // optional enrichment
            $table->string('source', 50)->default('companies_house');
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->index('company_status');
            $table->index('category');
            $table->index('postal_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_leads');
    }
};
