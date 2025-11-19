@extends('layouts.soft')

@php
    use Illuminate\Support\Str;
    $statusBadges = [
        'pending' => 'bg-gradient-secondary',
        'in_progress' => 'bg-gradient-warning text-dark',
        'suspect' => 'bg-gradient-danger',
        'clear' => 'bg-gradient-success',
    ];
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header d-flex flex-wrap gap-3 justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Anggota Risiko – {{ $patient->name }}</h5>
                        <p class="text-sm text-muted mb-0">Kelola anggota keluarga yang berpotensi terpapar.</p>
                    </div>
                    <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left me-1"></i> Kembali</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Nama Anggota</th>
                                    <th>Kontak</th>
                                    <th>Status Skrining</th>
                                    <th>Catatan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($familyMembers as $member)
                                    @php
                                        $waNumber = preg_replace('/[^0-9]/', '', $member->phone ?? '');
                                        if ($waNumber && Str::startsWith($waNumber, '0')) {
                                            $waNumber = '62'.substr($waNumber, 1);
                                        }
                                        $waMessage = rawurlencode('Halo '.$member->name.' (anggota '.$patient->name.'). Kami dari Puskesmas ingin menjadwalkan skrining TBC.');
                                        $waLink = $waNumber ? 'https://wa.me/'.$waNumber.'?text='.$waMessage : null;
                                    @endphp
                                    <tr>
                                    <td>
                                        <strong>{{ $member->name }}</strong>
                                        <div class="text-xs text-muted">Relasi: {{ $member->relation ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="text-xs text-muted">{{ $member->phone ?? '-' }}</span>
                                        @if ($waLink)
                                            <a href="{{ $waLink }}" target="_blank" class="btn btn-sm btn-success d-block mt-2">
                                                <i class="fa-brands fa-whatsapp me-1"></i> Hubungi WA
                                            </a>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $statusBadges[$member->screening_status] ?? 'bg-secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $member->screening_status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $member->notes ?? '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('puskesmas.patient.family.update', [$patient, $member]) }}" class="d-flex flex-column gap-2">
                                            @csrf
                                            <select name="screening_status" class="form-select form-select-sm">
                                                @foreach ($statusBadges as $value => $class)
                                                    <option value="{{ $value }}" @selected($member->screening_status === $value)>{{ ucfirst(str_replace('_', ' ', $value)) }}</option>
                                                @endforeach
                                            </select>
                                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Catatan tambahan">{{ $member->notes }}</textarea>
                                            <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                                            @if ($member->converted_user_id)
                                                <span class="badge bg-gradient-success">Sudah jadi pasien</span>
                                            @endif
                                        </form>
                                    </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada anggota keluarga.</td>
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
