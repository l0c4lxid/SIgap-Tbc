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
    </div>
@endsection
