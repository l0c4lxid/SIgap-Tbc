<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SITUBA') }} | Konfirmasi Password</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Work+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .font-heading { font-family: 'Outfit', sans-serif; }
        .font-body { font-family: 'Work Sans', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-body bg-gray-50 text-gray-900 antialiased h-full">
    <div class="min-h-screen flex">

        <!-- LEFT PANE: Illustration / message -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-emerald-50 to-teal-50 relative overflow-hidden items-center justify-center p-12">
            <div class="absolute top-0 right-0 -mr-24 -mt-24 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -ml-24 -mb-24 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl"></div>

            <div class="relative z-10 max-w-lg text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-3xl shadow-2xl mb-8">
                    <i class="ri-lock-2-fill text-5xl text-emerald-600"></i>
                </div>
                <h1 class="font-heading text-5xl font-bold text-gray-900 leading-tight mb-4">
                    Konfirmasi<br><span class="text-emerald-600">Password Anda</span>
                </h1>
                <p class="text-gray-600 text-lg leading-relaxed">
                    Untuk keamanan, kami perlu memastikan bahwa benar Anda yang melakukan aksi ini.
                </p>
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
            </div>

            <div class="flex-1 flex flex-col justify-center max-w-md mx-auto w-full p-6 lg:p-12">
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-emerald-100 rounded-2xl mb-4 lg:hidden">
                        <i class="ri-lock-2-fill text-2xl text-emerald-600"></i>
                    </div>
                    <h2 class="font-heading text-3xl font-bold text-gray-900 mb-2">Konfirmasi Password</h2>
                    <p class="text-gray-600">Masukkan password Anda untuk melanjutkan.</p>
                </div>

                <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-gray-700 font-heading">Password</label>
                        <div class="relative">
                            <i class="ri-key-2-line absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400"></i>
                            <input id="password" name="password" type="password" required autocomplete="current-password"
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all @error('password') border-red-500 bg-red-50 @enderror"
                                placeholder="••••••••">
                        </div>
                        @error('password')
                            <p class="text-sm text-red-600 flex items-center gap-1.5">
                                <i class="ri-error-warning-fill"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center items-center gap-2.5 py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-emerald-500/20 text-base font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/50 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        <i class="ri-shield-check-fill text-xl"></i>
                        <span>Konfirmasi</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
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
    </script>
</body>
</html>
