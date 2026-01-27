@extends('layouts.soft')

@section('subjudul', 'Detail verifikasi pengguna')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Detail Pengguna</h5>
                    <p class="text-sm text-muted mb-0">Kelola identitas, status, dan kredensial pengguna.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('pemda.verification') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
                    <form method="POST" action="{{ route('pemda.verification.destroy', $user) }}" data-confirm="Hapus {{ $user->name }}?" data-confirm-text="Ya, hapus">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash me-1"></i> Hapus</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Informasi Pengguna</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pemda.verification.update', $user) }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Peran</label>
                            <input type="text" class="form-control" value="{{ $user->role->label() }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch ms-1 mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label">Aktifkan akun</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Instansi / Organisasi</label>
                            <input type="text" name="organization" class="form-control" value="{{ old('organization', $user->detail->organization ?? '') }}" {{ $user->role === \App\Enums\UserRole::Pemda ? 'disabled' : '' }}>
                            @error('organization')
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $user->detail->address ?? '') }}">
                            @error('address')
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        @if ($supervisorLabel)
                            <div class="col-12">
                                <label class="form-label">{{ $supervisorLabel }}</label>
                                <select name="supervisor_id" class="form-select">
                                    <option value="">Pilih</option>
                                    @foreach ($supervisorOptions as $option)
                                        <option value="{{ $option->id }}" {{ old('supervisor_id', $user->detail->supervisor_id ?? null) == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                                    @endforeach
                                </select>
                                @error('supervisor_id')
                                    <span class="text-danger text-xs">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                        <div class="col-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" rows="3" class="form-control">{{ old('notes', $user->detail->notes ?? '') }}</textarea>
                            @error('notes')
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Kredensial Login</h6>
                    <p class="text-xs text-muted mb-0">Ubah username (nomor HP) dan password.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pemda.verification.credentials', $user) }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-12">
                            <label class="form-label">Nomor HP (username)</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                            @error('phone')
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Password Baru (opsional)</label>
                            <input type="password" name="password" class="form-control" autocomplete="new-password">
                            @error('password')
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-dark">Simpan Kredensial</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header pb-0 d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">Ringkasan</h6>
                    <span class="badge {{ $user->is_active ? 'bg-gradient-success' : 'bg-gradient-warning text-dark' }}">{{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 text-sm">
                        <li class="d-flex justify-content-between py-1"><span>Nama</span><strong>{{ $user->name }}</strong></li>
                        <li class="d-flex justify-content-between py-1"><span>Peran</span><strong>{{ $user->role->label() }}</strong></li>
                        <li class="d-flex justify-content-between py-1"><span>Dibuat</span><strong>{{ $user->created_at->format('d M Y H:i') }}</strong></li>
                        <li class="d-flex justify-content-between py-1"><span>Instansi</span><strong>{{ $user->detail->organization ?? '-' }}</strong></li>
                        @if ($supervisorLabel)
                            <li class="d-flex justify-content-between py-1"><span>{{ $supervisorLabel }}</span><strong>{{ optional($user->detail?->supervisor)->name ?? '-' }}</strong></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            @if (session('status'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('status')),
                });
            @endif
        });
    </script>
@endpush
