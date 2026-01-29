<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SITUBA') }} | Registrasi Baru</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Work+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .font-heading { font-family: 'Outfit', sans-serif; }
        .font-body { font-family: 'Work Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        
        /* Custom Scrollbar for Dropdowns */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</head>
<body class="font-body bg-gray-50 text-gray-900 antialiased h-full">

    <div class="min-h-screen flex">
        
        <!-- LEFT PANE: Art & Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-emerald-50 relative overflow-hidden items-center justify-center p-12 lg:fixed lg:inset-y-0 lg:left-0 z-10">
            <!-- Decorative Blobs -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 max-w-lg text-center lg:text-left">
                <a href="/" class="inline-flex items-center gap-3 mb-8 group">
                    <img src="{{ asset('assets/img/situba-logo.png') }}" class="h-12 w-auto" alt="Logo">
                    <span class="font-heading font-bold text-3xl text-gray-900 group-hover:text-emerald-600 transition-colors">SITUBA</span>
                </a>
                
                <h1 class="font-heading text-4xl lg:text-5xl font-bold text-gray-900 leading-tight mb-6">
                    Mulai Langkah <br>
                    <span class="text-emerald-600">Eliminasi TBC</span>
                </h1>
                
                <p class="text-gray-600 text-lg leading-relaxed mb-8">
                    Bergabung dengan ekosistem kesehatan terpadu Kota Surakarta. Akses data real-time, validasi mudah, dan penanganan tepat sasaran.
                </p>

                <div class="flex flex-col gap-4 text-sm font-medium text-gray-500">
                    <div class="flex items-center gap-3 p-4 bg-white/60 backdrop-blur-sm rounded-2xl border border-emerald-100 shadow-sm">
                        <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600">
                            <i class="fa-solid fa-users text-lg"></i>
                        </div>
                        <span class="text-gray-700">Kolaborasi Lintas Sektor & Masarakat</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANE: Form -->
        <div class="w-full lg:w-1/2 flex flex-col bg-white min-h-screen lg:ml-auto">
            
            <!-- Mobile Header: Adjusted padding and icon size -->
            <div class="lg:hidden px-6 py-5 flex items-center justify-between sticky top-0 bg-white z-20 shadow-sm border-b border-gray-100">
                <a href="/" class="flex items-center gap-2.5">
                    <img src="{{ asset('assets/img/situba-logo.png') }}" class="h-9 w-auto" alt="Logo">
                    <span class="font-heading font-bold text-2xl text-gray-900">SITUBA</span>
                </a>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg transition-colors">Masuk</a>
            </div>

            <!-- Scrollable Form Container - Full Width Mode with reduced padding on mobile -->
            <div class="flex-1 flex flex-col justify-center w-full px-5 py-8 lg:px-20 lg:py-16">
                
                <div class="mb-8">
                    <h2 class="font-heading text-3xl font-bold text-gray-900 mb-2">Buat Akun Baru</h2>
                    <p class="text-gray-500">Silakan isi data berikut untuk mendaftar sebagai mitra kesehatan.</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-6" x-data="registerForm">
                    @csrf

                    <!-- 1. ROLE SELECTION (Custom Dropdown) -->
                    <div class="space-y-1.5" x-data="searchableSelect({
                        name: 'role',
                        placeholder: 'Pilih Peran...',
                        options: roleInfo,
                        selected: '{{ old('role') }}'
                    })">
                        <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Daftar Sebagai</label>
                        <input type="hidden" name="role" x-model="value" @change="role = value">
                        
                        <!-- Trigger -->
                        <div class="relative">
                             <button type="button" @click="toggle" @click.outside="close"
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-left flex items-center justify-between focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all hover:bg-gray-100"
                                :class="{'bg-white border-emerald-500 ring-4 ring-emerald-500/10': open}">
                                <div class="flex items-center gap-3 overflow-hidden">
                                     <template x-if="value && getOption(value).icon">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-sm shrink-0">
                                            <i :class="getOption(value).icon"></i>
                                        </div>
                                     </template>
                                     <span class="block truncate font-medium text-base" x-text="value ? getOption(value).label : placeholder" :class="{'text-gray-500': !value, 'text-gray-900': value}"></span>
                                </div>
                                <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>
                            </button>

                            <!-- Dropdown -->
                            <div x-show="open" x-transition.opacity.duration.200ms
                                class="absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 max-h-80 overflow-hidden flex flex-col custom-scrollbar" x-cloak>
                                <div class="overflow-y-auto p-2 custom-scrollbar space-y-1">
                                    <template x-for="item in options" :key="item.value">
                                        <div @click="select(item.value); role = item.value" 
                                            class="cursor-pointer select-none rounded-xl px-4 py-3 flex items-center gap-4 hover:bg-emerald-50/80 transition-colors group"
                                            :class="{'bg-emerald-50 text-emerald-700': value === item.value}">
                                            <div class="w-10 h-10 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:border-emerald-200 group-hover:text-emerald-600 transition-colors" :class="{'bg-white border-emerald-200 text-emerald-600': value === item.value}">
                                                <i :class="item.icon || 'fa-solid fa-circle'" class="text-lg"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 text-base" x-text="item.label"></div>
                                                <div class="text-xs text-gray-500 mt-0.5" x-text="item.desc"></div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. NAME FIELD -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 font-heading ml-1" x-text="nameLabel"></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all focus:bg-white"
                            placeholder="Masukkan nama lengkap">
                    </div>

                    <!-- 3. DYNAMIC SECTIONS -->
                    
                    <!-- KADER SECTION -->
                    <div x-show="role === 'kader'" x-transition class="space-y-6 pt-4 border-t border-gray-100">
                        <div class="bg-blue-50 p-4 rounded-2xl border border-blue-100 flex gap-3 text-sm text-blue-700">
                            <i class="fa-solid fa-circle-info mt-0.5 text-blue-500"></i>
                            <p>Lengkapi detail wilayah tugas Anda sebagai Kader TBC.</p>
                        </div>

                        <!-- Kelurahan Select (Searchable & Iconized) -->
                         <div class="space-y-1.5" x-data="searchableSelect({
                            name: 'kader_kelurahan_id',
                            placeholder: 'Cari Kelurahan...',
                            options: kelurahanList,
                            searchable: true,
                            selected: '{{ old('kader_kelurahan_id') }}'
                        })">
                            <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Kelurahan Bertugas</label>
                            <input type="hidden" name="kader_kelurahan_id" x-model="value">
                            
                            <div class="relative">
                                <button type="button" @click="toggle" @click.outside="close"
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-left flex items-center justify-between focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all hover:bg-gray-100"
                                     :class="{'bg-white border-emerald-500 ring-4 ring-emerald-500/10': open}">
                                     <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-6 h-6 rounded bg-orange-100 text-orange-600 flex items-center justify-center text-xs shrink-0" x-show="value">
                                            <i class="fa-solid fa-map-location-dot"></i>
                                        </div>
                                        <span class="block truncate font-medium" x-text="value ? getOption(value).label : placeholder" :class="{'text-gray-500': !value}"></span>
                                     </div>
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </button>
                                
                                <div x-show="open" x-transition.opacity.duration.200ms class="absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden flex flex-col" x-cloak>
                                    <div class="p-3 border-b border-gray-100 sticky top-0 bg-white">
                                        <div class="relative">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                            <input x-model="search" x-ref="searchInput" type="text" class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 transition-colors" placeholder="Ketik nama kelurahan...">
                                        </div>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto p-2 custom-scrollbar">
                                        <template x-for="item in filteredOptions" :key="item.value">
                                            <div @click="select(item.value)" class="cursor-pointer select-none rounded-lg px-3 py-2.5 text-sm hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 flex items-center gap-3" :class="{'bg-emerald-50 text-emerald-700 font-bold': value === item.value}">
                                                <i :class="item.icon" class="text-gray-400 w-5 text-center" :class="{'text-emerald-500': value === item.value}"></i>
                                                <span x-text="item.label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0" class="p-4 text-sm text-gray-400 text-center italic">Tidak ditemukan</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-5">
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 font-heading ml-1">RW</label>
                                <input type="number" name="rw_code" value="{{ old('rw_code') }}" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:bg-white transition-all text-center placeholder-gray-300 font-bold" placeholder="001">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-sm font-medium text-gray-700 font-heading ml-1">RT</label>
                                <input type="number" name="rt_code" value="{{ old('rt_code') }}" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:bg-white transition-all text-center placeholder-gray-300 font-bold" placeholder="005">
                            </div>
                        </div>
                    </div>

                    <!-- KELURAHAN SECTION -->
                    <div x-show="role === 'kelurahan'" x-transition class="space-y-6 pt-4 border-t border-gray-100">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Nama Kelurahan</label>
                            <input type="text" name="kelurahan_name" value="{{ old('kelurahan_name') }}" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:bg-white transition-all" placeholder="Contoh: Kelurahan Nusukan">
                        </div>
                        
                        <!-- Puskesmas Select (Searchable) -->
                        <div class="space-y-1.5" x-data="searchableSelect({
                            name: 'kelurahan_puskesmas_id',
                            placeholder: 'Pilih Puskesmas Pembina...',
                            options: puskesmasList,
                            searchable: true,
                            selected: '{{ old('kelurahan_puskesmas_id') }}'
                        })">
                            <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Puskesmas Pembina</label>
                            <input type="hidden" name="kelurahan_puskesmas_id" x-model="value">
                            
                            <div class="relative">
                                <button type="button" @click="toggle" @click.outside="close"
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-left flex items-center justify-between focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all hover:bg-gray-100"
                                    :class="{'bg-white border-emerald-500 ring-4 ring-emerald-500/10': open}">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-6 h-6 rounded bg-blue-100 text-blue-600 flex items-center justify-center text-xs shrink-0" x-show="value">
                                            <i class="fa-solid fa-hospital"></i>
                                        </div>
                                        <span class="block truncate font-medium" x-text="value ? getOption(value).label : placeholder" :class="{'text-gray-500': !value}"></span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="{'rotate-180': open}"></i>
                                </button>
                                
                                <div x-show="open" x-transition.opacity.duration.200ms class="absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden flex flex-col" x-cloak>
                                    <div class="p-3 border-b border-gray-100 sticky top-0 bg-white">
                                         <div class="relative">
                                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                            <input x-model="search" x-ref="searchInput" type="text" class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-emerald-500 transition-colors" placeholder="Cari puskesmas...">
                                        </div>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto p-2 custom-scrollbar">
                                        <template x-for="item in filteredOptions" :key="item.value">
                                            <div @click="select(item.value)" class="cursor-pointer select-none rounded-lg px-3 py-2.5 text-sm hover:bg-emerald-50 text-gray-700 hover:text-emerald-700 flex items-center gap-3" :class="{'bg-emerald-50 text-emerald-700 font-bold': value === item.value}">
                                                <i :class="item.icon" class="text-gray-400 w-5 text-center" :class="{'text-emerald-500': value === item.value}"></i>
                                                <span x-text="item.label"></span>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0" class="p-4 text-sm text-gray-400 text-center italic">Tidak ditemukan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PUSKESMAS SECTION -->
                    <div x-show="role === 'puskesmas'" x-transition class="space-y-6 pt-4 border-t border-gray-100">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Nama Puskesmas</label>
                            <input type="text" name="puskesmas_name" value="{{ old('puskesmas_name') }}" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:bg-white transition-all" placeholder="Contoh: Puskesmas Gilingan">
                        </div> 
                    </div>

                    <!-- 4. COMMON FIELDS -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Nomor WhatsApp</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-phone text-sm"></i>
                            </div>
                            <input type="tel" name="phone" value="{{ old('phone') }}" required
                                class="w-full pl-12 pr-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-900 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all focus:bg-white"
                                placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <!-- Password Fields with Toggle -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5" x-data="{ showPass: false, showConfirm: false }">
                        <div class="space-y-1.5 relative">
                            <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Password</label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" name="password" required autocomplete="new-password"
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:bg-white transition-all pr-12">
                                <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-1.5 relative">
                            <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Konfirmasi</label>
                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                                    class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:bg-white transition-all pr-12">
                                <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i class="fa-solid" :class="showConfirm ? 'fa-eye-slash' : 'fa-eye'"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" 
                            class="w-full flex justify-center py-4 px-4 border border-transparent rounded-2xl shadow-xl shadow-emerald-500/20 text-base font-bold text-white bg-gray-900 hover:bg-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-500/30 transition-all transform hover:-translate-y-1">
                            Daftar Sekarang <i class="fa-solid fa-arrow-right ml-2 mt-1 text-sm"></i>
                        </button>
                    </div>

                    <p class="text-center text-sm text-gray-500">
                        Sudah punya akun? <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:text-emerald-500 transition-colors">Masuk</a>
                    </p>

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            
            // Main Form Data
            Alpine.data('registerForm', () => ({
                role: '{{ old('role') }}',
                
                // Define Roles with Icons locally
                roleInfo: [
                    { value: 'kader', label: 'Kader TBC', icon: 'fa-solid fa-user-nurse', desc: 'Petugas lapangan & pemantau pasien' },
                    { value: 'puskesmas', label: 'Puskesmas', icon: 'fa-solid fa-hospital', desc: 'Fasilitas kesehatan & validasi' },
                    { value: 'kelurahan', label: 'Kelurahan', icon: 'fa-solid fa-building-columns', desc: 'Pemangku wilayah & koordinasi' }
                ],

                // Data options from PHP - NOW WITH ICONS
                kelurahanList: [
                    @foreach($kelurahanOptions as $kel)
                        { value: '{{ $kel->id }}', label: '{{ $kel->name }}', icon: 'fa-solid fa-map-pin' },
                    @endforeach
                ],
                
                puskesmasList: [
                    @foreach($puskesmasOptions as $pusk)
                        { value: '{{ $pusk->id }}', label: '{{ optional($pusk->detail)->organization ?? $pusk->name }}', icon: 'fa-solid fa-hospital-user' },
                    @endforeach
                ],

                get nameLabel() {
                    const map = {
                        'kader': 'Nama Kader',
                        'puskesmas': 'Nama PJ Puskesmas',
                        'kelurahan': 'Nama PJ Kelurahan'
                    };
                    return map[this.role] || 'Nama Lengkap';
                }
            }));

            // Reusable Searchable Select Component
            Alpine.data('searchableSelect', ({ name, placeholder, options, selected, searchable = false }) => ({
                value: selected,
                open: false,
                search: '',
                placeholder: placeholder,
                options: options,

                get filteredOptions() {
                    if (!this.search) return this.options;
                    return this.options.filter(i => i.label.toLowerCase().includes(this.search.toLowerCase()));
                },

                getOption(val) {
                    return this.options.find(i => i.value == val) || { label: val };
                },

                toggle() { 
                    this.open = !this.open;
                    if (this.open && searchable) {
                        this.$nextTick(() => {
                           if(this.$refs.searchInput) this.$refs.searchInput.focus(); 
                        });
                    }
                },
                close() { this.open = false; },
                select(val) {
                    this.value = val;
                    this.close();
                    this.search = '';
                }
            }));
        });

        // SweetAlert Errors
        @if ($errors->any())
            const errors = @json($errors->all());
            Swal.fire({
                icon: 'error',
                title: 'Periksa Kembali',
                html: '<ul class="text-left space-y-1 text-sm">' + errors.map(e => `<li>• ${e}</li>`).join('') + '</ul>',
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-bold shadow-lg shadow-emerald-500/20'
                }
            });
        @endif
    </script>
</body>
</html>
