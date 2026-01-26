@extends('layouts.soft')

@section('content')
    <div class="row kader-puskesmas-page">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h5 class="mb-0">Puskesmas Induk</h5>
                    <p class="text-sm text-muted mb-0">Informasi puskesmas yang menaungi kader ini.</p>
                </div>
                <div class="card-body">
                    @if ($puskesmas)
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-xs text-muted">Nama Puskesmas</h6>
                                <p class="mb-0 h5">{{ $puskesmas->name }}</p>
                                <p class="text-sm text-muted">{{ $puskesmas->detail->organization ?? 'Puskesmas' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-xs text-muted">Kontak</h6>
                                <p class="mb-0">Nomor HP: {{ $puskesmas->phone }}</p>
                                <p class="mb-0">Status Akun:
                                    @if ($puskesmas->is_active)
                                        <span class="badge bg-gradient-success text-white">Aktif</span>
                                    @else
                                        <span class="badge bg-gradient-warning text-dark">Belum Aktif</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-12">
                                <h6 class="text-uppercase text-xs text-muted">Alamat</h6>
                                <p class="mb-0">{{ $puskesmas->detail->address ?? '-' }}</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            Kader belum terhubung dengan puskesmas mana pun. Hubungi admin untuk mengatur relasi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
