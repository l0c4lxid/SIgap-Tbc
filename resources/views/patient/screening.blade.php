@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Skrining Mandiri TBC</h5>
                        <p class="text-sm text-muted mb-0">Jawab pertanyaan di bawah untuk memantau kondisi Anda.</p>
                    </div>
                    @if ($screening)
                        <span class="badge bg-gradient-success text-white">Sudah skrining</span>
                    @else
                        <span class="badge bg-gradient-warning text-dark">Belum skrining</span>
                    @endif
                </div>
                <div class="card-body">
                    @if ($screening)
                        <div class="alert alert-success">
                            Terima kasih! Anda mengirim skrining pada {{ $screening->created_at->format('d M Y H:i') }}.
                        </div>
                        <h6 class="text-sm text-muted mb-3">Ringkasan Jawaban</h6>
                        <ul class="list-group">
                            @foreach ($questions as $key => $label)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $label }}</span>
                                    <span class="badge {{ ($screening->answers[$key] ?? '') === 'ya' ? 'bg-gradient-danger' : 'bg-gradient-success' }}">
                                        {{ strtoupper($screening->answers[$key] ?? '-') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <form method="POST" action="{{ route('patient.screening.store') }}">
                            @csrf
                            <div class="list-group">
                                @foreach ($questions as $name => $label)
                                    <div class="list-group-item">
                                        <p class="mb-2 fw-semibold">{{ $label }}</p>
                                        <div class="d-flex gap-3">
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input me-1" name="{{ $name }}" value="ya" @checked(old($name) === 'ya') required>
                                                Ya
                                            </label>
                                            <label class="form-check-label">
                                                <input type="radio" class="form-check-input me-1" name="{{ $name }}" value="tidak" @checked(old($name) === 'tidak') required>
                                                Tidak
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-4">Kirim Skrining</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h6 class="mb-0">Petunjuk</h6>
                </div>
                <div class="card-body">
                    <p class="text-sm text-muted">Skrining mandiri hanya perlu dilakukan sekali. Jika ada gejala berlanjut setelah skrining, segera hubungi kader pendamping atau puskesmas terdekat.</p>
                    <ul class="text-sm text-muted ps-3 mb-0">
                        <li>Jawab dengan jujur sesuai kondisi Anda.</li>
                        <li>Data akan diteruskan ke kader pendamping Anda.</li>
                        <li>Jika ada jawaban "Ya", sistem akan memberi notifikasi kepada petugas.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
