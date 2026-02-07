<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppWebhookController;

Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);
