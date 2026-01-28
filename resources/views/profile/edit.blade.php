@extends('layouts.soft')

@section('subjudul', 'Perbarui profil akun')

@section('content')
    <div class="glass-card bg-green-50/50 border-green-100 p-4 mb-6 flex items-start gap-3">
        <i class="ri-information-line text-green-600 text-xl mt-0.5"></i>
        <div>
             <h6 class="font-bold text-green-800 text-sm mb-1">Silahkan update profil anda.</h6>
             <p class="text-sm text-green-700 mb-0">Lengkapi data agar koordinasi skrining dan tindak lanjut berjalan lebih cepat.</p>
        </div>
    </div>

    <div class="glass-card p-6 mb-6">
         <div class="border-b border-gray-200/50 pb-4 mb-4">
             <h5 class="font-bold text-lg text-gray-800 mb-1">Kenapa profil penting?</h5>
            <p class="text-sm text-gray-500 mb-0">Data profil yang lengkap membantu tim lintas peran mengenali penanggung jawab dan mempercepat tindak lanjut kasus.</p>
         </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-start gap-3 p-3 rounded-xl hover:bg-white/40 transition-all">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 flex-shrink-0">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
                <div>
                     <p class="font-bold text-gray-800 text-sm mb-1">Identitas jelas</p>
                    <p class="text-xs text-gray-500 mb-0">Memudahkan verifikasi dan pelaporan.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl hover:bg-white/40 transition-all">
                 <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
                    <i class="ri-map-pin-line"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-sm mb-1">Alamat akurat</p>
                    <p class="text-xs text-gray-500 mb-0">Koordinasi lapangan lebih cepat.</p>
                </div>
            </div>
            <div class="flex items-start gap-3 p-3 rounded-xl hover:bg-white/40 transition-all">
                 <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                    <i class="ri-shield-check-line"></i>
                </div>
                <div>
                    <p class="font-bold text-gray-800 text-sm mb-1">Keamanan data</p>
                    <p class="text-xs text-gray-500 mb-0">Akun terlindungi dan dapat ditelusuri.</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" id="generalProfileForm" data-original-phone="{{ $user->phone }}">
        @csrf
        @method('PATCH')
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Detail Profil --}}
            <div class="lg:col-span-7">
                <div class="glass-card h-full p-6">
                     <div class="flex items-center gap-4 mb-6 border-b border-gray-200/50 pb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[var(--color-glass-primary)] to-emerald-600 shadow-lg flex items-center justify-center text-white text-xl">
                            <i class="ri-user-settings-line"></i>
                        </div>
                        <div>
                             <h5 class="font-bold text-lg text-gray-800 mb-1">Detail Profil</h5>
                            <p class="text-sm text-gray-500 mb-0">Perbarui identitas dan informasi tambahan Anda.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                         <div class="md:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('name', $user->name) }}" required>
                        </div>
                         <div class="md:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Instansi / Organisasi</label>
                            <input type="text" name="organization" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('organization', $user->detail->organization ?? '') }}">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NIK</label>
                            <input type="text" name="nik" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('nik', $user->detail->nik ?? '') }}">
                        </div>
                        <div class="md:col-span-1">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap</label>
                            <input type="text" name="address" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('address', $user->detail->address ?? '') }}">
                        </div>
                        <div class="md:col-span-2">
                             <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                            <textarea name="notes" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all">{{ old('notes', $user->detail->notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Keamanan --}}
            <div class="lg:col-span-5">
                <div class="glass-card h-full p-6">
                     <div class="flex items-center gap-4 mb-6 border-b border-gray-200/50 pb-4">
                        <div class="w-12 h-12 rounded-xl bg-gray-800 shadow-lg flex items-center justify-center text-white text-xl">
                            <i class="ri-lock-password-line"></i>
                        </div>
                        <div>
                             <h5 class="font-bold text-lg text-gray-800 mb-1">Keamanan Akun</h5>
                            <p class="text-sm text-gray-500 mb-0">Ganti nomor login atau password.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                         <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP Login</label>
                            <input type="text" name="phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                            <input type="password" name="password" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" autocomplete="new-password">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" autocomplete="new-password">
                        </div>
                        <p class="text-xs text-gray-500 italic bg-gray-50 p-2 rounded-lg border border-gray-100">
                             Kosongkan jika tidak ingin mengganti password.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end mt-6">
            <button type="button" class="glass-button-cta px-8 py-3 rounded-xl font-bold text-white shadow-lg transform hover:-translate-y-0.5 transition-all" onclick="confirmGeneralProfile()">
                <i class="ri-save-3-line me-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmGeneralProfile() {
            const form = document.getElementById('generalProfileForm');
            if (! form) return;

            const originalPhone = form.dataset.originalPhone;
            const phoneField = form.querySelector('input[name="phone"]');
            const passwordField = form.querySelector('input[name="password"]');

            const changes = [];
            if (phoneField && originalPhone && phoneField.value !== originalPhone) {
                changes.push('nomor HP login akan diperbarui');
            }
            if (passwordField && passwordField.value.trim().length > 0) {
                changes.push('password akan diganti');
            }

            let message = 'Simpan perubahan profil?';
            if (changes.length) {
                message = 'Anda akan ' + changes.join(' dan ') + '. Tetap lanjutkan?';
            }

            Swal.fire({
                icon: 'question',
                title: 'Konfirmasi',
                text: message,
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'bg-[var(--color-glass-primary)] text-white px-4 py-2 rounded-lg font-bold',
                    cancelButton: 'bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-lg font-bold hover:bg-gray-50'
                },
                 buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@endpush
