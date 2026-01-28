@extends('layouts.soft')

@section('subjudul', 'Data kader kelurahan')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Kader di Wilayah Kelurahan</h5>
                 <p class="text-sm text-gray-500 mb-0">Daftar kader yang ditugaskan oleh puskesmas mitra kelurahan ini.</p>
            </div>
            
            <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
                 <a href="{{ route('kelurahan.kaders.export.excel', request()->only('q')) }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 no-underline w-full md:w-auto">
                    <i class="ri-file-excel-line"></i> Export
                </a>
                
                <form method="GET" action="{{ route('kelurahan.kaders') }}" class="flex w-full md:w-auto gap-2" data-auto-submit>
                    <div class="relative flex-1 md:w-64">
                         <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                        <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari nama / nomor HP" value="{{ $search ?? '' }}">
                    </div>
                    <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold">Cari</button>
                </form>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kader</th>
                        <th>Puskesmas Induk</th>
                        <th>Area</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = $kaders->firstItem();
                    @endphp
                    @forelse ($kaders as $kader)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <a href="{{ route('kelurahan.kaders.show', $kader) }}" class="font-bold text-gray-800 hover:text-[var(--color-glass-primary)] transition-colors no-underline">
                                        {{ $kader->name }}
                                    </a>
                                     <span class="text-xs text-gray-500">HP: {{ $kader->phone }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs text-gray-600">{{ $kader->detail->supervisor->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="text-xs text-gray-600">{{ $kader->detail->area ?? '-' }}</span>
                            </td>
                            <td>
                                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $kader->is_active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $kader->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center items-center gap-2">
                                     <a href="{{ route('kelurahan.kaders.show', $kader) }}" class="text-gray-500 hover:text-[var(--color-glass-primary)] transition-colors" title="Detail">
                                        <i class="ri-eye-line text-lg"></i>
                                    </a>
                                    <form method="POST" action="{{ route('kelurahan.kaders.status', $kader) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $kader->is_active ? 'inactive' : 'active' }}">
                                        <button type="submit" class="border-0 bg-transparent p-0 cursor-pointer transition-colors {{ $kader->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700' }}" title="{{ $kader->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="{{ $kader->is_active ? 'ri-close-circle-line' : 'ri-checkbox-circle-line' }} text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                             <td colspan="6" class="text-center py-8 text-gray-500">Belum ada kader terdata untuk kelurahan ini.</td>
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
