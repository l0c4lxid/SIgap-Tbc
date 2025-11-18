@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header pb-1">
                    <h5 class="mb-0">Skrining Pasien: {{ $patient->name }}</h5>
                    <p class="text-sm text-muted mb-0">Jawab pertanyaan berikut untuk menilai risiko TBC.</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('kader.patients.screening.store', $patient) }}">
                        @csrf
                        <div class="space-y-4">
                            @foreach ($questions as $key => $question)
                                <div class="mb-4">
                                    <label class="form-label">{{ $loop->iteration }}. {{ $question }}</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $key }}" value="ya" id="{{ $key }}_ya" required>
                                            <label class="form-check-label" for="{{ $key }}_ya">Ya</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="{{ $key }}" value="tidak" id="{{ $key }}_tidak">
                                            <label class="form-check-label" for="{{ $key }}_tidak">Tidak</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="text-end mt-4">
                            <a href="{{ route('kader.patients') }}" class="btn btn-outline-secondary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Hasil Skrining</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
