<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SITUBA') }} | Masuk</title>
    
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
    </style>
</head>
<body class="font-body bg-gray-50 text-gray-900 antialiased h-full">

    <div class="min-h-screen flex">
        
        <!-- LEFT PANE: Art (Matches Register) -->
        <div class="hidden lg:flex lg:w-1/2 bg-emerald-50 relative overflow-hidden items-center justify-center p-12">
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 max-w-lg">
                <a href="/" class="inline-flex items-center gap-3 mb-8 group">
                    <img src="{{ asset('assets/img/situba-logo.png') }}" class="h-10 w-auto" alt="Logo">
                    <span class="font-heading font-bold text-2xl text-gray-900 group-hover:text-emerald-600 transition-colors">SITUBA</span>
                </a>
                
                <h1 class="font-heading text-5xl font-bold text-gray-900 leading-tight mb-6">
                    Deteksi TBC, <br>
                    <span class="text-emerald-600">Lebih Cepat</span>
                </h1>
                
                <p class="text-gray-600 text-lg leading-relaxed mb-8">
                    Masuk ke dashboard untuk memantau kasus, validasi data, dan koordinasi penanganan TBC.
                </p>

                 <!-- Trust Badges -->
                 <div class="flex items-center gap-6 text-sm font-medium text-gray-500">
                    <span class="flex items-center gap-2"><i class="fa-solid fa-server text-emerald-500"></i> Terintegrasi</span>
                    <span class="flex items-center gap-2"><i class="fa-solid fa-check-shield text-emerald-500"></i> Valid</span>
                </div>
            </div>
        </div>

        <!-- RIGHT PANE: Form -->
        <div class="w-full lg:w-1/2 flex flex-col bg-white h-screen overflow-y-auto">
            
            <!-- Mobile Header -->
            <div class="lg:hidden p-6 pb-0 flex items-center justify-between">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('assets/img/situba-logo.png') }}" class="h-8 w-auto" alt="Logo">
                    <span class="font-heading font-bold text-xl text-gray-900">SITUBA</span>
                </a>
                <a href="{{ route('register') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700">Daftar</a>
            </div>

            <div class="flex-1 flex flex-col justify-center max-w-md mx-auto w-full p-6 lg:p-12">
                
                <div class="mb-8">
                    <h2 class="font-heading text-3xl font-bold text-gray-900 mb-2">Selamat Datang</h2>
                    <p class="text-gray-500">Masuk ke akun Anda untuk melanjutkan.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ showPass: false }">
                    @csrf

                    <!-- Phone Field -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700 font-heading ml-1">Nomor WhatsApp</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </div>
                            <input type="text" name="phone" value="{{ old('phone') }}" required autofocus
                                class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all focus:bg-white"
                                placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between ml-1">
                            <label class="block text-sm font-medium text-gray-700 font-heading">Password</label>
                        </div>
                        <div class="relative">
                            <input :type="showPass ? 'text' : 'password'" name="password" required
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all focus:bg-white"
                                placeholder="••••••••">
                            <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember" class="ml-2 block text-sm text-gray-600">Ingat Saya</label>
                        </div>
                        {{-- 
                        <a href="{{ route('password.wa') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-1.5">
                            <i class="fab fa-whatsapp"></i>
                            <span>Lupa Password?</span>
                        </a>
                        --}}
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-lg shadow-emerald-500/30 text-sm font-bold text-white bg-gray-900 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all transform hover:-translate-y-0.5">
                            Masuk
                        </button>
                    </div>

                    <p class="text-center text-sm text-gray-500">
                        Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-emerald-600 hover:text-emerald-500 transition-colors">Daftar Sekarang</a>
                    </p>
                    
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-emerald-600 transition-colors">
                            <i class="fa-solid fa-newspaper text-emerald-500"></i> Baca Berita & Edukasi
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        @if ($errors->any())
            const errors = @json($errors->all());
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                html: '<ul class="text-left space-y-1 text-sm">' + errors.map(e => `<li>${e}</li>`).join('') + '</ul>',
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-bold shadow-lg shadow-emerald-500/20'
                }
            });
        @endif
        
        @if (session('status') || session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @json(session('status') ?? session('success')),
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
