@extends('layouts.soft')

@section('subjudul', 'Detail data skrining')

@section('content')
     <div class="mb-6">
        <a href="{{ route('pemda.screenings') }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 no-underline">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>

    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-6 border-b border-gray-200/50">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Detail Skrining Pasien</h5>
                 <p class="text-sm text-gray-500 mb-0">Lihat detail skrining yang tercatat oleh kader.</p>
            </div>
             @php
                $suspectCount = collect($screening->answers ?? [])
                    ->filter(fn ($ans, $key) => str_starts_with((string) $key, 'gejala_') && $ans === 'ya')
                    ->count();
            @endphp
            <div class="flex items-center gap-3">
                 <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $suspectCount ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                    {{ $suspectCount ? 'Suspek TBC' : 'Non Suspek' }}
                </span>
                <span class="text-xs text-gray-500">
                    <i class="ri-time-line align-middle"></i> {{ $screening->created_at->format('d M Y H:i') }}
                </span>
            </div>
        </div>
        
        @php
            $formatNumber = function ($value) {
                if ($value === null || $value === '') {
                    return '-';
                }
                $number = is_numeric($value) ? (float) $value : $value;
                $formatted = is_numeric($number) ? number_format($number, 2, '.', '') : $number;
                return rtrim(rtrim($formatted, '0'), '.');
            };
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Identity Section --}}
            <div class="col-span-1 lg:col-span-2">
                <div class="flex items-center gap-2 mb-4">
                     <span class="w-8 h-8 rounded-full bg-[var(--color-glass-primary)] text-white flex items-center justify-center font-bold text-sm">A</span>
                    <h6 class="font-bold text-lg text-gray-800 mb-0">Identitas & Alamat</h6>
                </div>
                
                <div class="bg-white/40 rounded-xl border border-white/50 overflow-hidden">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            <tr class="hover:bg-white/30">
                                <td class="px-4 py-3 font-medium text-gray-600 w-1/3">WNI</td>
                                <td class="px-4 py-3 text-gray-800">{{ $screening->patient_is_wni ? 'Ya' : 'Tidak' }}</td>
                            </tr>
                            <tr class="hover:bg-white/30">
                                <td class="px-4 py-3 font-medium text-gray-600">Nama Peserta</td>
                                <td class="px-4 py-3 text-gray-800 font-bold">{{ $screening->patient_name ?? '-' }}</td>
                            </tr>
                            <tr class="hover:bg-white/30">
                                <td class="px-4 py-3 font-medium text-gray-600">NIK</td>
                                <td class="px-4 py-3 text-gray-800 font-mono">{{ $screening->patient_nik ?? '-' }}</td>
                            </tr>
                            <tr class="hover:bg-white/30">
                                <td class="px-4 py-3 font-medium text-gray-600">Jenis Kelamin</td>
                                <td class="px-4 py-3 text-gray-800">{{ $screening->patient_gender ?? '-' }}</td>
                            </tr>
                            <tr class="hover:bg-white/30">
                                <td class="px-4 py-3 font-medium text-gray-600">TTL</td>
                                <td class="px-4 py-3 text-gray-800">{{ $screening->patient_birth_place ?? '-' }}, {{ optional($screening->patient_birth_date)->locale('id')->translatedFormat('d F Y') }} ({{ $screening->patient_age ?? '-' }} tahun)</td>
                            </tr>
                            <tr class="hover:bg-white/30">
                                <td class="px-4 py-3 font-medium text-gray-600">Fisik</td>
                                <td class="px-4 py-3 text-gray-800">BB: {{ $formatNumber($screening->patient_weight) }} kg, TB: {{ $formatNumber($screening->patient_height) }} cm</td>
                            </tr>
                             <tr class="hover:bg-white/30">
                                <td class="px-4 py-3 font-medium text-gray-600">Kontak</td>
                                <td class="px-4 py-3 text-gray-800">{{ $screening->patient_phone ?? '-' }}</td>
                            </tr>
                             <tr class="hover:bg-white/30">
                                <td class="px-4 py-3 font-medium text-gray-600">Alamat Lengkap</td>
                                <td class="px-4 py-3 text-gray-800">
                                    {{ $screening->patient_address_domisili ?? '-' }} <br>
                                    <span class="text-xs text-gray-500">RT: {{ $screening->patient_address_rt ?? '-' }} / RW: {{ $screening->patient_address_rw ?? '-' }} - Kelurahan {{ $screening->patient_address_kelurahan ?? '-' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Risk Factors --}}
            <div>
                 <div class="flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm">B</span>
                    <h6 class="font-bold text-lg text-gray-800 mb-0">Faktor Risiko & Riwayat</h6>
                </div>
                <div class="bg-white/40 rounded-xl border border-white/50 p-4 space-y-3">
                    @foreach ($riskQuestions as $key => $question)
                        <div class="flex justify-between items-start gap-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-700">{{ $loop->iteration }}. {{ $question }}</span>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Symptoms --}}
            <div>
                 <div class="flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-yellow-500 text-white flex items-center justify-center font-bold text-sm">C</span>
                    <h6 class="font-bold text-lg text-gray-800 mb-0">Gejala TBC (Penentu Suspek)</h6>
                </div>
                 <div class="bg-white/40 rounded-xl border border-white/50 p-4 space-y-3">
                    @foreach ($symptomQuestions as $key => $question)
                         <div class="flex justify-between items-start gap-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                            <span class="text-sm text-gray-700">{{ $loop->iteration + count($riskQuestions) }}. {{ $question }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
