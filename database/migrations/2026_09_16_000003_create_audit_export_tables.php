<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('action')->index(); $table->text('description'); $table->string('subject_type')->nullable(); $table->unsignedBigInteger('subject_id')->nullable(); $table->json('metadata')->nullable(); $table->ipAddress('ip_address')->nullable(); $table->timestamps(); $table->index(['subject_type', 'subject_id']); });
        Schema::create('exports', function (Blueprint $table) { $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $table->string('format', 8); $table->string('status')->default('pending')->index(); $table->json('filters')->nullable(); $table->string('filename')->nullable(); $table->unsignedBigInteger('row_count')->default(0); $table->text('error')->nullable(); $table->timestamp('completed_at')->nullable(); $table->timestamps(); });
    }
    public function down(): void { Schema::dropIfExists('exports'); Schema::dropIfExists('activity_logs'); }
};
