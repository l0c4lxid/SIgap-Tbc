@extends('layouts.soft')

@section('subjudul', 'Detail kader kelurahan')

@section('content')
     <div class="mb-6">
        <a href="{{ route('kelurahan.kaders') }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 no-underline">
            <i class="ri-arrow-left-line"></i> Kembali ke Data Kader
        </a>
    </div>

    <div class="glass-card p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h4 class="font-bold text-2xl text-gray-800 mb-1">{{ $kader->name }}</h4>
                <div class="text-sm text-gray-500 flex flex-wrap gap-4">
                     <span class="flex items-center gap-1"><i class="ri-phone-line"></i> {{ $kader->phone }}</span>
                     <span class="flex items-center gap-1"><i class="ri-hospital-line"></i> {{ $kader->detail->supervisor->name ?? '-' }}</span>
                </div>
            </div>
             <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $kader->is_active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                {{ $kader->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white/40 rounded-xl p-6 border border-white/50 shadow-sm">
             <p class="text-xs text-gray-500 uppercase font-bold mb-1">Pasien Tercatat</p>
            <h4 class="font-bold text-2xl text-gray-800 mb-0">{{ number_format($screeningSummary['total_patients']) }}</h4>
        </div>
        <div class="bg-white/40 rounded-xl p-6 border border-white/50 shadow-sm">
             <p class="text-xs text-gray-500 uppercase font-bold mb-1">Total Skrining</p>
            <h4 class="font-bold text-2xl text-[var(--color-glass-primary)] mb-0">{{ number_format($screeningSummary['total_screenings']) }}</h4>
        </div>
         <div class="bg-white/40 rounded-xl p-6 border border-white/50 shadow-sm">
             <p class="text-xs text-gray-500 uppercase font-bold mb-1">Suspek TBC</p>
            <h4 class="font-bold text-2xl text-red-500 mb-0">{{ number_format($screeningSummary['suspect']) }}</h4>
        </div>
    </div>

    <div class="glass-card p-6">
         <div class="mb-6 border-b border-gray-200/50 pb-4">
            <h5 class="font-bold text-lg text-gray-800 mb-1">Skrining Terbaru</h5>
            <p class="text-sm text-gray-500 mb-0">Ringkasan skrining yang dicatat kader ini.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pasien</th>
                        <th>Nomor HP</th>
                        <th>Alamat</th>
                        <th>Tanggal Skrining</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($screenings as $screening)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-bold text-gray-800 text-sm">{{ $screening->patient_name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-sm text-gray-600">{{ $screening->patient_phone ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                     <span class="text-sm text-gray-600">{{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}</span>
                                    <span class="text-xs text-gray-400">RT/RW {{ $screening->patient_address_rt ?? '-' }}/{{ $screening->patient_address_rw ?? '-' }} • {{ $screening->patient_address_kelurahan ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm text-gray-600">{{ $screening->created_at?->format('d M Y') ?? '-' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">Belum ada skrining tercatat untuk kader ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
