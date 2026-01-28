@extends('layouts.soft')

@section('subjudul', 'Daftar puskesmas mitra')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Puskesmas Mitra</h5>
                 <p class="text-sm text-gray-500 mb-0">Pilih puskesmas induk jika belum terhubung. Hubungan aktif ditandai pada kartu.</p>
            </div>
            <form method="GET" action="{{ route('kelurahan.puskesmas') }}" class="flex w-full md:w-auto gap-2" data-auto-submit>
                <div class="relative flex-1 md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari nama / alamat" value="{{ $search ?? '' }}">
                </div>
                <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold">Cari</button>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Puskesmas</th>
                        <th>Kontak</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = $puskesmasList->firstItem();
                    @endphp
                    @forelse ($puskesmasList as $puskesmas)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800">{{ $puskesmas->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $puskesmas->detail->organization ?? 'Puskesmas' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs text-gray-600">HP Admin: {{ $puskesmas->phone }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-gray-600">{{ $puskesmas->detail->address ?? '-' }}</span>
                            </td>
                            <td>
                                @php
                                    $isActive = $currentPuskesmasId === $puskesmas->id;
                                    $isPending = optional($kelurahan->detail)->pending_supervisor_id === $puskesmas->id;
                                @endphp
                                @if ($isActive)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Mitra aktif</span>
                                @elseif($isPending)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Menunggu persetujuan</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-800">Belum terhubung</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($isActive)
                                    <form method="POST" action="{{ route('kelurahan.puskesmas.detach', $puskesmas) }}"
                                        data-confirm="Lepas kemitraan dengan {{ $puskesmas->name }}?"
                                        data-confirm-text="Lepas"
                                        data-cancel-text="Batal"
                                        class="inline-block">
                                        @csrf
                                        <button type="submit" class="text-red-500 hover:text-red-700 font-semibold text-xs border border-red-200 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors">
                                            Lepas mitra
                                        </button>
                                    </form>
                                @elseif($isPending)
                                    <span class="text-xs text-gray-400 italic">Menunggu...</span>
                                @else
                                    <form method="POST" action="{{ route('kelurahan.puskesmas.request', $puskesmas) }}" class="inline-block">
                                        @csrf
                                        <button type="submit" class="text-[var(--color-glass-primary)] hover:text-green-700 font-semibold text-xs border border-green-200 hover:bg-green-50 px-3 py-1.5 rounded-lg transition-colors">Ajukan sebagai induk</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Belum ada puskesmas mitra.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @php
            $hasPagination = method_exists($puskesmasList, 'firstItem');
            $from = $hasPagination ? $puskesmasList->firstItem() : ($puskesmasList->count() ? 1 : 0);
            $to = $hasPagination ? $puskesmasList->lastItem() : $puskesmasList->count();
            $total = $hasPagination ? $puskesmasList->total() : $puskesmasList->count();
        @endphp
        
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6 border-t border-gray-200/50 pt-4">
             <p class="text-sm text-gray-500 m-0">
                Menampilkan <span class="font-bold">{{ $from }}</span> - <span class="font-bold">{{ $to }}</span> dari <span class="font-bold">{{ $total }}</span> puskesmas
            </p>
            @if ($hasPagination)
                <div>
                     {{ $puskesmasList->withQueryString()->onEachSide(1)->links('pagination.glass') }}
                </div>
            @endif
        </div>
    </div>
@endsection
