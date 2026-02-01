@extends('layouts.soft')

@section('subjudul', 'Form skrining kader')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        .select2-container {
            width: 100% !important;
        }
        .select2-container .select2-selection--single {
            height: 46px; /* Match tailwind py-2.5 + border */
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb; /* gray-200 */
            border-radius: 0.5rem; /* rounded-lg */
            background-color: rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px;
            top: 1px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #374151; /* gray-700 */
            padding-left: 0;
            line-height: normal;
        }
        /* Focus state mimic */
        .select2-container--open .select2-selection--single {
             border-color: #10B981;
             box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
        }
    </style>
@endpush

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-6 border-b border-gray-200/50">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Tambah Skrining Pasien</h5>
                 <p class="text-sm text-gray-500 mb-0">Lengkapi identitas pasien dan isi pertanyaan skrining.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-[var(--color-glass-primary)] text-white flex items-center justify-center font-bold text-sm">A</span>
                <span class="text-sm font-semibold text-gray-700">Identitas & Alamat</span>
            </div>
        </div>

        <form method="POST" action="{{ route('kader.screening.store') }}" id="screeningForm">
            @csrf
            
             <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                 {{-- WNI --}}
                <div class="md:col-span-4">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">WNI</label>
                    <select name="patient_is_wni" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" required>
                        <option value="">Pilih</option>
                        <option value="1" @selected(old('patient_is_wni', '1') === '1')>Ya</option>
                        <option value="0" @selected(old('patient_is_wni') === '0')>Tidak</option>
                    </select>
                </div>

                {{-- Nama --}}
                <div class="md:col-span-8">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Peserta</label>
                    <input type="text" name="patient_name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_name') }}" placeholder="Nama sesuai KTP" required>
                </div>

                {{-- NIK --}}
                 <div class="md:col-span-6">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">NIK</label>
                    <input type="text" name="patient_nik" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_nik') }}" id="patientNik" inputmode="numeric" placeholder="NIK (angka saja)" oninput="this.value = this.value.replace(/\D/g, '')" onkeypress="if (event.key && !/[0-9]/.test(event.key)) event.preventDefault()">
                </div>

                {{-- Gender --}}
                <div class="md:col-span-6">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
                    <select name="patient_gender" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" required>
                        <option value="">Pilih</option>
                        <option value="L" @selected(old('patient_gender') === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('patient_gender') === 'P')>Perempuan</option>
                    </select>
                </div>

                 {{-- TTL --}}
                <div class="md:col-span-4">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Tempat Lahir</label>
                    <input type="text" name="patient_birth_place" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_birth_place') }}" placeholder="Contoh: Surakarta" required>
                </div>
                <div class="md:col-span-4">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                    <input type="date" name="patient_birth_date" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_birth_date') }}" required>
                </div>
                 <div class="md:col-span-4">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Umur (tahun)</label>
                    <input type="number" name="patient_age" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-100/50 text-gray-600 cursor-not-allowed" min="0" max="150" value="{{ old('patient_age') }}" readonly required>
                </div>

                {{-- Fisik --}}
                 <div class="md:col-span-3">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Berat Badan (kg)</label>
                    <input type="number" step="0.1" name="patient_weight" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" min="0" value="{{ old('patient_weight') }}" required>
                </div>
                <div class="md:col-span-3">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" name="patient_height" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" min="0" value="{{ old('patient_height') }}" required>
                </div>
                 <div class="md:col-span-6">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP (opsional)</label>
                    <input type="text" name="patient_phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_phone') }}" placeholder="Nomor HP (angka saja)" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '')" onkeypress="if (event.key && !/[0-9]/.test(event.key)) event.preventDefault()">
                </div>

                {{-- Alamat --}}
                 <div class="md:col-span-4">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">RT</label>
                    <input type="text" name="patient_address_rt" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_rt') }}" placeholder="00" inputmode="numeric" maxlength="3" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 3)" onkeypress="if (event.key && !/[0-9]/.test(event.key)) event.preventDefault()" required>
                </div>
                <div class="md:col-span-4">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">RW</label>
                    <input type="text" name="patient_address_rw" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_rw') }}" placeholder="00" inputmode="numeric" maxlength="3" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 3)" onkeypress="if (event.key && !/[0-9]/.test(event.key)) event.preventDefault()" required>
                </div>
                 <div class="md:col-span-4">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Kelurahan</label>
                    <select name="patient_address_kelurahan" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all select2-kelurahan" required>
                        <option value="">Pilih kelurahan</option>
                        @if (!empty($kelurahanName)
                            && !collect($kelurahanOptions ?? [])->contains($kelurahanName)
                            && (empty($kelurahanOptions) || \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($kelurahanName), 'kelurahan')))
                            <option value="{{ $kelurahanName }}" selected>{{ $kelurahanName }}</option>
                        @endif
                        @foreach ($kelurahanOptions ?? [] as $kelurahanOption)
                            <option value="{{ $kelurahanOption }}" @selected(old('patient_address_kelurahan', $kelurahanName ?? '') === $kelurahanOption)>
                                {{ $kelurahanOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-6">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat KTP</label>
                     <div class="relative">
                        <input type="text" name="patient_address_ktp" id="patient_address_ktp" class="w-full pl-4 pr-10 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_ktp') }}" placeholder="Alamat sesuai KTP" required>
                        <button type="button" id="triggerLocationBtn" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[var(--color-glass-primary)] transition-colors p-1" title="Ambil Lokasi Saat Ini">
                            <i class="ri-map-pin-add-line text-lg"></i>
                        </button>
                     </div>
                </div>
                <div class="md:col-span-6">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Domisili</label>
                    <input type="text" name="patient_address_domisili" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('patient_address_domisili') }}" placeholder="Alamat tempat tinggal sekarang" required>
                    <div class="mt-2 flex items-center gap-2">
                        <input type="checkbox" id="domisiliSame" name="domisili_same" class="w-4 h-4 text-[var(--color-glass-primary)] border-gray-300 rounded focus:ring-[var(--color-glass-primary)]">
                        <label for="domisiliSame" class="text-sm text-gray-600 select-none cursor-pointer">Sama dengan alamat KTP</label>
                    </div>
                </div>
            </div>
             <div class="h-px bg-gray-200/50 mb-8"></div>
            
             {{-- Location Section --}}
             <h6 class="font-bold text-lg text-gray-800 mb-6">Lokasi & Peta</h6>
             <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                 <div class="md:col-span-12">
                     <div class="flex flex-col gap-4">
                         <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                             <div>
                                 <label class="block text-sm font-semibold text-gray-700 mb-1">Titik Lokasi</label>
                                 <p class="text-xs text-gray-500 italic">Pastikan GPS aktif dan izinkan akses lokasi.</p>
                             </div>
                             <button type="button" id="getLocationBtn" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-all shadow-md active:scale-95">
                                <i class="ri-map-pin-user-line text-lg"></i> Ambil Lokasi Saat Ini
                             </button>
                         </div>
                         
                         <div class="relative w-full h-[400px] rounded-xl border border-gray-200 shadow-sm overflow-hidden bg-gray-50">
                             <div id="map" class="absolute inset-0 z-0"></div>
                             <div id="map-loader" class="absolute inset-0 z-10 flex items-center justify-center bg-gray-50/80 backdrop-blur-sm hidden">
                                 <div class="flex flex-col items-center gap-2">
                                     <i class="ri-loader-4-line text-3xl text-blue-600 animate-spin"></i>
                                     <span class="text-sm text-gray-600 font-medium">Memuat Peta...</span>
                                 </div>
                             </div>
                         </div>
                         
                         {{-- Visible Coordinates (Read-only) --}}
                         <div class="grid grid-cols-2 gap-4">
                             <div>
                                 <label class="block text-xs font-semibold text-gray-500 mb-1">Latitude</label>
                                 <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" readonly class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-gray-100 text-sm text-gray-600 focus:outline-none cursor-default">
                             </div>
                             <div>
                                 <label class="block text-xs font-semibold text-gray-500 mb-1">Longitude</label>
                                 <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" readonly class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-gray-100 text-sm text-gray-600 focus:outline-none cursor-default">
                             </div>
                         </div>
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
                
                 {{-- Col 1 --}}
                <div class="space-y-6">
                    @foreach ($riskQuestions as $key => $question)
                        @if ($loop->iteration <= 5)
                            <div>
                                <label class="block text-sm font-semibold text-gray-800 mb-3">{{ $loop->iteration }}. {{ $question }}</label>
                                <div class="flex gap-4">
                                     <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                        <input type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key) === 'ya') required class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                        <span class="text-sm text-gray-700 font-medium">Ya</span>
                                    </label>
                                     <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                        <input type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key) === 'tidak') class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                        <span class="text-sm text-gray-700 font-medium">Tidak</span>
                                    </label>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                 {{-- Col 2 --}}
                <div class="space-y-6">
                     @foreach ($riskQuestions as $key => $question)
                        @if ($loop->iteration > 5)
                            <div>
                                <label class="block text-sm font-semibold text-gray-800 mb-3">{{ $loop->iteration }}. {{ $question }}</label>
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                        <input type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key) === 'ya') required class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                        <span class="text-sm text-gray-700 font-medium">Ya</span>
                                    </label>
                                     <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                        <input type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key) === 'tidak') class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
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
                                    <input type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" @checked(old($key) === 'ya') required class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                    <span class="text-sm text-gray-700 font-medium">Ya</span>
                                </label>
                                 <label class="flex items-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/50 border border-transparent hover:border-gray-200 transition-all">
                                    <input type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak" @checked(old($key) === 'tidak') class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]">
                                    <span class="text-sm text-gray-700 font-medium">Tidak</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="text-xs text-center text-gray-500 bg-gray-50 p-3 rounded-lg border border-gray-100 italic">
                <i class="ri-information-line align-middle me-1"></i> Hasil otomatis: minimal 1 jawaban "Ya" pada pertanyaan 11-15 langsung tercatat sebagai suspek.
            </p>

            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('kader.screening.index') }}" class="glass-button px-6 py-2.5 rounded-xl font-bold text-gray-600 bg-white/50 hover:bg-white border border-gray-200 no-underline text-sm">Batal</a>
                <button type="submit" class="glass-button-cta px-8 py-2.5 rounded-xl font-bold text-white shadow-lg text-sm">Simpan Skrining</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wniField = document.querySelector('select[name="patient_is_wni"]');
            const nikField = document.getElementById('patientNik');
            const birthDateField = document.querySelector('input[name="patient_birth_date"]');
            const ageField = document.querySelector('input[name="patient_age"]');
            const addressKtpField = document.querySelector('input[name="patient_address_ktp"]');
            const addressDomField = document.querySelector('input[name="patient_address_domisili"]');
            const domisiliSame = document.getElementById('domisiliSame');
            const rtField = document.querySelector('input[name="patient_address_rt"]');
            const rwField = document.querySelector('input[name="patient_address_rw"]');

            const toggleNikRequired = () => {
                if (!wniField || !nikField) {
                    return;
                }
                const isWni = wniField.value === '1';
                nikField.required = isWni;
                // Update opacity via parent div style/class logic if needed, 
                // but standard disabled look might be enough or handled by browser
                if (!isWni) {
                    nikField.parentElement.classList.add('opacity-50');
                } else {
                    nikField.parentElement.classList.remove('opacity-50');
                }
            };

            wniField?.addEventListener('change', toggleNikRequired);
            toggleNikRequired();

            const syncAge = () => {
                if (!birthDateField || !ageField) {
                    return;
                }
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
                syncLansia();
            };

            const syncLansia = () => {
                const ageValue = parseInt(ageField?.value ?? '', 10);
                const lansiaYes = document.getElementById('lansia_ya');
                const lansiaNo = document.getElementById('lansia_tidak');
                if (!Number.isFinite(ageValue) || !lansiaYes || !lansiaNo) {
                    return;
                }
                if (ageValue >= 66) {
                    lansiaYes.checked = true;
                } else {
                    lansiaNo.checked = true;
                }
            };

            birthDateField?.addEventListener('change', syncAge);
            syncAge();

            const syncDomisili = () => {
                if (!domisiliSame || !addressKtpField || !addressDomField) {
                    return;
                }
                if (domisiliSame.checked) {
                    addressDomField.value = addressKtpField.value;
                    addressDomField.setAttribute('readonly', 'readonly');
                    addressDomField.classList.add('bg-gray-100', 'text-gray-500'); // Add styling for readonly
                } else {
                    addressDomField.removeAttribute('readonly');
                     addressDomField.classList.remove('bg-gray-100', 'text-gray-500');
                }
            };

            domisiliSame?.addEventListener('change', syncDomisili);
            addressKtpField?.addEventListener('input', () => {
                if (domisiliSame?.checked) {
                    addressDomField.value = addressKtpField.value;
                }
            });
            syncDomisili();

            const enforceDigits = (field) => {
                if (!field) {
                    return;
                }
                field.addEventListener('input', () => {
                    field.value = field.value.replace(/\D/g, '').slice(0, 3);
                });
            };
            enforceDigits(rtField);
            enforceDigits(rwField);

            const padTwoDigits = (field) => {
                if (!field) {
                    return;
                }
                field.addEventListener('blur', () => {
                    const value = field.value.trim();
                    if (value.length === 1) {
                        field.value = `0${value}`;
                    }
                });
            };
            padTwoDigits(rtField);
            padTwoDigits(rwField);

            const applyIndonesianValidation = (field) => {
                if (!field) {
                    return;
                }
                field.addEventListener('invalid', () => {
                    if (field.validity.valueMissing) {
                        field.setCustomValidity('Harap isi kolom ini.');
                        return;
                    }
                    if (field.validity.patternMismatch) {
                        field.setCustomValidity('Format tidak sesuai.');
                        return;
                    }
                    if (field.validity.typeMismatch) {
                        field.setCustomValidity('Format tidak valid.');
                        return;
                    }
                    field.setCustomValidity('Data tidak valid.');
                });
                field.addEventListener('input', () => {
                    field.setCustomValidity('');
                });
                field.addEventListener('change', () => {
                    field.setCustomValidity('');
                });
            };

            document.querySelectorAll('#screeningForm input, #screeningForm select, #screeningForm textarea')
                .forEach((field) => applyIndonesianValidation(field));

            if (window.jQuery && window.jQuery.fn.select2) {
                window.jQuery('.select2-kelurahan').select2({
                    width: '100%',
                    placeholder: 'Pilih kelurahan',
                });
            }

            // --- Location Logic ---
            const mapContainer = document.getElementById('map');
            const getLocationBtn = document.getElementById('getLocationBtn');
            const triggerLocationBtn = document.getElementById('triggerLocationBtn');
            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const addressInput = document.getElementById('patient_address_ktp'); // Changed target to KTP
            let map, marker;

            // Initialize Map (Default to Surakarta/Solo ID)
            const defaultLat = -7.5755;
            const defaultLng = 110.8243;
            
            if (mapContainer) {
                 map = L.map('map').setView([defaultLat, defaultLng], 13);
                 
                 // Google Maps Layer (HTTPS)
                 L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                    attribution: '© Google Maps'
                }).addTo(map);
                
                // Force map resize to fix "gray box" issue when initially hidden or in flexible container
                setTimeout(() => { 
                    map.invalidateSize(); 
                }, 500);
                
                // Also invalidate on window resize
                window.addEventListener('resize', () => {
                     map.invalidateSize();
                });

                // If old values exist (validation error), restore marker
                if (latInput.value && lngInput.value) {
                    const savedLat = parseFloat(latInput.value);
                    const savedLng = parseFloat(lngInput.value);
                    updateMarker(savedLat, savedLng);
                    map.setView([savedLat, savedLng], 16);
                }
            }

            function updateMarker(lat, lng) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], {draggable: true}).addTo(map);
                    
                    // Update input on drag end
                    marker.on('dragend', function(e) {
                        const position = marker.getLatLng();
                        latInput.value = position.lat;
                        lngInput.value = position.lng;
                    });
                }
                latInput.value = lat;
                lngInput.value = lng;
            }

            const handleGetLocation = (btn) => {
                if (!navigator.geolocation) {
                    alert('Browser anda tidak mendukung Geolocation.');
                    return;
                }
                
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="ri-loader-4-line animate-spin"></i>';

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        updateMarker(lat, lng);
                        map.setView([lat, lng], 16);
                        
                        // Reverse Geocoding (Nominatim) with Indonesian Language
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=id`)
                            .then(response => response.json())
                            .then(data => {
                                    if(data && data.display_name) {
                                         // Autofill Alamat KTP
                                         if(addressInput && !addressInput.value) {
                                             // User requested "Complete Address" -> display_name is usually the best validation
                                             // It includes Jalan, Kelurahan, Kecamatan, Kota, etc.
                                             addressInput.value = data.display_name;
                                             addressInput.dispatchEvent(new Event('input'));
                                         }

                                     // Attempt to extract RT/RW (Nominatim rarely gives this explicitly, but we can try parsing)
                                     // Or use 'neighbourhood' field if available as fallback, though imprecise.
                                     // For now, let's just log the full address components for debugging if needed.
                                     
                                     // Basic heuristic for RT/RW if present in display_name (e.g. "RT 05 RW 02")
                                     const rtMatch = data.display_name.match(/RT\s*0*(\d+)/i);
                                     const rwMatch = data.display_name.match(/RW\s*0*(\d+)/i);
                                     
                                     const rtField = document.querySelector('input[name="patient_address_rt"]');
                                     const rwField = document.querySelector('input[name="patient_address_rw"]');

                                     if (rtMatch && rtField && !rtField.value) {
                                         rtField.value = rtMatch[1].padStart(3, '0'); // Format 005
                                         rtField.dispatchEvent(new Event('input'));
                                     }
                                     if (rwMatch && rwField && !rwField.value) {
                                         rwField.value = rwMatch[1].padStart(3, '0'); // Format 002
                                         rwField.dispatchEvent(new Event('input'));
                                     }
                                }
                            })
                            .catch(err => console.error('Reverse Geocoding Error:', err))
                            .finally(() => {
                                btn.disabled = false;
                                btn.innerHTML = originalHtml;
                            });
                    },
                    (error) => {
                        alert('Gagal mengambil lokasi: ' + error.message);
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            };

            getLocationBtn?.addEventListener('click', () => handleGetLocation(getLocationBtn));
            triggerLocationBtn?.addEventListener('click', () => handleGetLocation(triggerLocationBtn));
        });
    </script>
@endpush
