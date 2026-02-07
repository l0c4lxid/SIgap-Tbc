<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaOutbox;
use App\Models\WaInbox;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AdminWhatsAppController extends Controller
{
    protected WhatsAppService $waService;

    public function __construct(WhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    /**
     * Display a listing of outbox messages
     */
    public function index(Request $request)
    {
        $data = $this->getDashboardData($request);
        
        // For AJAX requests, return the same view (JavaScript will parse and update specific sections)
        return view('admin.whatsapp.index', array_merge($data, ['selectedMessage' => null]));
    }

    /**
     * Show the form for creating a new message
     */
    public function create()
    {
        return view('admin.whatsapp.create');
    }

    /**
     * Store a newly created message in storage
     */
    public function send(Request $request)
    {
        // Handle both JSON (AJAX) and form requests
        $isJson = $request->expectsJson() || $request->isJson();
        
        $data = $isJson ? $request->json()->all() : $request->all();
        
        $validated = Validator::make($data, [
            'phone' => $isJson ? 'nullable|string' : 'required|string',
            'to_phone' => 'nullable|string',
            'message' => 'required|string|max:4096',
            'delay_minutes' => 'nullable|integer|min:0|max:1440',
            'send_now' => 'nullable|boolean',
            'instant_send' => 'nullable|boolean', // For chat: send immediately
            'type' => 'nullable|string|in:notif,otp',
        ])->validate();

        $phone = $validated['to_phone'] ?? $validated['phone'] ?? null;
        
        if (!$phone) {
            return $isJson 
                ? response()->json(['message' => 'Phone number required'], 400)
                : back()->withErrors(['phone' => 'Phone number required']);
        }

        if ($validated['instant_send'] ?? false) {
            try {
                $result = $this->waService->sendNow(
                    $phone,
                    $validated['message'],
                    $validated['type'] ?? 'notif',
                    [
                        'created_by' => auth()->id(),
                        'source' => 'admin_panel',
                    ]
                );

                if (!$result['success']) {
                    throw new \Exception($result['error'] ?? 'Unknown error');
                }

                if ($isJson) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Pesan berhasil dikirim',
                        'data' => $result
                    ]);
                }

                return redirect()->route('pemda.whatsapp.show', $phone)
                    ->with('success', 'Pesan berhasil dikirim');
            } catch (\Exception $e) {
                if ($isJson) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengirim pesan: ' . $e->getMessage()
                    ], 500);
                }

                return back()->withErrors(['error' => 'Gagal mengirim pesan: ' . $e->getMessage()]);
            }
        }

        // Otherwise, queue the message
        $delaySeconds = ($validated['send_now'] ?? false)
            ? 0 
            : ($validated['delay_minutes'] ?? 0) * 60;

        $outbox = $this->waService->queueMessage(
            $phone,
            $validated['message'],
            $validated['type'] ?? 'notif',
            $delaySeconds,
            [
                'created_by' => auth()->id(),
                'source' => 'admin_panel',
            ]
        );

        if ($isJson) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil diantrikan',
                'data' => [
                    'id' => $outbox->id,
                    'status' => $outbox->status,
                    'to_phone' => $outbox->to_phone,
                ]
            ]);
        }

        return redirect()->route('pemda.whatsapp.show', $phone)
            ->with('success', 'Pesan berhasil diantrikan. ID: ' . $outbox->id);
    }

    /**
     * Display the specified message
     */
    public function show(Request $request, $phone = null)
    {
        // If no phone provided, try to get from query or redirect
        if (!$phone) {
            $phone = $request->get('phone');
            if (!$phone) {
                return redirect()->route('pemda.whatsapp.index');
            }
        }
        
        // Normalize phone number
        $phone = $this->waService->normalizePhone($phone);
        
        // Fetch conversation (sent + received) for this phone number
        $sent = WaOutbox::where('to_phone', $phone)
            ->get()
            ->map(function($msg) {
                return (object)[
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'media_path' => null,
                    'media_type' => null,
                    'created_at' => $msg->created_at,
                    'status' => $msg->status,
                    'type' => $msg->type,
                    'source' => 'outbox'
                ];
            });
            
        $received = WaInbox::where('from_phone', $phone)
            ->get()
            ->map(function($msg) {
                return (object)[
                    'id' => $msg->id,
                    'wa_message_id' => $msg->wa_message_id,
                    'message' => $msg->message,
                    'media_path' => $msg->media_path,
                    'media_type' => $msg->media_type,
                    'created_at' => $msg->received_at ?? $msg->created_at,
                    'status' => 'received',
                    'type' => 'text',
                    'source' => 'inbox'
                ];
            });
            
        // Merge and sort chronologically (oldest first - like WhatsApp)
        $conversation = $sent->concat($received)
            ->sortBy('created_at')
            ->values();

        // Create a dummy selected message for compatibility
        $selectedMessage = (object)[
            'id' => $phone,
            'to_phone' => $phone,
            'phone' => $phone
        ];

        if ($request->ajax()) {
            return view('admin.whatsapp.detail-partial', [
                'selectedMessage' => $selectedMessage,
                'conversation' => $conversation,
                'phone' => $phone
            ])->render();
        }

        $data = $this->getDashboardData($request);
        return view('admin.whatsapp.index', array_merge($data, [
            'selectedMessage' => $selectedMessage,
            'conversation' => $conversation,
            'phone' => $phone
        ]));
    }

    /**
     * Proxy inbox media from Node service (requires auth token server-side)
     */
    public function inboxMedia(string $messageId)
    {
        $nodeUrl = config('services.whatsapp.node_url');
        $token = config('services.whatsapp.token');

        if (!$nodeUrl || !$token) {
            return response()->json([
                'ok' => false,
                'error' => 'WhatsApp service not configured'
            ], 500);
        }

        $message = WaInbox::where('wa_message_id', $messageId)->first();
        if (!$message || !$message->media_path) {
            return response()->json([
                'ok' => false,
                'error' => 'Media not found'
            ], 404);
        }

        $filename = $message->media_path;
        $url = rtrim($nodeUrl, '/') . "/api/media/{$filename}";
        
        $response = Http::withoutVerifying()
            ->timeout(15)
            ->withToken($token)
            ->get($url);

        if (!$response->successful()) {
            $contentType = $response->header('Content-Type') ?? 'application/json';
            return response($response->body(), $response->status())
                ->header('Content-Type', $contentType);
        }

        $contentType = $response->header('Content-Type')
            ?? $message->media_type
            ?? 'application/octet-stream';
        $filename = $message->media_path ?: $messageId;

        return response($response->body(), 200)
            ->header('Content-Type', $contentType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }

    private function getDashboardData(Request $request)
    {
        // Calculate Stats (Current Month)
        $stats = [
            'total' => WaOutbox::whereMonth('created_at', now()->month)->count(),
            'sent' => WaOutbox::whereMonth('created_at', now()->month)->where('status', 'sent')->count(),
            'pending' => WaOutbox::where('status', 'pending')->count(), // All pending
            'failed' => WaOutbox::where('status', 'failed')->count(), // All failed
        ];

        // Get all unique phone numbers from both inbox and outbox
        $outboxPhones = WaOutbox::select('to_phone as phone', \DB::raw('MAX(created_at) as last_activity'))
            ->groupBy('to_phone');
        
        $inboxPhones = WaInbox::select('from_phone as phone', \DB::raw('MAX(received_at) as last_activity'))
            ->groupBy('from_phone');

        // Combine and get latest activity per phone
        $allPhones = \DB::table(\DB::raw("({$outboxPhones->toSql()} UNION {$inboxPhones->toSql()}) as combined"))
            ->mergeBindings($outboxPhones->getQuery())
            ->mergeBindings($inboxPhones->getQuery())
            ->select('phone', \DB::raw('MAX(last_activity) as last_activity'))
            ->groupBy('phone')
            ->orderByDesc('last_activity');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $normalizedSearch = $this->waService->normalizePhone($search);
            $allPhones->where('phone', 'like', "%{$normalizedSearch}%");
        }

        // Paginate phone numbers
        $page = $request->get('page', 1);
        $perPage = 20;
        $phonesCollection = $allPhones->get();
        $total = $phonesCollection->count();
        $phones = $phonesCollection->forPage($page, $perPage);

        // For each phone, get the latest message (either sent or received)
        $messages = collect();
        foreach ($phones as $phoneData) {
            $phone = $phoneData->phone;
            
            // Get latest outbox message for this phone
            $latestOutbox = WaOutbox::where('to_phone', $phone)
                ->latest('created_at')
                ->first();
            
            // Get latest inbox message for this phone
            $latestInbox = WaInbox::where('from_phone', $phone)
                ->latest('received_at')
                ->first();

            // Determine which is more recent
            $latestMessage = null;
            if ($latestOutbox && $latestInbox) {
                $latestMessage = $latestOutbox->created_at > $latestInbox->received_at 
                    ? $latestOutbox 
                    : $latestInbox;
            } elseif ($latestOutbox) {
                $latestMessage = $latestOutbox;
            } else {
                $latestMessage = $latestInbox;
            }

            if ($latestMessage) {
                // Add metadata for display
                $latestMessage->phone = $phone;
                $latestMessage->is_inbox = $latestMessage instanceof \App\Models\WaInbox;
                $messages->push($latestMessage);
            }
        }

        // Create manual paginator
        $messages = new \Illuminate\Pagination\LengthAwarePaginator(
            $messages,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get Node service health
        $nodeHealth = $this->waService->checkHealth();

        return compact('messages', 'nodeHealth', 'stats');
    }

    /**
     * Retry a failed message
     */
    public function retry(WaOutbox $outbox)
    {
        if (!in_array($outbox->status, ['failed', 'pending'])) {
            return back()->with('error', 'Hanya pesan failed/pending yang bisa di-retry.');
        }

        if ($outbox->attempts >= 3) {
            $outbox->update(['attempts' => 0]);
        }

        $outbox->update([
            'status' => 'pending',
            'scheduled_at' => now(),
            'last_error' => null,
        ]);

        return back()->with('success', 'Pesan direset ke pending dan akan dikirim ulang.');
    }

    /**
     * Cancel a pending message
     */
    public function cancel(WaOutbox $outbox)
    {
        if ($outbox->status !== 'pending') {
            return back()->with('error', 'Hanya pesan pending yang bisa dibatalkan.');
        }

        $outbox->update(['status' => 'cancelled']);

        return redirect()->route('pemda.whatsapp.index')
            ->with('success', 'Pesan berhasil dibatalkan.');
    }

    /**
     * Remove the specified message from storage.
     */
    public function destroy(WaOutbox $outbox)
    {
        $outbox->delete();
        return redirect()->route('pemda.whatsapp.index')
            ->with('success', 'Pesan berhasil dihapus.');
    }

    /**
     * Remove the specified inbox message from storage.
     */
    public function destroyInbox(\App\Models\WaInbox $inbox)
    {
        $inbox->delete();
        return back()->with('success', 'Pesan masuk berhasil dihapus.');
    }
}
