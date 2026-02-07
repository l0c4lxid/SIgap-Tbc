<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WaInbox;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Handle incoming WhatsApp Webhook
     */
    public function handle(Request $request)
    {
        try {
            $payload = $request->all();

            // Log incoming webhook for debugging
            Log::info('WA Webhook received', ['payload' => $payload]);

            // Only process 'messages.upsert' event for now
            if (($payload['event'] ?? '') !== 'messages.upsert') {
                return response()->json(['status' => 'ignored', 'reason' => 'unsupported_event']);
            }

            $data = $payload['data'] ?? [];
            if (empty($data)) {
                return response()->json(['status' => 'ignored', 'reason' => 'empty_data']);
            }

            // Avoid duplicates
            if (WaInbox::where('wa_message_id', $data['id'])->exists()) {
                return response()->json(['status' => 'ignored', 'reason' => 'duplicate']);
            }

            // Extract phone from JID
            $jid = $data['from'] ?? '';
            $rawPhone = explode('@', $jid)[0];
            $jidType = explode('@', $jid)[1] ?? 'unknown';

            // Log for debugging
            Log::info('WA Webhook JID extraction', [
                'jid' => $jid,
                'jid_type' => $jidType,
                'raw_phone' => $rawPhone,
                'message_type' => $data['type'] ?? 'unknown'
            ]);

            // Handle different JID types
            if ($jidType === 'lid') {
                return response()->json(['status' => 'ignored', 'reason' => 'lid_message']);
            } else {
                $normalizedPhone = $this->waService->normalizePhone($rawPhone);
            }

            // Parse timestamp
            $timestamp = $data['timestamp'] ?? null;
            $receivedAt = now(); // Default

            if ($timestamp) {
                if (is_numeric($timestamp)) {
                    $ts = (int) $timestamp;
                    if ($ts > 10_000_000_000) {
                        $ts = (int) floor($ts / 1000);
                    }
                    $receivedAt = \Carbon\Carbon::createFromTimestamp($ts, 'Asia/Jakarta');
                }
            }

            // Save to database
            $inbox = WaInbox::create([
                'wa_message_id' => $data['id'],
                'from_phone' => $normalizedPhone,
                'message' => $data['text'] ?? '',
                'received_at' => $receivedAt,
                'is_group' => $data['isGroup'] ?? false,
                'media_path' => $data['media'] ? ($data['media']['file'] ?? null) : null,
                'media_type' => $data['media'] ? ($data['media']['mime'] ?? null) : null,
                'raw_data' => $data
            ]);

            Log::info('WA Webhook processed', ['from' => $normalizedPhone, 'msg_id' => $data['id']]);

            // AI reply is handled by external wa-service only.

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            Log::error('WA Webhook Error: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
