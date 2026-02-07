<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\WaContextController;

Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);
Route::get('/wa/context/stats', [WaContextController::class, 'stats'])->middleware('throttle:30,1');
