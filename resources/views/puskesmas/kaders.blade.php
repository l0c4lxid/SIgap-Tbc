@extends('layouts.soft')

@section('subjudul', 'Data kader puskesmas')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div>
                <h5 class="font-bold text-xl text-gray-800 mb-1">Data Kader Mitra</h5>
                <p class="text-sm text-gray-500 mb-0">Daftar kader yang bekerja sama dengan puskesmas ini.</p>
            </div>
            <div class="w-full md:w-auto flex flex-col md:flex-row gap-3">
                <a href="{{ route('puskesmas.kaders.export.excel', request()->query()) }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2">
                    <i class="ri-file-excel-line"></i> Export Excel
                </a>
                <form method="GET" action="{{ route('puskesmas.kaders') }}" class="flex flex-col md:flex-row gap-2 w-full md:w-auto" data-auto-submit>
                    <div class="relative w-full md:w-64">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                        <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari nama / nomor HP" value="{{ $search ?? '' }}">
                    </div>
                    <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold">Cari</button>
                    @if ($search)
                        <a href="{{ route('puskesmas.kaders') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm font-semibold transition-colors text-center no-underline">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Kader</th>
                        <th>Kontak</th>
                        <th>Catatan</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Didaftarkan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = method_exists($kaders, 'firstItem') ? $kaders->firstItem() : null;
                    @endphp
                    @forelse ($kaders as $kader)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <a href="{{ route('puskesmas.kaders.show', $kader) }}" class="font-bold text-gray-800 hover:text-[var(--color-glass-primary)] transition-colors no-underline">
                                        {{ $kader->name }}
                                    </a>
                                    <span class="text-xs text-gray-500">{{ $kader->detail->organization ?? 'Kader' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm text-gray-600">{{ $kader->phone }}</span>
                            </td>
                            <td>
                                <span class="text-sm text-gray-600">{{ $kader->detail->notes ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                @if ($kader->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="text-xs text-gray-500">{{ $kader->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('puskesmas.kaders.show', $kader) }}" class="text-[var(--color-glass-primary)] hover:text-[var(--color-glass-secondary)] font-semibold text-sm no-underline">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">Belum ada kader mitra.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @php
            $hasPagination = method_exists($kaders, 'firstItem');
            $from = $hasPagination ? $kaders->firstItem() : ($kaders->count() ? 1 : 0);
            $to = $hasPagination ? $kaders->lastItem() : $kaders->count();
            $total = $hasPagination ? $kaders->total() : $kaders->count();
        @endphp
        
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6 border-t border-gray-200/50 pt-4">
            <p class="text-sm text-gray-500 m-0">
                Menampilkan <span class="font-bold">{{ $from }}</span> - <span class="font-bold">{{ $to }}</span> dari <span class="font-bold">{{ $total }}</span> kader
            </p>
            @if ($hasPagination)
                <div>
                    {{ $kaders->withQueryString()->onEachSide(1)->links('pagination.glass') }}
                </div>
            @endif
        </div>
    </div>
@endsection
