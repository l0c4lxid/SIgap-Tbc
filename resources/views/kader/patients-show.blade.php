@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header pb-1 d-flex align-items-center gap-2">
                    <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                        <i class="fa-solid fa-user text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $patient->name }}</h5>
                        <p class="text-sm text-muted mb-0">Detail pasien binaan.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <label class="text-xs text-muted mb-0">Status Akun:</label>
                        <form method="POST" action="{{ route('kader.patients.status', $patient) }}" data-confirm="Ubah status akun {{ $patient->name }}?" data-confirm-text="Ya, ubah" class="d-inline-block">
                            @csrf
                            <input type="hidden" name="status" value="{{ $patient->is_active ? 'inactive' : 'active' }}">
                            <div class="badge rounded-pill bg-light border d-inline-flex align-items-center gap-2 px-3 py-2">
                                <span class="text-xs text-muted {{ $patient->is_active ? '' : 'fw-bold text-danger' }}">Nonaktif</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $patient->is_active ? 'checked' : '' }}>
                                </div>
                                <span class="text-xs text-muted {{ $patient->is_active ? 'fw-bold text-success' : '' }}">Aktif</span>
                            </div>
                        </form>
                    </div>
                    <div class="mb-3">
                        <label class="text-xs text-muted">Nomor HP / Username</label>
                        <p class="mb-0 fw-semibold">{{ $patient->phone }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-xs text-muted">Password awal</label>
                        <p class="mb-0 fw-semibold">{{ $patient->detail->initial_password ?? '-' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-xs text-muted">NIK</label>
                        <p class="mb-0">{{ $patient->detail->nik ?? '-' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-xs text-muted">Alamat</label>
                        <p class="mb-0">{{ $patient->detail->address ?? '-' }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-xs text-muted">Catatan</label>
                        <p class="mb-0">{{ $patient->detail->notes ?? '-' }}</p>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('kader.patients') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-arrow-left me-1"></i> Kembali
                        </a>
                        <a href="{{ route('kader.patients.screening', $patient) }}" class="btn btn-primary">
                            Mulai Skrining
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header pb-1 d-flex align-items-center gap-2">
                    <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                        <i class="fa-solid fa-users text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Anggota Risiko</h5>
                        <p class="text-sm text-muted mb-0">Catat anggota keluarga yang perlu skrining lanjutan.</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('kader.patients.family.store', $patient) }}" class="mb-4">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Anggota</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hubungan</label>
                            <input type="text" name="relation" class="form-control" placeholder="Contoh: Istri / Anak">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" name="phone" class="form-control" placeholder="Opsional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Gejala atau riwayat kontak"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Tambah Anggota</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patient->familyMembers as $member)
                                    @php
                                        $badges = [
                                            'pending' => 'bg-gradient-secondary',
                                            'in_progress' => 'bg-gradient-warning text-dark',
                                            'suspect' => 'bg-gradient-danger',
                                            'clear' => 'bg-gradient-success',
                                        ];
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $member->name }}</strong>
                                            <div class="text-xs text-muted">{{ $member->relation ?? '-' }} • {{ $member->phone ?? '-' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $badges[$member->screening_status] ?? 'bg-secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $member->screening_status)) }}
                                            </span>
                                            @if ($member->notes)
                                                <p class="text-xs text-muted mb-0">{{ $member->notes }}</p>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted">Belum ada anggota keluarga.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
