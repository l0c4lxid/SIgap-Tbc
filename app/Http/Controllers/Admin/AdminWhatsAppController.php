<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaOutbox;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

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
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string|max:4096',
            'delay_minutes' => 'nullable|integer|min:0|max:1440',
            'send_now' => 'nullable|boolean',
        ]);

        $delaySeconds = $request->send_now 
            ? 0 
            : ($request->delay_minutes ?? 0) * 60;

        $outbox = $this->waService->queueMessage(
            $request->phone,
            $request->message,
            'notif',
            $delaySeconds,
            [
                'created_by' => auth()->id(),
                'source' => 'admin_panel',
            ]
        );

        return redirect()->route('pemda.whatsapp.show', $outbox)
            ->with('success', 'Pesan berhasil diantrikan. ID: ' . $outbox->id);
    }

    /**
     * Display the specified message
     */
    public function show(Request $request, WaOutbox $outbox)
    {
        if ($request->ajax()) {
            return view('admin.whatsapp.detail-partial', ['selectedMessage' => $outbox])->render();
        }

        $data = $this->getDashboardData($request);
        return view('admin.whatsapp.index', array_merge($data, ['selectedMessage' => $outbox]));
    }

    private function getDashboardData(Request $request)
    {
        $query = WaOutbox::query()->latest();

        // Calculate Stats (Current Month)
        $stats = [
            'total' => WaOutbox::whereMonth('created_at', now()->month)->count(),
            'sent' => WaOutbox::whereMonth('created_at', now()->month)->where('status', 'sent')->count(),
            'pending' => WaOutbox::where('status', 'pending')->count(), // All pending
            'failed' => WaOutbox::where('status', 'failed')->count(), // All failed
        ];

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by phone or message content
        if ($request->filled('search')) {
            $search = $request->search;
            $normalizedSearch = $this->waService->normalizePhone($search);
            
            $query->where(function($q) use ($search, $normalizedSearch) {
                $q->where('to_phone', 'like', "%{$normalizedSearch}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->paginate(20)->withQueryString();

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
}
