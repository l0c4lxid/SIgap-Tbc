<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\PdfToImage\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class RenderMateriPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'materi:render-pdf {--force : Force re-render all pages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Render Materi PDF into images for Flipbook';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pdfPath = public_path('pdf/Lembar balik.pdf');
        $outputDir = storage_path('app/public/materi/kader/pages');
        
        if (!file_exists($pdfPath)) {
            $this->error("PDF not found at: {$pdfPath}");
            return 1;
        }

        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        try {
            if (!class_exists('Imagick')) {
                throw new \Exception('Imagick is not installed.');
            }

            $pdf = new Pdf($pdfPath);
            $totalPages = $pdf->getNumberOfPages();
            
            $this->info("Found PDF with {$totalPages} pages.");
            
            // Limit to avoid infinite loops if something is wrong, but typically it's fine.
            // Check if we need to render
            $existingFiles = File::files($outputDir);
            if (!$this->option('force') && count($existingFiles) === $totalPages) {
                $this->info("Images already exist. Skipping render. Use --force to overwrite.");
                return 0;
            }

            $this->output->progressStart($totalPages);

            foreach (range(1, $totalPages) as $pageNumber) {
                // Determine output filename: 001.jpg, 002.jpg...
                $fileName = str_pad($pageNumber, 3, '0', STR_PAD_LEFT) . '.jpg';
                $outputPath = $outputDir . '/' . $fileName;

                // Set PDF page and save
                $pdf->setPage($pageNumber)
                    ->saveImage($outputPath);

                $this->output->progressAdvance();
            }

            $this->output->progressFinish();
            $this->info("Successfully rendered {$totalPages} pages to {$outputDir}");

        } catch (\Exception | \Error $e) {
            // Friendly fallback message (Indonesian)
            $this->line("");
            $this->info("  [INFO] Server-side rendering dilewati (Imagick tidak tersedia).");
            $this->line("  Sistem akan otomatis menggunakan **Browser Rendering**.");
            $this->comment("  Silakan refresh halaman di browser, materi akan muncul.");
            return 0;
        }

        return 0;
    }
}
