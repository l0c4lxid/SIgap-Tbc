@extends('layouts.soft')

@section('subjudul', 'Detail kelurahan binaan')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div>
                 <div class="flex items-center gap-2 mb-1 text-sm text-gray-500">
                    <a href="{{ route('puskesmas.kelurahan') }}" class="hover:text-[var(--color-glass-primary)] transition-colors no-underline flex items-center gap-1">
                        <i class="ri-arrow-left-line"></i> Kembali
                    </a>
                    <span>/</span>
                    <span>Kelurahan</span>
                </div>
                <h5 class="font-bold text-xl text-gray-800 mb-1">{{ $kelurahan->name }}</h5>
                <p class="text-sm text-gray-500 mb-0">Data skrining dengan alamat pasien di wilayah kelurahan ini.</p>
            </div>
             <form method="GET" action="{{ route('puskesmas.kelurahan.show', $kelurahan) }}" class="flex w-full md:w-auto gap-2" data-auto-submit>
                <div class="relative flex-1 md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                     <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari nama, alamat, RT/RW..." value="{{ $search ?? '' }}">
                </div>
                <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold">Cari</button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white/40 rounded-xl p-4 border border-white/50">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Nama Kelurahan</p>
                <h6 class="font-bold text-lg text-gray-800 mb-1">{{ $kelurahan->name }}</h6>
                <p class="text-xs text-gray-500 mb-0">{{ optional($kelurahan->detail)->organization ?? '-' }}</p>
            </div>
            <div class="bg-white/40 rounded-xl p-4 border border-white/50">
                 <p class="text-xs text-gray-500 uppercase font-bold mb-1">Alamat Kelurahan</p>
                <p class="text-sm text-gray-800 mb-0">{{ optional($kelurahan->detail)->address ?? '-' }}</p>
            </div>
             <div class="bg-white/40 rounded-xl p-4 border border-white/50">
                 <p class="text-xs text-gray-500 uppercase font-bold mb-1">Total Skrining Sesuai Alamat</p>
                <h5 class="font-bold text-2xl text-[var(--color-glass-primary)] mb-1">{{ method_exists($screenings, 'total') ? number_format($screenings->total()) : $screenings->count() }}</h5>
                <p class="text-xs text-gray-500 mb-0">Alamat mengandung: {{ $kelurahan->name }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pasien</th>
                        <th>Kontak</th>
                        <th>Kader</th>
                        <th>Alamat</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : 1;
                    @endphp
                    @forelse ($screenings as $screening)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800">{{ $screening->patient_name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500">NIK: {{ $screening->patient_nik ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs text-gray-600">{{ $screening->patient_phone ?? '-' }}</span>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-700">{{ $screening->kader?->name ?? '-' }}</span>
                                    @if ($screening->kader)
                                        <span class="text-xs text-gray-400">Hub: {{ $screening->kader->phone ?? '-' }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                     <span class="text-xs text-gray-800">{{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}</span>
                                    <span class="text-[10px] text-gray-500">RT/RW {{ $screening->patient_address_rt ?? '-' }}/{{ $screening->patient_address_rw ?? '-' }} • {{ $screening->patient_address_kelurahan ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs text-gray-500">{{ $screening->created_at->format('d M Y H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Belum ada skrining dengan alamat yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @php
            $hasPagination = method_exists($screenings, 'firstItem');
            $from = $hasPagination ? $screenings->firstItem() : ($screenings->count() ? 1 : 0);
            $to = $hasPagination ? $screenings->lastItem() : $screenings->count();
            $total = $hasPagination ? $screenings->total() : $screenings->count();
        @endphp
        
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6 border-t border-gray-200/50 pt-4">
             <p class="text-sm text-gray-500 m-0">
                Menampilkan <span class="font-bold">{{ $from }}</span> - <span class="font-bold">{{ $to }}</span> dari <span class="font-bold">{{ $total }}</span> skrining
            </p>
            @if ($hasPagination)
                <div>
                     {{ $screenings->withQueryString()->onEachSide(1)->links('pagination.glass') }}
                </div>
            @endif
        </div>
    </div>
@endsection
