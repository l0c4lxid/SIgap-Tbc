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
        ]);
    }
}
