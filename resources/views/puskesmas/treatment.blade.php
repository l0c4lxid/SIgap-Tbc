@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Pengelolaan Pengobatan Pasien</h5>
                        <p class="text-sm text-muted mb-0">Pantau progres pasien yang sedang ditindaklanjuti pengobatan TBC.</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Pasien</th>
                                    <th>Kader Penghubung</th>
                                    <th>Status Pengobatan</th>
                                    <th>Jadwal Kontrol</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($patients as $patient)
                                    <tr>
                                        <td>
                                            <h6 class="mb-0 text-sm">{{ $patient->name }}</h6>
                                            <p class="text-xs text-muted mb-0">{{ $patient->phone }}</p>
                                        </td>
                                        <td>
                                            <p class="text-sm fw-semibold mb-0">{{ $patient->detail->supervisor->name ?? '-' }}</p>
                                            <p class="text-xs text-muted mb-0">{{ $patient->detail->supervisor->phone ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <span class="badge bg-gradient-info">{{ ucfirst(str_replace('_', ' ', $patient->detail->treatment_status)) }}</span>
                                            @if ($patient->detail->treatment_notes)
                                                <p class="text-xs text-muted mb-0">{{ $patient->detail->treatment_notes }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($patient->detail->next_follow_up_at)
                                                <span class="text-xs text-muted">{{ \Carbon\Carbon::parse($patient->detail->next_follow_up_at)->format('d M Y') }}</span>
                                            @else
                                                <span class="text-xs text-muted">Belum dijadwalkan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#treatmentModal{{ $patient->id }}">
                                                Perbarui
                                            </button>
                                            <div class="modal fade" id="treatmentModal{{ $patient->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h6 class="modal-title">Perbarui Status {{ $patient->name }}</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form method="POST" action="{{ route('puskesmas.treatment.update', $patient) }}">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Status Pengobatan</label>
                                                                    <select name="status" class="form-select">
                                                                        @foreach (['contacted' => 'Sudah Dihubungi', 'scheduled' => 'Terjadwal Datang', 'in_treatment' => 'Sedang Berobat', 'recovered' => 'Selesai / Sembuh'] as $value => $label)
                                                                            <option value="{{ $value }}" @selected($patient->detail->treatment_status === $value)>{{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Jadwal Kontrol Berikutnya</label>
                                                                    <input type="date" name="next_follow_up_at" class="form-control" value="{{ $patient->detail->next_follow_up_at }}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Catatan</label>
                                                                    <textarea name="treatment_notes" rows="3" class="form-control">{{ $patient->detail->treatment_notes }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-success">Simpan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada pasien dalam pengobatan.</td>
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
