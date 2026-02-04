<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SITUBA') }} | Reset Password via WhatsApp</title>
    
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
        <!-- LEFT PANE: Art -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-emerald-50 to-teal-50 relative overflow-hidden items-center justify-center p-12">
            <!-- Decorative blobs -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-80 h-80 bg-gradient-to-br from-blue-500/10 to-emerald-500/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 max-w-lg text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-3xl shadow-2xl mb-8">
                    <i class="fab fa-whatsapp text-5xl text-emerald-600"></i>
                </div>
                
                <h1 class="font-heading text-5xl font-bold text-gray-900 leading-tight mb-6">
                    Reset Password<br>
                    <span class="text-emerald-600">via WhatsApp</span>
                </h1>
                
                <p class="text-gray-600 text-lg leading-relaxed mb-8">
                    Dapatkan kode OTP melalui WhatsApp untuk reset password akun Anda dengan aman dan cepat.
                </p>

                 <!-- Trust Badges -->
                 <div class="flex items-center justify-center gap-8 text-sm font-medium text-gray-600">
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-shield-check text-emerald-600 text-xl"></i>
                        </div>
                        <span>Aman</span>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-clock text-emerald-600 text-xl"></i>
                        </div>
                        <span>Cepat</span>
                    </div>
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-lg">
                            <i class="fa-solid fa-lock text-emerald-600 text-xl"></i>
                        </div>
                        <span>Terpercaya</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT PANE: Form -->
        <div class="w-full lg:w-1/2 flex flex-col bg-white h-screen overflow-y-auto">
            
            <!-- Mobile Header -->
            <div class="lg:hidden p-6 pb-0 flex items-center justify-between border-b border-gray-100">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ asset('assets/img/situba-logo.png') }}" class="h-8 w-auto" alt="Logo">
                    <span class="font-heading font-bold text-xl text-gray-900">SITUBA</span>
                </a>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">Masuk</a>
            </div>

            <div class="flex-1 flex flex-col justify-center max-w-md mx-auto w-full p-6 lg:p-12">
                
                <!-- Page Title -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-emerald-100 rounded-2xl mb-4 lg:hidden">
                        <i class="fab fa-whatsapp text-2xl text-emerald-600"></i>
                    </div>
                    <h2 class="font-heading text-3xl font-bold text-gray-900 mb-2">Lupa Password?</h2>
                    <p class="text-gray-600">Masukkan nomor WhatsApp yang terdaftar untuk menerima kode OTP.</p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('password.wa.request') }}" class="space-y-6">
                    @csrf

                    <!-- Phone Field -->
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 font-heading">Nomor WhatsApp</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <span class="text-sm font-medium text-gray-500">+62</span>
                            </div>
                            <input type="text" name="phone" value="{{ old('phone') }}" required autofocus
                                class="w-full pl-14 pr-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all @error('phone') border-red-500 bg-red-50 @enderror"
                                placeholder="8123456789">
                        </div>
                        <p class="text-xs text-gray-500 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-gray-400"></i>
                            <span>Tanpa 0 di depan. Contoh: 8123456789</span>
                        </p>
                        @error('phone')
                            <p class="text-sm text-red-600 flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                        class="w-full flex justify-center items-center gap-2.5 py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-emerald-500/20 text-base font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/50 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        <i class="fab fa-whatsapp text-xl"></i>
                        <span>Kirim Kode OTP</span>
                    </button>

                    <!-- Back to Login -->
                    <p class="text-center text-sm text-gray-600 pt-2">
                        Ingat password? 
                        <a href="{{ route('login') }}" class="font-bold text-emerald-600 hover:text-emerald-700 transition-colors">Masuk di sini</a>
                    </p>

                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-normalize phone input
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.querySelector('input[name="phone"]');
            
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    if (value.startsWith('08')) value = value.substring(1);
                    else if (value.startsWith('628')) value = value.substring(2);
                    else if (value.startsWith('62') && value.length > 2) value = value.substring(2);
                    e.target.value = value;
                });
                
                phoneInput.addEventListener('paste', function(e) {
                    e.preventDefault();
                    let paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                    if (paste.startsWith('08')) paste = paste.substring(1);
                    else if (paste.startsWith('628')) paste = paste.substring(2);
                    else if (paste.startsWith('62') && paste.length > 2) paste = paste.substring(2);
                    phoneInput.value = paste;
                });
            }
        });
    
        @if ($errors->any())
            const errors = @json($errors->all());
            Swal.fire({
                icon: 'error',
                title: 'Error',
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
