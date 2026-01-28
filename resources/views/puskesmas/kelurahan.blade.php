@extends('layouts.soft')

@section('subjudul', 'Kelurahan binaan puskesmas')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
             <div>
                <h5 class="font-bold text-xl text-gray-800 mb-1">Kelurahan Binaan</h5>
                <p class="text-sm text-gray-500 mb-0">Daftar kelurahan yang terhubung ke puskesmas ini.</p>
            </div>
             <form method="GET" action="{{ route('puskesmas.kelurahan') }}" class="flex w-full md:w-auto gap-2" data-auto-submit>
                <div class="relative flex-1 md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari nama / alamat" value="{{ $search ?? '' }}">
                </div>
                <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold">Cari</button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white/40 rounded-xl p-4 border border-white/50">
                <p class="text-xs text-gray-500 uppercase font-bold mb-1">Total kelurahan</p>
                <h5 class="font-bold text-2xl text-gray-800 mb-0">{{ number_format($stats['total']) }}</h5>
            </div>
            <div class="bg-white/40 rounded-xl p-4 border border-white/50">
                 <p class="text-xs text-gray-500 uppercase font-bold mb-1">Aktif</p>
                <h5 class="font-bold text-2xl text-[var(--color-glass-primary)] mb-0">{{ number_format($stats['active']) }}</h5>
            </div>
            <div class="bg-white/40 rounded-xl p-4 border border-white/50">
                 <p class="text-xs text-gray-500 uppercase font-bold mb-1">Menunggu persetujuan</p>
                <h5 class="font-bold text-2xl text-yellow-500 mb-0">{{ number_format($stats['pending']) }}</h5>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Kelurahan</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Ditambahkan</th>
                        <th class="text-center">Kelola</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = method_exists($kelurahan, 'firstItem') ? $kelurahan->firstItem() : null;
                    @endphp
                    @forelse ($kelurahan as $row)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <a href="{{ route('puskesmas.kelurahan.show', $row) }}" class="font-bold text-gray-800 hover:text-[var(--color-glass-primary)] transition-colors no-underline">
                                        {{ $row->name }}
                                    </a>
                                    <span class="text-xs text-gray-500">{{ optional($row->detail)->organization ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-sm text-gray-600">{{ optional($row->detail)->address ?? '-' }}</span>
                            </td>
                            <td>
                                @php
                                    $isActive = optional($row->detail)->supervisor_id === auth()->id();
                                    $isPending = optional($row->detail)->pending_supervisor_id === auth()->id();
                                @endphp
                                @if ($isActive)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Aktif</span>
                                @elseif($isPending)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Menunggu</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">Tidak aktif</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-xs text-gray-500">{{ $row->created_at?->format('d M Y') }}</span>
                            </td>
                            <td class="text-center">
                                @if ($isActive)
                                    <form method="POST" action="{{ route('puskesmas.kelurahan.destroy', $row) }}" class="inline-block" data-confirm="Lepas kemitraan kelurahan ini?" data-confirm-text="Ya, lepas">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-xs border border-red-200 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors">Lepas</button>
                                    </form>
                                @elseif($isPending)
                                    <form method="POST" action="{{ route('puskesmas.kelurahan.approve', $row) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-700 font-semibold text-xs border border-green-200 hover:bg-green-50 px-3 py-1.5 rounded-lg transition-colors">Setujui</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">Tidak terhubung</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Belum ada kelurahan terhubung.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @php
            $hasPagination = method_exists($kelurahan, 'firstItem');
            $from = $hasPagination ? $kelurahan->firstItem() : ($kelurahan->count() ? 1 : 0);
            $to = $hasPagination ? $kelurahan->lastItem() : $kelurahan->count();
            $total = $hasPagination ? $kelurahan->total() : $kelurahan->count();
        @endphp
        
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6 border-t border-gray-200/50 pt-4">
             <p class="text-sm text-gray-500 m-0">
                Menampilkan <span class="font-bold">{{ $from }}</span> - <span class="font-bold">{{ $to }}</span> dari <span class="font-bold">{{ $total }}</span> kelurahan
            </p>
            @if ($hasPagination)
                <div>
                     {{ $kelurahan->withQueryString()->onEachSide(1)->links('pagination.glass') }}
                </div>
            @endif
        </div>
    </div>
@endsection
