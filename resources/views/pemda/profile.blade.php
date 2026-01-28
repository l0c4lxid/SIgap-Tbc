@extends('layouts.soft')

@section('subjudul', 'Profil pemerintah daerah')

@section('content')
    <div class="glass-card p-6 mb-6">
        <h5 class="font-bold text-xl text-gray-800 mb-2">Dukungan untuk koordinasi pemda</h5>
        <p class="text-sm text-gray-500 mb-6">Profil yang lengkap membantu pelaporan lintas puskesmas dan sinkronisasi program daerah.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/40 border border-white/50">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-[var(--color-glass-primary)] shrink-0">
                    <i class="ri-user-settings-line text-xl"></i>
                </div>
                <div>
                     <p class="font-bold text-gray-800 mb-1">Kontak penanggung jawab</p>
                     <p class="text-xs text-gray-500 leading-relaxed mb-0">Mudah dihubungi saat diperlukan.</p>
                </div>
            </div>
            <div class="flex items-start gap-4 p-4 rounded-xl bg-white/40 border border-white/50">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                    <i class="ri-building-4-line text-xl"></i>
                </div>
                <div>
                     <p class="font-bold text-gray-800 mb-1">Data instansi jelas</p>
                     <p class="text-xs text-gray-500 leading-relaxed mb-0">Mendukung validasi dan laporan resmi.</p>
                </div>
            </div>
             <div class="flex items-start gap-4 p-4 rounded-xl bg-white/40 border border-white/50">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                    <i class="ri-shield-keyhole-line text-xl"></i>
                </div>
                <div>
                     <p class="font-bold text-gray-800 mb-1">Keamanan akun</p>
                     <p class="text-xs text-gray-500 leading-relaxed mb-0">Kontrol akses tetap terjaga.</p>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('pemda.profile.update') }}" id="pemdaProfileForm" data-original-phone="{{ $user->phone }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Detail Section --}}
            <div class="lg:col-span-2 glass-card p-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200/50">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-glass-primary)] to-[var(--color-glass-secondary)] flex items-center justify-center text-white shadow-lg shadow-green-500/30">
                        <i class="ri-id-card-line text-xl"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-lg text-gray-800 mb-0">Detail Penanggung Jawab</h5>
                        <p class="text-sm text-gray-500 mb-0">Perbarui identitas dan informasi Pemda.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Penanggung Jawab</label>
                        <input type="text" name="name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div>
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Instansi / Pemda</label>
                        <input type="text" name="organization" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('organization', $user->detail->organization ?? '') }}">
                    </div>
                    <div class="md:col-span-2">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap</label>
                        <input type="text" name="address" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('address', $user->detail->address ?? '') }}">
                    </div>
                    <div class="md:col-span-2">
                         <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                        <textarea name="notes" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all">{{ old('notes', $user->detail->notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Security Section --}}
            <div class="glass-card p-6 h-fit">
                 <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200/50">
                    <div class="w-10 h-10 rounded-lg bg-gray-800 flex items-center justify-center text-white shadow-lg">
                        <i class="ri-lock-password-line text-xl"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-lg text-gray-800 mb-0">Keamanan Akun</h5>
                        <p class="text-sm text-gray-500 mb-0">Ganti nomor login atau password.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP Login</label>
                         <input type="text" name="phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-gray-800 transition-all font-mono" value="{{ old('phone', $user->phone) }}" required>
                    </div>
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password baru</label>
                        <input type="password" name="password" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-gray-800 transition-all" autocomplete="new-password">
                    </div>
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi password</label>
                         <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-gray-800 transition-all" autocomplete="new-password">
                    </div>
                    <p class="text-xs text-gray-500 italic mt-2">Kosongkan password jika tidak ingin mengganti.</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-6">
            <button type="button" class="glass-button px-8 py-3 rounded-xl font-bold text-base shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all" onclick="confirmPemdaProfile()">
                Simpan Perubahan
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function confirmPemdaProfile() {
            const form = document.getElementById('pemdaProfileForm');
            if (!form) return;

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

            let message = 'Simpan perubahan profil Pemda?';
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
                confirmButtonColor: '#10B981',
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@endpush