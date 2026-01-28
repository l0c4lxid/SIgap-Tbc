@extends('layouts.soft')

@section('subjudul', 'Detail skrining puskesmas')

@section('content')
    <div class="glass-card p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-gray-200/50 pb-4 mb-4">
            <div>
                <h5 class="font-bold text-xl text-gray-800 mb-1">Detail Skrining Pasien</h5>
                <p class="text-sm text-gray-500 mb-0">Lihat detail skrining yang tercatat oleh kader.</p>
            </div>
            <a href="{{ route('puskesmas.screenings') }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 no-underline">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
        </div>

        @php
            $suspectCount = collect($screening->answers ?? [])
                ->filter(fn ($ans, $key) => str_starts_with((string) $key, 'gejala_') && $ans === 'ya')
                ->count();
            
            $formatNumber = function ($value) {
                if ($value === null || $value === '') {
                    return '-';
                }
                $number = is_numeric($value) ? (float) $value : $value;
                $formatted = is_numeric($number) ? number_format($number, 2, '.', '') : $number;
                return rtrim(rtrim($formatted, '0'), '.');
            };
        @endphp

        <div class="flex flex-wrap items-center gap-3 mb-6">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $suspectCount ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                {{ $suspectCount ? 'Suspek TBC' : 'Non Suspek' }}
            </span>
            <span class="text-sm text-gray-500 flex items-center gap-1">
                <i class="ri-calendar-line"></i> {{ $screening->created_at->format('d M Y H:i') }}
            </span>
            <span class="text-sm text-gray-500 flex items-center gap-1">
                <i class="ri-user-line"></i> Kader PJ:
                @if ($kader?->id)
                    <a href="{{ route('puskesmas.kaders.show', $kader) }}" class="text-[var(--color-glass-primary)] hover:underline font-semibold">
                        {{ $kader->name }}
                    </a>
                @else
                    -
                @endif
            </span>
        </div>

        <div class="grid grid-cols-1 gap-6">
            {{-- Identitas Section --}}
            <div class="bg-white/40 rounded-xl p-4 md:p-6 border border-white/50">
                <div class="flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-sm">A</span>
                    <span class="font-bold text-gray-800">Identitas & Alamat</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">WNI</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_is_wni ? 'Ya' : 'Tidak' }}</span>
                    </div>
                    <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Nama Peserta</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_name ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                         <span class="text-gray-500 md:w-40 shrink-0">NIK</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_nik ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Jenis Kelamin</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_gender ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Tempat Lahir</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_birth_place ?? '-' }}</span>
                    </div>
                     <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Tanggal Lahir</span>
                        <span class="font-semibold text-gray-800">{{ optional($screening->patient_birth_date)->locale('id')->translatedFormat('d F Y') }}</span>
                    </div>
                     <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Umur</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_age ?? '-' }} tahun</span>
                    </div>
                    <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Berat Badan</span>
                        <span class="font-semibold text-gray-800">{{ $formatNumber($screening->patient_weight) }} kg</span>
                    </div>
                     <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Tinggi Badan</span>
                        <span class="font-semibold text-gray-800">{{ $formatNumber($screening->patient_height) }} cm</span>
                    </div>
                     <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Nomor HP</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_phone ?? '-' }}</span>
                    </div>
                     <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Alamat (RT/RW)</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_address_rt ?? '-' }} / {{ $screening->patient_address_rw ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between md:justify-start gap-4 border-b border-gray-100 pb-2">
                        <span class="text-gray-500 md:w-40 shrink-0">Kelurahan</span>
                        <span class="font-semibold text-gray-800">{{ $screening->patient_address_kelurahan ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Faktor Risiko Section --}}
            <div class="bg-white/40 rounded-xl p-4 md:p-6 border border-white/50">
                 <div class="flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center font-bold text-sm">B</span>
                    <span class="font-bold text-gray-800">Faktor Risiko & Riwayat</span>
                </div>
                
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    @foreach ($riskQuestions as $key => $question)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100/50">
                            <span class="text-sm text-gray-600 pr-4">{{ $loop->iteration }}. {{ $question }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Gejala Section --}}
            <div class="bg-white/40 rounded-xl p-4 md:p-6 border border-white/50">
                 <div class="flex items-center gap-2 mb-4">
                    <span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center font-bold text-sm">C</span>
                    <span class="font-bold text-gray-800">Gejala TBC (Penentu Suspek)</span>
                </div>
                
                 <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2">
                    @foreach ($symptomQuestions as $key => $question)
                        <div class="flex justify-between items-center py-2 border-b border-gray-100/50">
                            <span class="text-sm text-gray-600 pr-4">{{ $loop->iteration + count($riskQuestions) }}. {{ $question }}</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ ($screening->answers[$key] ?? 'tidak') === 'ya' ? 'Ya' : 'Tidak' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
