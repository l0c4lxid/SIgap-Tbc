@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Puskesmas Rujukan</h5>
                    <p class="text-sm text-muted mb-0">Informasi fasilitas kesehatan yang menangani pengobatan Anda.</p>
                </div>
                <div class="card-body">
                    @if ($puskesmas)
                        <div class="mb-3">
                            <label class="text-xs text-muted">Nama Puskesmas</label>
                            <p class="mb-0 fw-semibold">{{ $puskesmas->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-xs text-muted">Alamat</label>
                            <p class="mb-0">{{ optional($puskesmas->detail)->address ?? 'Belum tersedia' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="text-xs text-muted">Kontak</label>
                            <p class="mb-0">{{ $puskesmas->phone ?? '-' }}</p>
                        </div>
                        <div class="alert alert-info text-sm mb-0">
                            Jika ada perubahan jadwal, hubungi kader pendamping atau petugas Puskesmas di atas.
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            Data Puskesmas belum tersedia. Silakan hubungi kader pendamping Anda untuk memastikan jadwal pengobatan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header">
                    <h5 class="mb-0">Kader & Jadwal Pengobatan</h5>
                    <p class="text-sm text-muted mb-0">Kontak pendamping dan catatan jadwal terbaru.</p>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-xs text-muted">Kader Pendamping</label>
                        <p class="mb-0 fw-semibold">{{ $kader->name ?? 'Belum ada' }}</p>
                        <p class="text-sm text-muted mb-0">{{ $kader->phone ?? '-' }}</p>
                    </div>
                    @if ($latestTreatment)
                        <div class="mb-3">
                            <label class="text-xs text-muted">Status Pengobatan</label>
                            <p class="mb-0">
                                <span class="badge bg-gradient-warning text-dark">{{ ucfirst(str_replace('_', ' ', $latestTreatment->status)) }}</span>
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="text-xs text-muted">Jadwal Kontrol Berikutnya</label>
                            <p class="mb-0">{{ optional($latestTreatment->next_follow_up_at)->format('d M Y') ?? 'Belum dijadwalkan' }}</p>
                        </div>
                        @if ($latestTreatment->notes)
                            <div class="mb-3">
                                <label class="text-xs text-muted">Catatan Petugas</label>
                                <p class="mb-0">{{ $latestTreatment->notes }}</p>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-secondary mb-0">
                            Anda belum memiliki catatan pengobatan. Pastikan melakukan skrining atau hubungi kader untuk tindak lanjut.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
