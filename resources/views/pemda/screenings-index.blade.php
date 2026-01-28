@extends('layouts.soft')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-8">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Data Skrining Kader</h5>
                 <p class="text-sm text-gray-500 mb-0">Rekap seluruh skrining yang diinput oleh kader.</p>
            </div>
            
             <form method="GET" action="{{ route('pemda.screenings') }}" class="w-full xl:w-auto flex flex-col gap-4" data-auto-submit>
                 <div class="flex flex-col md:flex-row gap-4">
                     {{-- Date Filter Group --}}
                     <div class="flex gap-2 w-full md:w-auto">
                        <div class="flex-1 md:w-40">
                             <label class="text-xs text-gray-500 mb-1 block">Dari</label>
                            <input type="date" name="from" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)]" value="{{ $filters['from'] ?? now()->subMonth()->toDateString() }}">
                        </div>
                        <div class="flex-1 md:w-40">
                             <label class="text-xs text-gray-500 mb-1 block">Sampai</label>
                             <input type="date" name="to" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)]" value="{{ $filters['to'] ?? now()->toDateString() }}">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-3">
                     <a href="{{ route('pemda.screenings.export.excel', request()->query()) }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 md:w-auto w-full no-underline">
                        <i class="ri-file-excel-line"></i> Export
                    </a>
                    
                    <div class="relative flex-1 md:min-w-[300px]">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                        <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari nama / NIK / kader" value="{{ $search ?? '' }}">
                    </div>
                    
                    <button type="submit" class="glass-button px-6 py-2 rounded-lg text-sm font-semibold">Terapkan</button>
                </div>
            </form>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                <p class="text-xs font-bold text-blue-600 uppercase mb-1">Jumlah Skrining</p>
                <h5 class="text-2xl font-bold text-blue-800 mb-0">{{ number_format($summary['screenings'] ?? 0) }}</h5>
            </div>
             <div class="bg-green-50/50 border border-green-100 rounded-xl p-4">
                <p class="text-xs font-bold text-green-600 uppercase mb-1">Jumlah RT</p>
                <h5 class="text-2xl font-bold text-green-800 mb-0">{{ number_format($summary['rt'] ?? 0) }}</h5>
            </div>
             <div class="bg-yellow-50/50 border border-yellow-100 rounded-xl p-4">
                <p class="text-xs font-bold text-yellow-600 uppercase mb-1">Jumlah RW</p>
                <h5 class="text-2xl font-bold text-yellow-800 mb-0">{{ number_format($summary['rw'] ?? 0) }}</h5>
            </div>
             <div class="bg-purple-50/50 border border-purple-100 rounded-xl p-4">
                <p class="text-xs font-bold text-purple-600 uppercase mb-1">Jumlah Kelurahan</p>
                <h5 class="text-2xl font-bold text-purple-800 mb-0">{{ number_format($summary['kelurahan'] ?? 0) }}</h5>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Kader PJ</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
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
                                <span class="font-bold text-gray-800 text-sm">{{ $screening->patient_name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-sm text-gray-600">{{ $screening->patient_nik ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-sm font-semibold text-[var(--color-glass-primary)]">{{ $screening->kader?->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-sm text-gray-500">{{ $screening->created_at->format('d M Y H:i') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('pemda.screenings.show', $screening) }}" class="text-[var(--color-glass-primary)] hover:text-[var(--color-glass-secondary)] font-semibold text-sm no-underline flex items-center gap-1">
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
