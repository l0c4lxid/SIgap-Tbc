<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PatientScreening;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaContextController extends Controller
{
    public function stats(Request $request)
    {
        $provided = (string) ($request->header('X-WA-Context-Key') ?? '');
        $allowedSecrets = array_values(array_filter([
            (string) config('services.whatsapp.context_key', ''),
            (string) config('services.whatsapp.token', ''),
        ]));

        $authorized = $provided !== '' && collect($allowedSecrets)->contains(
            fn ($secret) => $secret !== '' && hash_equals($secret, $provided)
        );
        if (!$authorized) {
            return response()->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $usersByRole = User::query()
            ->select('role', DB::raw('COUNT(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $totalScreenings = (int) PatientScreening::query()->count();
        $screenings30Days = (int) PatientScreening::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        $distinctKelurahanScreening = (int) PatientScreening::query()
            ->whereNotNull('patient_address_kelurahan')
            ->where('patient_address_kelurahan', '!=', '')
            ->distinct('patient_address_kelurahan')
            ->count('patient_address_kelurahan');

        $topKelurahan = PatientScreening::query()
            ->whereNotNull('patient_address_kelurahan')
            ->where('patient_address_kelurahan', '!=', '')
            ->select('patient_address_kelurahan as kelurahan', DB::raw('COUNT(*) as total'))
            ->groupBy('patient_address_kelurahan')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'kelurahan' => (string) $row->kelurahan,
                'total' => (int) $row->total
            ])
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'generated_at' => now()->toISOString(),
            'facts' => [
                'usersByRole' => $usersByRole,
                'totalUsers' => array_sum($usersByRole),
                'totalKelurahan' => (int) ($usersByRole[UserRole::Kelurahan->value] ?? 0),
                'totalPuskesmas' => (int) ($usersByRole[UserRole::Puskesmas->value] ?? 0),
                'totalKader' => (int) ($usersByRole[UserRole::Kader->value] ?? 0),
                'totalPemda' => (int) ($usersByRole[UserRole::Pemda->value] ?? 0),
                'totalScreenings' => $totalScreenings,
                'screeningsLast30Days' => $screenings30Days,
                'distinctKelurahanWithScreening' => $distinctKelurahanScreening,
                'topKelurahanByScreening' => $topKelurahan,
            ]
        ]);
    }
}
