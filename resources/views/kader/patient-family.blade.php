@extends('layouts.soft')

@php
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
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Anggota Keluarga {{ $patient->name }}</h5>
                        <p class="text-sm text-muted mb-0">Pantau progres skrining dan pengobatan anggota keluarga.</p>
                    </div>
                    <a href="{{ route('kader.screening.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kontak</th>
                                    <th>Status Skrining</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($familyMembers as $member)
                                    <tr>
                                        <td>
                                            <strong>{{ $member->name }}</strong>
                                            <p class="text-xs text-muted mb-0">Relasi: {{ $member->relation ?? '-' }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs text-muted mb-0">{{ $member->phone ?? '-' }}</p>
                                            @if ($member->nik)
                                                <p class="text-xs text-muted mb-0">NIK: {{ $member->nik }}</p>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadges[$member->screening_status] ?? 'bg-secondary' }}">
                                                {{ ucfirst(str_replace('_', ' ', $member->screening_status ?? 'pending')) }}
                                            </span>
                                            @if ($member->last_screened_at)
                                                <p class="text-xs text-muted mb-0">Terakhir: {{ $member->last_screened_at->format('d M Y H:i') }}</p>
                                            @endif
                                        </td>
                                        <td>{{ $member->notes ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Belum ada anggota keluarga.</td>
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
