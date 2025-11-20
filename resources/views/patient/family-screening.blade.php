@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Skrining {{ $member->name }}</h5>
                        <p class="text-sm text-muted mb-0">Isi sesuai kondisi anggota keluarga ini.</p>
                    </div>
                    <a href="{{ route('patient.family') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('patient.family.screening.store', $member) }}">
                        @csrf
                        <div class="list-group">
                            @foreach ($questions as $name => $label)
                                <div class="list-group-item">
                                    <p class="mb-2 fw-semibold">{{ $label }}</p>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="{{ $name }}" id="{{ $name }}_ya" value="ya" required>
                                            <label class="form-check-label" for="{{ $name }}_ya">Ya</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="radio" class="form-check-input" name="{{ $name }}" id="{{ $name }}_tidak" value="tidak" required>
                                            <label class="form-check-label" for="{{ $name }}_tidak">Tidak</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-4">Kirim</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
