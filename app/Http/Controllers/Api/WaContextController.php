<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PatientScreening;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class WaContextController extends Controller
{
    private function buildStatsFacts(): array
    {
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

        return [
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
        ];
    }

    private function buildKnowledgePayload(): array
    {
        $knowledgePath = storage_path('app/knowledge/lembar_balik.txt');
        if (!File::exists($knowledgePath)) {
            return [
                'source' => 'storage/app/knowledge/lembar_balik.txt',
                'content' => '',
                'exists' => false,
            ];
        }

        $content = (string) File::get($knowledgePath);
        $content = preg_replace('/\s+/', ' ', $content) ?? '';
        $content = trim($content);
        $maxChars = 18000;
        if (mb_strlen($content) > $maxChars) {
            $content = mb_substr($content, 0, $maxChars) . '...';
        }

        return [
            'source' => 'storage/app/knowledge/lembar_balik.txt',
            'content' => $content,
            'exists' => true,
            'updated_at' => date(DATE_ATOM, File::lastModified($knowledgePath)),
        ];
    }

    private function buildDocsPayload(int $maxFiles = 160): array
    {
        $root = base_path();
        $maxFiles = max(20, min($maxFiles, 400));
        $targets = [
            'routes' => ['*.php'],
            'app/Http/Controllers' => ['*.php'],
            'resources/views' => ['*.blade.php'],
            'config' => ['*.php'],
        ];

        $docs = [];
        foreach ($targets as $dir => $patterns) {
            $fullDir = $root . DIRECTORY_SEPARATOR . $dir;
            if (!File::isDirectory($fullDir)) {
                continue;
            }

            $files = File::allFiles($fullDir);
            foreach ($files as $file) {
                if (count($docs) >= $maxFiles) {
                    break 2;
                }

                $relative = str_replace('\\', '/', $file->getRelativePathname());
                $relative = trim($dir . '/' . $relative, '/');
                $allowed = collect($patterns)->contains(function ($pattern) use ($relative) {
                    return fnmatch($pattern, basename($relative));
                });
                if (!$allowed) {
                    continue;
                }

                $category = str_starts_with($relative, 'routes/') ? 'routing'
                    : (str_starts_with($relative, 'app/Http/Controllers/') ? 'controller'
                        : (str_starts_with($relative, 'resources/views/') ? 'view' : 'config'));
                $docs[] = [
                    'source' => $relative,
                    'type' => 'code',
                    'category' => $category,
                    'summary' => "Dokumen aplikasi SITUBA kategori {$category}. Gunakan sebagai referensi perilaku fitur.",
                ];
            }
        }

        return [
            'root' => [
                'label' => basename($root),
                'is_accessible' => File::isDirectory($root),
            ],
            'docs' => $docs,
        ];
    }

    private function isAuthorized(Request $request): bool
    {
        $provided = (string) ($request->header('X-WA-Context-Key') ?? '');
        $allowedSecrets = array_values(array_filter([
            (string) config('services.whatsapp.context_key', ''),
            (string) config('services.whatsapp.token', ''),
        ]));

        return $provided !== '' && collect($allowedSecrets)->contains(
            fn ($secret) => $secret !== '' && hash_equals($secret, $provided)
        );
    }

    private function unauthorizedResponse()
    {
        return response()->json(['ok' => false, 'error' => 'Unauthorized'], 401);
    }

    public function stats(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse();
        }

        return response()->json([
            'ok' => true,
            'generated_at' => now()->toISOString(),
            'facts' => $this->buildStatsFacts(),
        ]);
    }

    public function knowledge(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse();
        }

        return response()->json([
            'ok' => true,
            'generated_at' => now()->toISOString(),
            'knowledge' => $this->buildKnowledgePayload(),
        ]);
    }

    public function docs(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse();
        }

        $payload = $this->buildDocsPayload((int) $request->integer('max_files', 160));

        return response()->json([
            'ok' => true,
            'generated_at' => now()->toISOString(),
            'root' => $payload['root'],
            'docs' => $payload['docs'],
        ]);
    }

    public function bundle(Request $request)
    {
        if (!$this->isAuthorized($request)) {
            return $this->unauthorizedResponse();
        }

        $docsPayload = $this->buildDocsPayload((int) $request->integer('max_files', 160));

        return response()->json([
            'ok' => true,
            'generated_at' => now()->toISOString(),
            'facts' => $this->buildStatsFacts(),
            'knowledge' => $this->buildKnowledgePayload(),
            'root' => $docsPayload['root'],
            'docs' => $docsPayload['docs'],
        ]);
    }
}
