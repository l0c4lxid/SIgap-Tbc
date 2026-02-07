<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\File;

class CachePdfContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cache-pdf-content';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse large PDF and cache content to text file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ini_set('memory_limit', '512M'); // Increase memory for this process
        
        $this->info('Parsing Lembar balik.pdf...');
        
        $path = public_path('pdf/Lembar balik.pdf');
        if (!file_exists($path)) {
            $this->error('File not found: ' . $path);
            return 1;
        }

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
            $text = preg_replace('/\s+/', ' ', $text); // Compress whitespace
            
            $outputPath = storage_path('app/knowledge/lembar_balik.txt');
            
            // Ensure directory exists
            if (!file_exists(dirname($outputPath))) {
                mkdir(dirname($outputPath), 0755, true);
            }
            
            File::put($outputPath, $text);
            
            $this->info('Successfully cached to: ' . $outputPath);
            $this->info('Length: ' . strlen($text) . ' chars');
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
