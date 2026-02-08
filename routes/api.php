<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use App\Http\Controllers\Api\WaContextController;

Route::post('/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle']);
Route::get('/wa/context', [WaContextController::class, 'bundle'])->middleware('throttle:30,1');
Route::get('/wa/context/stats', [WaContextController::class, 'stats'])->middleware('throttle:30,1');
Route::get('/wa/context/knowledge', [WaContextController::class, 'knowledge'])->middleware('throttle:30,1');
Route::get('/wa/context/docs', [WaContextController::class, 'docs'])->middleware('throttle:30,1');
