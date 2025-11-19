@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between alignments-center">
                    <div>
                        <h5 class="mb-0">Pengelolaan Pengobatan Pasien</h5>
                        <p class="text-sm text-muted mb-0">Pantau progres pasien yang sedang ditindaklanjuti pengobatan TBC.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <ul class="nav nav-pills">
                            @foreach ($statuses as $value => $label)
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeStatus === $value ? 'active' : '' }}" href="{{ route('puskesmas.treatment', ['status' => $value]) }}">
                                        {{ $label }}
                                        <span class="badge bg-white text-dark ms-1">{{ $counts[$value] ?? 0 }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addTreatmentModal" {{ $eligiblePatients->isEmpty() ? 'disabled' : '' }}>
                            <i class="fa fa-plus me-1"></i> Tambah Pasien
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Pasien</th>
                                    <th>Kader Penghubung</th>
                                    <th>Hasil Skrining Terakhir</th>
                                    <th>Status Pengobatan</th>
                                    <th>Jadwal Kontrol</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($treatments as $treatment)
                                    @php
                                        $patient = $treatment->patient;
                                        $latestScreening = $patient->screenings->first();
                                        $positiveCount = collect($latestScreening->answers ?? [])->filter(fn ($answer) => $answer === 'ya')->count();
                                    @endphp
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
                                            @if ($latestScreening)
                                                <span class="badge bg-gradient-{{ $positiveCount >= 2 ? 'danger' : ($positiveCount === 1 ? 'warning text-dark' : 'success') }}">
                                                    {{ $positiveCount }} indikasi "Ya"
                                                </span>
                                                <p class="text-xs text-muted mb-0">{{ $latestScreening->created_at->format('d M Y H:i') }}</p>
                                            @else
                                                <span class="badge bg-gradient-secondary">Belum ada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-gradient-info">{{ ucfirst(str_replace('_', ' ', $treatment->status)) }}</span>
                                            @if ($treatment->notes)
                                                <p class="text-xs text-muted mb-0">{{ $treatment->notes }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($treatment->next_follow_up_at)
                                                <span class="text-xs text-muted">{{ $treatment->next_follow_up_at->format('d M Y') }}</span>
                                            @else
                                                <span class="text-xs text-muted">Belum dijadwalkan</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#treatmentModal{{ $treatment->id }}">
                                                    Perbarui
                                                </button>
                                                <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#historyModal{{ $patient->id }}">
                                                    Riwayat
                                                </button>
                                            </div>
                                            <div class="modal fade" id="treatmentModal{{ $treatment->id }}" tabindex="-1" aria-hidden="true">
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
                                                                            <option value="{{ $value }}" @selected($treatment->status === $value)>{{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Jadwal Kontrol Berikutnya</label>
                                                                    <input type="date" name="next_follow_up_at" class="form-control" value="{{ optional($treatment->next_follow_up_at)->toDateString() }}">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Catatan</label>
                                                                    <textarea name="treatment_notes" rows="3" class="form-control">{{ $treatment->notes }}</textarea>
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
                                            <div class="modal fade" id="historyModal{{ $patient->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h6 class="modal-title">Riwayat Pengobatan {{ $patient->name }}</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="table-responsive">
                                                                <table class="table align-items-center">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Status</th>
                                                                            <th>Catatan</th>
                                                                            <th>Jadwal Kontrol</th>
                                                                            <th>Diperbarui</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($patient->treatments()->latest()->get() as $history)
                                                                            <tr>
                                                                                <td>
                                                                                    <span class="badge bg-gradient-info">{{ ucfirst(str_replace('_', ' ', $history->status)) }}</span>
                                                                                    @if ($history->completed_at)
                                                                                        <span class="text-xs text-muted d-block">Selesai {{ $history->completed_at->format('d M Y') }}</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td>{{ $history->notes ?? '-' }}</td>
                                                                                <td>{{ optional($history->next_follow_up_at)->format('d M Y') ?? '-' }}</td>
                                                                                <td class="text-xs text-muted">{{ $history->updated_at->format('d M Y H:i') }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @foreach ($patient->familyMembers->whereNull('converted_user_id') as $member)
                                        @php
                                            $memberAnswers = collect($member->last_screening_answers ?? []);
                                            $memberPositive = $memberAnswers->filter(fn ($answer) => $answer === 'ya')->count();
                                            $memberScreenedAt = $member->last_screened_at;
                                            $memberStatusKey = $member->screening_status ?? 'pending';
                                            $familyStatus = $familyStatuses[$memberStatusKey] ?? [
                                                'label' => ucfirst(str_replace('_', ' ', $memberStatusKey)),
                                                'badge' => 'bg-gradient-secondary',
                                            ];
                                            $screeningColor = $memberPositive >= 2 ? 'danger' : ($memberPositive === 1 ? 'warning text-dark' : 'success');
                                        @endphp
                                        <tr class="align-middle bg-light">
                                            <td class="ps-5 border-start border-3 border-primary">
                                                <div>
                                                    <h6 class="mb-0 text-sm d-flex align-items-center gap-2">
                                                        <i class="fa-solid fa-user-group text-primary"></i>
                                                        {{ $member->name }}
                                                        <span class="badge bg-gradient-primary">Anggota Keluarga</span>
                                                    </h6>
                                                    <p class="text-xs text-muted mb-0">Relasi: {{ $member->relation ?? '-' }}{{ $member->phone ? ' • ' . $member->phone : '' }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs text-muted mb-0">Mengikuti {{ $patient->name }}</p>
                                                <p class="text-xs text-muted mb-0">Kader: {{ $patient->detail->supervisor->name ?? '-' }}</p>
                                            </td>
                                            <td>
                                                @if ($memberScreenedAt)
                                                    <span class="badge bg-gradient-{{ $screeningColor }}">
                                                        {{ $memberPositive }} indikasi "Ya"
                                                    </span>
                                                    <p class="text-xs text-muted mb-0">{{ $memberScreenedAt->format('d M Y H:i') }}</p>
                                                @else
                                                    <span class="badge bg-gradient-secondary">Belum disaring</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $familyStatus['badge'] ?? 'bg-gradient-secondary' }}">{{ $familyStatus['label'] }}</span>
                                                @if ($member->notes)
                                                    <p class="text-xs text-muted mb-0">{{ $member->notes }}</p>
                                                @endif
                                            </td>
                                            <td><span class="text-xs text-muted">-</span></td>
                                            <td>
                                                <a href="{{ route('puskesmas.patient.family', $patient) }}" class="btn btn-sm btn-outline-primary">
                                                    Kelola Anggota
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada pasien dalam pengobatan.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addTreatmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Tambah Pasien ke Pengobatan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('puskesmas.treatment.store') }}">
                    @csrf
                    <div class="modal-body">
                        @if ($eligiblePatients->isEmpty())
                            <div class="alert alert-secondary mb-0">Tidak ada pasien yang siap dimasukkan. Pastikan pasien sudah melakukan skrining.</div>
                        @else
                            <div class="mb-3">
                                <label class="form-label">Pilih Pasien</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">-- Pilih Pasien --</option>
                                    @foreach ($eligiblePatients as $candidate)
                                        <option value="{{ $candidate->id }}">{{ $candidate->name }} ({{ $candidate->detail->supervisor->name ?? 'Tanpa Kader' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status Pengobatan</label>
                                <select name="status" class="form-select">
                                    <option value="contacted">Sudah Dihubungi</option>
                                    <option value="scheduled">Terjadwal Datang</option>
                                    <option value="in_treatment">Sedang Berobat</option>
                                    <option value="recovered">Selesai / Sembuh</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jadwal Kontrol Berikutnya</label>
                                <input type="date" name="next_follow_up_at" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea name="treatment_notes" rows="3" class="form-control" placeholder="Catatan tindak lanjut"></textarea>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success" {{ $eligiblePatients->isEmpty() ? 'disabled' : '' }}>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
