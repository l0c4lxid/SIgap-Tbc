<?php

namespace App\Http\Controllers\Kader;

use App\Http\Controllers\Controller;
use App\Support\SigapMaterial;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        return view('kader.materi', [
            'downloads' => SigapMaterial::downloads(),
            'pages' => $this->scanPages(),
        ]);
    }

    public function publicIndex(Request $request)
    {
        return view('public.materi', [
            'pages' => $this->scanPages(),
        ]);
    }

    private function scanPages(): array
    {
        $materiDir = storage_path('app/public/materi/kader/pages');
        $pages = [];

        if (file_exists($materiDir)) {
            $files = scandir($materiDir);
            foreach ($files as $file) {
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $pages[] = asset('storage/materi/kader/pages/' . $file);
                }
            }
            sort($pages); 
        }

        return $pages;
    }
}
