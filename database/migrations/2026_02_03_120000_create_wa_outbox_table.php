<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wa_outbox', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['notif', 'otp'])->default('notif');
            $table->string('to_phone', 20)->index();
            $table->text('message');
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->string('provider_message_id', 100)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            
            // Composite index for dispatcher query
            $table->index(['status', 'scheduled_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_outbox');
    }
};
