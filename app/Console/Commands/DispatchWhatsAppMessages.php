<?php

namespace App\Console\Commands;

use App\Models\WaOutbox;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DispatchWhatsAppMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:dispatch {--limit=50 : Maximum messages to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch pending WhatsApp messages via Node service';

    protected WhatsAppService $waService;

    /**
     * Create a new command instance.
     */
    public function __construct(WhatsAppService $waService)
    {
        parent::__construct();
        $this->waService = $waService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $limit = (int) $this->option('limit');
        $startTime = microtime(true);

        Log::channel('whatsapp')->info('Dispatcher started', ['limit' => $limit]);

        // Fetch messages ready for dispatch
        $messages = WaOutbox::forDispatch()->limit($limit)->get();

        if ($messages->isEmpty()) {
            Log::channel('whatsapp')->info('No messages to dispatch');
            $this->info('No messages to dispatch.');
            return self::SUCCESS;
        }

        $this->info("Found {$messages->count()} message(s) to dispatch...");

        $sent = 0;
        $failed = 0;
        $maxAttempts = 3;

        foreach ($messages as $message) {
            // Increment attempts first
            $message->incrementAttempts();

            $this->line("Processing #{$message->id} (attempt {$message->attempts}/{$maxAttempts})");

            // Send via Node service
            $result = $this->waService->sendNowViaNode($message);

            if ($result['success']) {
                $sent++;
                $this->info("  ✓ Sent successfully");
            } else {
                $failed++;
                $this->error("  ✗ Failed: {$result['error']}");

                // Mark as permanently failed if max attempts reached
                if ($message->attempts >= $maxAttempts) {
                    $message->update(['status' => 'failed']);
                    $this->warn("  ! Max attempts reached, marked as failed");
                }
            }

            // Small delay to avoid overwhelming the Node service
            usleep(100000); // 100ms
        }

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::channel('whatsapp')->info('Dispatcher finished', [
            'processed' => $messages->count(),
            'sent' => $sent,
            'failed' => $failed,
            'duration_ms' => $duration,
        ]);

        $this->newLine();
        $this->info("Dispatch completed in {$duration}ms");
        $this->info("Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
