<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function pasien(Request $request)
    {
        return view('dashboards.pasien', ['user' => $request->user()]);
    }

    public function kader(Request $request)
    {
        return view('dashboards.kader', ['user' => $request->user()]);
    }

    public function puskesmas(Request $request)
    {
        return view('dashboards.puskesmas', ['user' => $request->user()]);
    }

    public function kelurahan(Request $request)
    {
        return view('dashboards.kelurahan', ['user' => $request->user()]);
    }

    public function pemda(Request $request)
    {
        return view('dashboards.pemda', ['user' => $request->user()]);
    }
}
