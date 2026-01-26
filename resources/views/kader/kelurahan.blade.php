@extends('layouts.soft')

@section('content')
    <div class="container-fluid py-3">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="mb-1">Mitra</h5>
                <p class="text-sm text-muted mb-0">Informasi puskesmas induk dan daftar kelurahan mitra kader.</p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="icon icon-sm icon-shape bg-gradient-info text-white rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fa fa-hospital"></i>
                    </span>
                    <div>
                        <h6 class="mb-0">Puskesmas Induk</h6>
                        <p class="text-xs text-muted mb-0">Info puskesmas yang menaungi kader.</p>
                    </div>
                </div>
                @if (!$hasPuskesmas)
                    <div class="d-flex align-items-center gap-2 text-muted">
                        <i class="ri-information-line"></i>
                        <span class="text-sm">Puskesmas induk belum ditetapkan.</span>
                    </div>
                @else
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <span class="text-info"><i class="ri-building-4-line"></i></span>
                                <div>
                                    <div class="text-xs text-muted">Nama Puskesmas</div>
                                    <div class="fw-semibold">{{ $puskesmas?->name ?? '-' }}</div>
                                    <div class="text-sm text-muted">{{ optional($puskesmas?->detail)->organization ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <span class="text-info"><i class="ri-map-pin-2-line"></i></span>
                                <div>
                                    <div class="text-xs text-muted">Alamat</div>
                                    <div class="text-sm">{{ optional($puskesmas?->detail)->address ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <span class="text-info"><i class="ri-phone-line"></i></span>
                                <div>
                                    <div class="text-xs text-muted">Kontak</div>
                                    <div class="text-sm">{{ $puskesmas?->phone ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <span class="text-info"><i class="ri-shield-check-line"></i></span>
                                <div>
                                    <div class="text-xs text-muted">Status</div>
                                    <span class="badge bg-gradient-success">Aktif</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="icon icon-sm icon-shape bg-gradient-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                        <i class="fa fa-map-marker-alt"></i>
                    </span>
                    <div>
                        <h6 class="mb-0">Kelurahan Mitra</h6>
                        <p class="text-xs text-muted mb-0">Kelurahan tempat kader bertugas.</p>
                    </div>
                </div>

                @if (!$hasPuskesmas)
                    <p class="text-sm text-muted mb-0">Puskesmas induk belum ditetapkan.</p>
                @elseif (!$kelurahan)
                    <p class="text-sm text-muted mb-0">Kelurahan mitra belum ditemukan.</p>
                @else
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <span class="text-primary"><i class="fa fa-building"></i></span>
                                <div>
                                    <div class="text-xs text-muted">Nama Kelurahan</div>
                                    <div class="fw-semibold">{{ $kelurahan->name }}</div>
                                    <div class="text-sm text-muted">{{ optional($kelurahan->detail)->organization ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <span class="text-primary"><i class="fa fa-map-marker-alt"></i></span>
                                <div>
                                    <div class="text-xs text-muted">Alamat</div>
                                    <div class="text-sm">{{ optional($kelurahan->detail)->address ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2">
                                <span class="text-primary"><i class="fa fa-phone"></i></span>
                                <div>
                                    <div class="text-xs text-muted">Kontak</div>
                                    <div class="text-sm">{{ $kelurahan->phone ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
