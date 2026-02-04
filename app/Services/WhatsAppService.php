<?php

namespace App\Services;

use App\Models\WaOutbox;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppService
{
    protected ?string $nodeUrl;
    protected ?string $token;
    protected string $defaultCountry;
    protected string $senderName;

    public function __construct()
    {
        $this->nodeUrl = config('services.whatsapp.node_url') 
            ? rtrim(config('services.whatsapp.node_url'), '/') 
            : null;
        $this->token = config('services.whatsapp.token');
        $this->defaultCountry = config('services.whatsapp.default_country') ?? '62';
        $this->senderName = config('services.whatsapp.sender_name') ?? 'SITUBA';
    }

    /**
     * Normalize Indonesian phone number
     */
    public function normalizePhone(string $phone): string
    {
        // Remove all non-digit characters
        $normalized = preg_replace('/\D/', '', $phone);

        // Handle Indonesian formats
        if (str_starts_with($normalized, '0')) {
            $normalized = $this->defaultCountry . substr($normalized, 1);
        } elseif (str_starts_with($normalized, '8')) {
            $normalized = $this->defaultCountry . $normalized;
        }

        return $normalized;
    }

    /**
     * Queue a WhatsApp message
     */
    public function queueMessage(
        string $to,
        string $message,
        string $type = 'notif',
        int $delaySeconds = 0,
        array $meta = []
    ): WaOutbox {
        $normalizedPhone = $this->normalizePhone($to);
        
        $scheduledAt = $delaySeconds > 0 
            ? now()->addSeconds($delaySeconds)
            : now();

        return WaOutbox::create([
            'type' => $type,
            'to_phone' => $normalizedPhone,
            'message' => $message,
            'status' => 'pending',
            'scheduled_at' => $scheduledAt,
            'meta' => $meta,
        ]);
    }

    /**
     * Send message immediately via Node service
     */
    public function sendNowViaNode(WaOutbox $msg): array
    {
        // Check if service is configured
        if (!$this->nodeUrl || !$this->token) {
            $error = 'WhatsApp service not configured. Please set WA_NODE_URL and WA_NODE_TOKEN in .env';
            Log::channel('whatsapp')->error($error);
            $msg->markAsFailed($error);
            return [
                'success' => false,
                'error' => $error,
            ];
        }

        try {
            Log::channel('whatsapp')->info('Attempting to send message', [
                'outbox_id' => $msg->id,
                'to' => $this->maskPhone($msg->to_phone),
                'type' => $msg->type,
                'attempt' => $msg->attempts + 1,
            ]);

            $response = Http::timeout(10)
                ->withToken($this->token)
                ->post("{$this->nodeUrl}/send", [
                    'to' => $msg->to_phone,
                    'message' => $msg->message,
                    'clientRef' => "outbox_{$msg->id}",
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['ok'] ?? false) {
                    $msg->markAsSent($data['messageId'] ?? null);
                    
                    Log::channel('whatsapp')->info('Message sent successfully', [
                        'outbox_id' => $msg->id,
                        'message_id' => $data['messageId'] ?? null,
                    ]);

                    return [
                        'success' => true,
                        'message_id' => $data['messageId'] ?? null,
                    ];
                } else {
                    $error = $data['error'] ?? 'Unknown error from Node service';
                    $msg->markAsFailed($error);
                    
                    Log::channel('whatsapp')->error('Node service returned error', [
                        'outbox_id' => $msg->id,
                        'error' => $error,
                    ]);

                    return [
                        'success' => false,
                        'error' => $error,
                    ];
                }
            } else {
                $error = "HTTP {$response->status()}: {$response->body()}";
                $msg->markAsFailed($error);
                
                Log::channel('whatsapp')->error('HTTP request failed', [
                    'outbox_id' => $msg->id,
                    'status' => $response->status(),
                    'error' => substr($error, 0, 200),
                ]);

                return [
                    'success' => false,
                    'error' => $error,
                ];
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            $msg->markAsFailed($error);
            
            Log::channel('whatsapp')->error('Exception during send', [
                'outbox_id' => $msg->id,
                'error' => $error,
                'exception' => get_class($e),
            ]);

            return [
                'success' => false,
                'error' => $error,
            ];
        }
    }

    /**
     * Send message immediately (shortcut method)
     */
    public function sendNow(string $to, string $message, string $type = 'notif', array $meta = []): array
    {
        $outbox = $this->queueMessage($to, $message, $type, 0, $meta);
        return $this->sendNowViaNode($outbox);
    }

    /**
     * Check Node service health
     */
    public function checkHealth(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->nodeUrl}/health");
            
            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status' => 'error',
                'error' => "HTTP {$response->status()}",
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Mask phone number for logging (privacy)
     */
    protected function maskPhone(string $phone): string
    {
        if (strlen($phone) < 8) {
            return '***';
        }

        return substr($phone, 0, 5) . '*****' . substr($phone, -3);
    }
}
