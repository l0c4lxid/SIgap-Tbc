@extends('layouts.soft')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8">
            <div class="flex-1">
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Daftar Skrining Pasien</h5>
                 <p class="text-sm text-gray-500 mb-0">Kelola laporan skrining yang Anda catat di lapangan.</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3 w-full lg:w-auto">
                 <a href="{{ route('kader.screening.create') }}" class="glass-button-cta px-4 py-2.5 rounded-xl font-bold text-sm shadow-lg hover:shadow-green-500/30 flex items-center justify-center gap-2 no-underline text-white w-full md:w-auto">
                    <i class="ri-add-line text-lg"></i> Tambah Skrining
                </a>
                
                 <a href="{{ route('kader.screening.export.excel') }}" class="glass-button px-4 py-2.5 rounded-xl font-bold text-sm flex items-center justify-center gap-2 no-underline w-full md:w-auto">
                    <i class="ri-file-excel-line text-lg"></i> Export
                </a>
                 
                <form method="GET" action="{{ route('kader.screening.index') }}" class="relative w-full md:w-64" data-auto-submit>
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                     <input type="text" name="q" class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm shadow-sm transition-all" placeholder="Cari..." value="{{ $search ?? '' }}">
                 </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th class="text-center w-16">No.</th>
                        <th>Pasien</th>
                        <th>NIK</th>
                        <th class="text-center">Jawaban Ya</th>
                        <th class="text-center">Waktu</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : null;
                    @endphp
                    @forelse ($screenings as $screening)
                        @php
                            $positive = collect($screening->answers ?? [])
                                ->filter(fn ($ans, $key) => str_starts_with((string) $key, 'gejala_') && $ans === 'ya')
                                ->count();
                        @endphp
                        <tr class="hover:bg-white/30 transition-colors">
                            <td class="text-center font-bold text-gray-500">{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                            <td>
                                 <a href="{{ route('kader.screening.show', $screening) }}" class="font-bold text-gray-800 hover:text-[var(--color-glass-primary)] transition-colors no-underline">
                                    {{ $screening->patient_name ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <span class="text-sm text-gray-600 font-mono">{{ $screening->patient_nik ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                 <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold {{ $positive ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }}">
                                    {{ $positive }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-xs text-gray-500">{{ $screening->created_at->format('d M Y H:i') }}</span>
                            </td>
                            <td class="text-center">
                                 <a href="{{ route('kader.screening.show', $screening) }}" class="text-[var(--color-glass-primary)] hover:text-green-700 font-semibold text-sm no-underline inline-flex items-center gap-1">
                                    Detail <i class="ri-arrow-right-line"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Belum ada skrining tercatat.</td>
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
