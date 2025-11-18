@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Tambahkan Anggota Risiko</h5>
                    <p class="text-sm text-muted mb-0">Catat anggota keluarga yang perlu skrining lanjutan.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('patient.family.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama Anggota</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hubungan</label>
                            <input type="text" name="relation" class="form-control" placeholder="Contoh: Suami / Istri / Anak">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nomor HP</label>
                            <input type="text" name="phone" class="form-control" placeholder="Opsional">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Gejala atau riwayat kontak"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Simpan Anggota</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Daftar Anggota</h5>
                    <p class="text-sm text-muted mb-0">Pantau status skrining tiap anggota keluarga.</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Relasi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($familyMembers as $member)
                                    <tr>
                                        <td>
                                            <strong>{{ $member->name }}</strong>
                                            <div class="text-xs text-muted">{{ $member->phone ?? '-' }}</div>
                                        </td>
                                        <td>{{ $member->relation ?? '-' }}</td>
                                        <td>
                                            @php
                                                $badges = [
                                                    'pending' => 'bg-gradient-secondary',
                                                    'in_progress' => 'bg-gradient-warning text-dark',
                                                    'suspect' => 'bg-gradient-danger',
                                                    'clear' => 'bg-gradient-success',
                                                ];
                                            @endphp
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
                                        <td colspan="3" class="text-center text-muted">Belum ada anggota keluarga.</td>
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
