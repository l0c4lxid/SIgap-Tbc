@extends('layouts.soft')

@section('content')
    <form method="POST" action="{{ route('pemda.profile.update') }}" id="pemdaProfileForm" data-original-phone="{{ $user->phone }}">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header pb-1 d-flex align-items-center gap-2">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="fa-solid fa-address-card text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Detail Penanggung Jawab</h5>
                            <p class="text-sm text-muted mb-0">Perbarui identitas dan informasi Pemda.</p>
                        </div>
                    </div>
                    <hr class="horizontal dark opacity-10 my-0">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Penanggung Jawab</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Instansi / Pemda</label>
                                <input type="text" name="organization" class="form-control" value="{{ old('organization', $user->detail->organization ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Alamat Lengkap</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address', $user->detail->address ?? '') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Catatan</label>
                                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $user->detail->notes ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header pb-1 d-flex align-items-center gap-2">
                        <div class="icon icon-shape bg-gradient-dark shadow text-center border-radius-md">
                            <i class="fa-solid fa-shield-halved text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">Keamanan Akun</h5>
                            <p class="text-sm text-muted mb-0">Ganti nomor login atau password.</p>
                        </div>
                    </div>
                    <hr class="horizontal dark opacity-10 my-0">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nomor HP Login</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password baru</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                        </div>
                        <div>
                            <label class="form-label">Konfirmasi password</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                        <p class="text-xs text-muted mt-3">Kosongkan password jika tidak ingin mengganti.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-end mt-3">
            <button type="button" class="btn btn-primary px-5" onclick="confirmPemdaProfile()">Simpan perubahan</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function confirmPemdaProfile() {
            const form = document.getElementById('pemdaProfileForm');
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
            }).then(result => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
@endpush
