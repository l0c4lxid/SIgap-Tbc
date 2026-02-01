@extends('layouts.soft')

@section('subjudul', 'Detail skrining kader')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@section('content')
     <div class="mb-6">
        <a href="{{ route('kader.screening.index') }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 no-underline">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>

    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-6 border-b border-gray-200/50">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Detail Skrining Pasien</h5>
                 <p class="text-sm text-gray-500 mb-0">Lihat detail skrining yang tercatat.</p>
            </div>
            <div class="flex items-center gap-2">
                 @if (!$isEdit)
                    <a href="{{ route('kader.screening.show', ['screening' => $screening, 'edit' => 1]) }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold no-underline text-blue-600 hover:text-blue-700 bg-white/50">
                        <i class="ri-edit-line me-1"></i> Edit
                    </a>
                @endif
                 <form method="POST" action="{{ route('kader.screening.destroy', $screening) }}" data-confirm="Hapus data skrining ini?" data-confirm-text="Hapus">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold text-red-600 hover:text-red-700 bg-white/50 border-red-200">
                        <i class="ri-delete-bin-line me-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        @if (!$isEdit)
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
            
             <div class="flex items-center gap-3 mb-8">
                 <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $suspectCount ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                    {{ $suspectCount ? 'Suspek TBC' : 'Non Suspek' }}
                </span>
                <span class="text-xs text-gray-500 flex items-center gap-1">
                    <i class="ri-time-line"></i> Diinput {{ $screening->created_at->format('d M Y H:i') }}
                </span>
            </div>

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
                                    <td class="px-4 py-3 text-gray-800">{{ $screening->patient_gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                                </tr>
                                <tr class="hover:bg-white/30">
                                    <td class="px-4 py-3 font-medium text-gray-600">Tempat, Tgl Lahir</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $screening->patient_birth_place ?? '-' }}, {{ optional($screening->patient_birth_date)->locale('id')->translatedFormat('d F Y') }}</td>
                                </tr>
                                 <tr class="hover:bg-white/30">
                                    <td class="px-4 py-3 font-medium text-gray-600">Umur</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $screening->patient_age ?? '-' }} tahun</td>
                                </tr>
                                 <tr class="hover:bg-white/30">
                                    <td class="px-4 py-3 font-medium text-gray-600">Fisik</td>
                                    <td class="px-4 py-3 text-gray-800">BB: {{ $formatNumber($screening->patient_weight) }} kg, TB: {{ $formatNumber($screening->patient_height) }} cm</td>
                                </tr>
                                <tr class="hover:bg-white/30">
                                    <td class="px-4 py-3 font-medium text-gray-600">Nomor HP</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $screening->patient_phone ?? '-' }}</td>
                                </tr>
                                <tr class="hover:bg-white/30">
                                    <td class="px-4 py-3 font-medium text-gray-600">Alamat Lengkap</td>
                                    <td class="px-4 py-3 text-gray-800">
                                        {{ $screening->patient_address_domisili ?? '-' }} <br>
                                        <span class="text-xs text-gray-500">RT: {{ $screening->patient_address_rt ?? '-' }} / RW: {{ $screening->patient_address_rw ?? '-' }} - Kelurahan {{ $screening->patient_address_kelurahan ?? '-' }}</span>
                                         <div class="mt-1 text-xs text-gray-400">KTP: {{ $screening->patient_address_ktp ?? '-' }}</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Map Display --}}
                    @php
                        $lat = $screening->latitude ?? $screening->answers['latitude'] ?? null;
                        $lng = $screening->longitude ?? $screening->answers['longitude'] ?? null;
                    @endphp
                    @if($lat && $lng)
                    <div class="mt-6">
                        <h6 class="font-bold text-lg text-gray-800 mb-2">Lokasi Penginputan</h6>
                        <div id="map-detail" class="w-full h-[300px] rounded-xl border border-gray-200 shadow-sm mb-4"></div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Latitude</label>
                                <div class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-700 font-mono">{{ $lat }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Longitude</label>
                                <div class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-sm text-gray-700 font-mono">{{ $lng }}</div>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex justify-end">
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $lat }},{{ $lng }}" target="_blank" class="bg-white hover:bg-gray-50 text-blue-600 border border-blue-200 px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition-all shadow-sm">
                                <i class="ri-map-2-line text-lg"></i>
                                Buka di Google Maps
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Risk Factors --}}
                <div>
                     <div class="flex items-center gap-2 mb-4">
                        <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-xs">1</span>
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
                        <span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-800 flex items-center justify-center font-bold text-xs">2</span>
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
                     <p class="text-xs text-gray-500 mt-2 italic">Hasil otomatis: minimal 1 jawaban "Ya" pada pertanyaan 11-15 langsung tercatat sebagai suspek.</p>
                </div>
            </div>

        @else
             {{-- Edit Mode --}}
             <div class="flex items-center gap-2 mb-6">
                 <span class="w-8 h-8 rounded-full bg-[var(--color-glass-primary)] text-white flex items-center justify-center font-bold text-sm">A</span>
                <span class="text-sm font-semibold text-gray-700">Identitas & Alamat</span>
            </div>

             <form method="POST" action="{{ route('kader.screening.update', $screening) }}" id="screeningForm">
                @csrf
                @method('PUT')
                
                {{-- Reuse grid structure from Create --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                     <div class="md:col-span-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">WNI</label>
                        <select name="patient_is_wni" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" required>
                            <option value="">Pilih</option>
                            <option value="1" @selected(old('patient_is_wni', (string) $screening->patient_is_wni) === '1')>Ya</option>
                            <option value="0" @selected(old('patient_is_wni', (string) $screening->patient_is_wni) === '0')>Tidak</option>
                        </select>
                    </div>
                     <div class="md:col-span-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Peserta</label>
                        <input type="text" name="patient_name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_name', $screening->patient_name) }}" required>
                    </div>
                    <div class="md:col-span-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NIK</label>
                        <input type="text" name="patient_nik" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_nik', $screening->patient_nik) }}" id="patientNik">
                    </div>
                     <div class="md:col-span-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
                        <select name="patient_gender" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" required>
                            <option value="">Pilih</option>
                            <option value="L" @selected(old('patient_gender', $screening->patient_gender) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('patient_gender', $screening->patient_gender) === 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div class="md:col-span-4">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                        <input type="text" name="patient_birth_place" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_birth_place', $screening->patient_birth_place) }}" required>
                    </div>
                    <div class="md:col-span-4">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                        <input type="date" name="patient_birth_date" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_birth_date', optional($screening->patient_birth_date)->format('Y-m-d')) }}" required>
                    </div>
                    <div class="md:col-span-4">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Umur (tahun)</label>
                        <input type="number" name="patient_age" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-100/50 cursor-not-allowed" value="{{ old('patient_age', $screening->patient_age) }}" readonly required>
                    </div>
                    <div class="md:col-span-3">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Berat Badan (kg)</label>
                        <input type="number" step="0.1" name="patient_weight" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_weight', $screening->patient_weight) }}" required>
                    </div>
                    <div class="md:col-span-3">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Tinggi Badan (cm)</label>
                        <input type="number" step="0.1" name="patient_height" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_height', $screening->patient_height) }}" required>
                    </div>
                    <div class="md:col-span-6">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP (opsional)</label>
                        <input type="text" name="patient_phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_phone', $screening->patient_phone) }}">
                    </div>

                    <div class="md:col-span-4">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">RT</label>
                        <input type="text" name="patient_address_rt" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_rt', $screening->patient_address_rt) }}" required>
                    </div>
                     <div class="md:col-span-4">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">RW</label>
                        <input type="text" name="patient_address_rw" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_rw', $screening->patient_address_rw) }}" required>
                    </div>
                     <div class="md:col-span-4">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Kelurahan</label>
                        <input type="text" name="patient_address_kelurahan" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_kelurahan', $screening->patient_address_kelurahan) }}" required>
                    </div>

                    <div class="md:col-span-6">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat KTP</label>
                        <input type="text" name="patient_address_ktp" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_ktp', $screening->patient_address_ktp) }}" required>
                    </div>
                     <div class="md:col-span-6">
                         @php
                            $domisiliSame = $screening->patient_address_domisili && $screening->patient_address_ktp
                                ? $screening->patient_address_domisili === $screening->patient_address_ktp
                                : false;
                        @endphp
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Domisili</label>
                        <input type="text" name="patient_address_domisili" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_domisili', $screening->patient_address_domisili) }}" required>
                        <div class="mt-2 flex items-center gap-2">
                             <input type="checkbox" id="domisiliSame" name="domisili_same" class="w-4 h-4 text-[var(--color-glass-primary)] border-gray-300 rounded focus:ring-[var(--color-glass-primary)]" @checked(old('domisili_same', $domisiliSame))>
                            <label for="domisiliSame" class="text-sm text-gray-600 select-none cursor-pointer">Sama dengan alamat KTP</label>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-gray-200/50 my-8"></div>

                 <h6 class="font-bold text-lg text-gray-800 mb-6">Pertanyaan Skrining</h6>
                 
                 <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                     {{-- Bagian 1 --}}
                    <div class="lg:col-span-2">
                         <div class="flex items-center gap-2 mb-4">
                            <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center font-bold text-xs">1</span>
                             <span class="text-sm font-bold text-gray-700 text-uppercase">Faktor Risiko & Riwayat</span>
                        </div>
                    </div>
                    
                     <div class="space-y-6">
                        @foreach ($riskQuestions as $key => $question)
                            @if ($loop->iteration <= 5)
                                <div>
                                    <label class="block text-sm font-semibold text-gray-800 mb-3">{{ $loop->iteration }}. {{ $question }}</label>
                                    <div class="flex gap-4">
                                         <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                            <input type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key, $screening->answers[$key] ?? '') === 'ya') required class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                            <span class="text-sm text-gray-700 font-medium">Ya</span>
                                        </label>
                                         <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                            <input type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key, $screening->answers[$key] ?? '') === 'tidak') class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                            <span class="text-sm text-gray-700 font-medium">Tidak</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    {{-- 6-last questions --}}
                     <div class="space-y-6">
                        @foreach ($riskQuestions as $key => $question)
                            @if ($loop->iteration > 5)
                                <div>
                                    <label class="block text-sm font-semibold text-gray-800 mb-3">{{ $loop->iteration }}. {{ $question }}</label>
                                    <div class="flex gap-4">
                                         <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                            <input type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key, $screening->answers[$key] ?? '') === 'ya') required class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                            <span class="text-sm text-gray-700 font-medium">Ya</span>
                                        </label>
                                         <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                            <input type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key, $screening->answers[$key] ?? '') === 'tidak') class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                            <span class="text-sm text-gray-700 font-medium">Tidak</span>
                                        </label>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    
                     {{-- Bagian 2 --}}
                    <div class="lg:col-span-2 pt-4">
                         <div class="h-px bg-gray-200/50 mb-6"></div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-8 h-8 rounded-full bg-yellow-100 text-yellow-800 flex items-center justify-center font-bold text-xs">2</span>
                             <span class="text-sm font-bold text-gray-700 text-uppercase">Gejala TBC (Penentu Suspek)</span>
                        </div>
                    </div>
                    
                    <div class="lg:col-span-2 space-y-6">
                        @foreach ($symptomQuestions as $key => $question)
                            <div>
                                 <label class="block text-sm font-semibold text-gray-800 mb-3">{{ $loop->iteration + count($riskQuestions) }}. {{ $question }}</label>
                                <div class="flex gap-4">
                                     <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                        <input type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key, $screening->answers[$key] ?? '') === 'ya') required class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                        <span class="text-sm text-gray-700 font-medium">Ya</span>
                                    </label>
                                     <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                        <input type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key, $screening->answers[$key] ?? '') === 'tidak') class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                        <span class="text-sm text-gray-700 font-medium">Tidak</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                 </div>

                <div class="flex justify-end gap-3 mt-8">
                     <a href="{{ route('kader.screening.show', $screening) }}" class="glass-button px-6 py-2.5 rounded-xl font-bold text-gray-600 bg-white/50 hover:bg-white border border-gray-200 no-underline text-sm">Batal</a>
                    <button type="submit" class="glass-button-cta px-8 py-2.5 rounded-xl font-bold text-white shadow-lg text-sm">Simpan Perubahan</button>
                </div>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Map Logic
            const lat = {{ $screening->latitude ?? $screening->answers['latitude'] ?? 'null' }};
            const lng = {{ $screening->longitude ?? $screening->answers['longitude'] ?? 'null' }};
            
            if (lat && lng) {
                const map = L.map('map-detail').setView([lat, lng], 16);
                
                // Google Maps Layer (HTTPS)
                L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '© Google Maps'
                }).addTo(map);
                
                // Force map resize to fix "gray box" issue
                setTimeout(() => { 
                    map.invalidateSize(); 
                }, 500);
                
                L.marker([lat, lng]).addTo(map)
                    .bindPopup('Lokasi Penginputan')
                    .openPopup();
            }

            // Existing Logic
            const wniField = document.querySelector('select[name="patient_is_wni"]');
            const nikField = document.getElementById('patientNik');
            const birthDateField = document.querySelector('input[name="patient_birth_date"]');
            const ageField = document.querySelector('input[name="patient_age"]');
            const addressKtpField = document.querySelector('input[name="patient_address_ktp"]');
            const addressDomField = document.querySelector('input[name="patient_address_domisili"]');
            const domisiliSame = document.getElementById('domisiliSame');

             // Logic from create view
             const toggleNikRequired = () => {
                if (!wniField || !nikField) return;
                const isWni = wniField.value === '1';
                nikField.required = isWni;
            };

            const syncAge = () => {
                 if (!birthDateField || !ageField) return;
                if (!birthDateField.value) {
                    ageField.value = '';
                    return;
                }
                const today = new Date();
                const birth = new Date(birthDateField.value);
                let age = today.getFullYear() - birth.getFullYear();
                 const monthDiff = today.getMonth() - birth.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                    age -= 1;
                }
                ageField.value = age >= 0 ? age : '';
            };

            const syncDomisili = () => {
                 if (!domisiliSame || !addressKtpField || !addressDomField) return;
                if (domisiliSame.checked) {
                    addressDomField.value = addressKtpField.value;
                    addressDomField.setAttribute('readonly', 'readonly');
                     addressDomField.classList.add('bg-gray-100', 'text-gray-500');
                } else {
                    addressDomField.removeAttribute('readonly');
                     addressDomField.classList.remove('bg-gray-100', 'text-gray-500');
                }
            };

            wniField?.addEventListener('change', toggleNikRequired);
            birthDateField?.addEventListener('change', syncAge);
            domisiliSame?.addEventListener('change', syncDomisili);
            addressKtpField?.addEventListener('input', () => {
                if (domisiliSame?.checked) addressDomField.value = addressKtpField.value;
            });

            // Run init
            toggleNikRequired();
            // syncAge(); // Don't run syncAge on edit init, trust server value
            syncDomisili();
        });
    </script>
@endpush
