<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SITUBA') }} | Verifikasi OTP</title>
    
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
        
        /* OTP Input styling */
        .otp-input {
            width: 3.5rem;
            height: 3.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        @media (max-width: 640px) {
            .otp-input {
                width: 2.75rem;
                height: 2.75rem;
                font-size: 1.25rem;
            }
        }
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
                    <i class="fa-solid fa-shield-check text-5xl text-emerald-600"></i>
                </div>
                
                <h1 class="font-heading text-5xl font-bold text-gray-900 leading-tight mb-6">
                    Verifikasi<br>
                    <span class="text-emerald-600">Kode OTP</span>
                </h1>
                
                <p class="text-gray-600 text-lg leading-relaxed mb-8">
                    Masukkan 6 digit kode OTP yang telah dikirim ke WhatsApp Anda.
                </p>

                <!-- Security Info -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg">
                    <div class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fa-solid fa-clock text-emerald-600 text-xl"></i>
                        <div class="text-left">
                            <p class="font-semibold">Kode berlaku 5 menit</p>
                            <p class="text-xs text-gray-500">Maksimal 5 percobaan</p>
                        </div>
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
            </div>

            <div class="flex-1 flex flex-col justify-center max-w-md mx-auto w-full p-6 lg:p-12">
                
                <!-- Page Title -->
                <div class="mb-8">
                    <div class="inline-flex items-center justify-center w-14 h-14 bg-emerald-100 rounded-2xl mb-4 lg:hidden">
                        <i class="fa-solid fa-shield-check text-2xl text-emerald-600"></i>
                    </div>
                    <h2 class="font-heading text-3xl font-bold text-gray-900 mb-2">Verifikasi Kode OTP</h2>
                    <p class="text-gray-600">Masukkan 6 digit kode yang dikirim ke WhatsApp Anda.</p>
                </div>

                <!-- Form -->
                <form method="POST" action="{{ route('password.wa.verify.post') }}" class="space-y-6" x-data="otpForm()">
                    @csrf

                    <!-- OTP Input -->
                    <div class="space-y-3">
                        <label class="block text-sm font-semibold text-gray-700 font-heading">Kode OTP</label>
                        
                        <!-- Single input for easy paste -->
                        <div class="relative">
                            <input 
                                type="text" 
                                name="code" 
                                maxlength="6" 
                                pattern="\d{6}"
                                required
                                autofocus
                                autocomplete="one-time-code"
                                inputmode="numeric"
                                class="w-full px-6 py-4 text-center text-3xl font-bold tracking-widest bg-gray-50 border-2 border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 focus:bg-white transition-all @error('code') border-red-500 bg-red-50 @enderror"
                                placeholder="000000"
                                value="{{ old('code') }}"
                                onclick="this.select()"
                                >
                        </div>
                        
                        <div class="flex items-center justify-between text-xs">
                            <p class="text-gray-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-paste text-gray-400"></i>
                                <span>Tempel (Paste) kode dari WhatsApp</span>
                            </p>
                            <p class="text-gray-500 flex items-center gap-1.5">
                                <i class="fa-solid fa-clock text-gray-400"></i>
                                <span>Berlaku 5 menit</span>
                            </p>
                        </div>
                        
                        @error('code')
                            <p class="text-sm text-red-600 flex items-center gap-1.5 mt-2">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>{{ $message }}</span>
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                        class="w-full flex justify-center items-center gap-2.5 py-4 px-6 border border-transparent rounded-xl shadow-lg shadow-emerald-500/20 text-base font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/50 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        <i class="fa-solid fa-check-circle text-xl"></i>
                        <span>Verifikasi Kode</span>
                    </button>

                    <!-- Back Link -->
                    <p class="text-center text-sm text-gray-600 pt-2">
                        Tidak menerima kode? 
                        <a href="{{ route('password.wa') }}" class="font-bold text-emerald-600 hover:text-emerald-700 transition-colors">Kirim ulang</a>
                    </p>

                </form>
            </div>
        </div>
    </div>

    <script>
        function otpForm() {
            return {
                otpCode: '',
                
                moveToNext(event, nextIndex) {
                    const value = event.target.value;
                    if (value && nextIndex <= 6) {
                        this.$refs['otp' + nextIndex].focus();
                    }
                    this.updateOtpCode();
                },
                
                moveToPrev(event, prevIndex) {
                    if (event.target.value === '' && prevIndex >= 1) {
                        this.$refs['otp' + prevIndex].focus();
                    }
                    this.updateOtpCode();
                },
                
                checkComplete() {
                    this.updateOtpCode();
                },
                
                updateOtpCode() {
                    this.otpCode = this.$refs.otp1.value + 
                                   this.$refs.otp2.value + 
                                   this.$refs.otp3.value + 
                                   this.$refs.otp4.value + 
                                   this.$refs.otp5.value + 
                                   this.$refs.otp6.value;
                }
            }
        }

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
        
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: @json(session('success')),
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
