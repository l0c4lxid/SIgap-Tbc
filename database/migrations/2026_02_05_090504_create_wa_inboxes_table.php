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
        Schema::create('wa_inbox', function (Blueprint $table) {
            $table->id();
            $table->string('wa_message_id')->unique()->comment('ID dari WhatsApp');
            $table->string('from_phone')->index();
            $table->string('push_name')->nullable();
            $table->text('message')->nullable();
            $table->string('media_path')->nullable();
            $table->string('media_type')->nullable(); // image, video, document
            $table->timestamp('received_at')->useCurrent();
            $table->boolean('is_group')->default(false);
            $table->json('raw_data')->nullable(); // Store full payload just in case
            $table->timestamps();

            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_inbox');
    }
};
