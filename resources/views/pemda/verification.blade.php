@extends('layouts.soft')

@section('subjudul', 'Validasi pengguna')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-8">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Verifikasi Pengguna SITUBA</h5>
                 <p class="text-sm text-gray-500 mb-0">Kelola status aktif pengguna sesuai kebutuhan wilayah.</p>
            </div>
            
            <div class="flex flex-col gap-4 w-full xl:w-auto">
                 {{-- Search & Filter Form --}}
                 <form method="GET" action="{{ route('pemda.verification') }}" class="flex flex-col md:flex-row gap-2 w-full" data-auto-submit>
                    <div class="w-full md:w-48">
                         <select name="role" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)]">
                            <option value="">Semua Peran</option>
                            @foreach ($roleOptions as $option)
                              <option value="{{ $option['value'] }}" {{ $selectedRole === $option['value'] ? 'selected' : '' }}>
                                {{ $option['label'] }}
                              </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="relative flex-1 md:min-w-[250px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                        <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari nama / nomor HP" value="{{ $search ?? '' }}">
                    </div>

                    <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold">Cari</button>
                </form>

                 {{-- Bulk Action Form --}}
                <form method="POST" action="{{ route('pemda.verification.bulk-status') }}" class="flex flex-col md:flex-row gap-2 w-full" data-confirm="Terapkan perubahan status massal?" data-confirm-text="Ya, terapkan">
                    @csrf
                    <input type="hidden" name="role" value="{{ $selectedRole }}">
                    
                     <div class="flex-1">
                        <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)]">
                            <option value="active">Aktifkan Semua</option>
                            <option value="inactive">Nonaktifkan Semua</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow-md transition-colors flex items-center justify-center gap-2">
                        <i class="ri-flashlight-line"></i> Terapkan
                    </button>
                </form>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Peran</th>
                        <th class="text-center">Dibuat</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse ($records as $user)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ ($records->firstItem() ?? 0) + $loop->index }}</td>
                            <td>
                                <a href="{{ route('pemda.verification.show', $user) }}" class="flex items-center gap-3 no-underline group">
                                     <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-[var(--color-glass-primary)] group-hover:text-white transition-colors">
                                        <i class="ri-user-line"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-bold text-gray-800 text-sm mb-0 group-hover:text-[var(--color-glass-primary)] transition-colors">{{ $user->name }}</h6>
                                    </div>
                                </a>
                            </td>
                            <td>
                                 <p class="text-xs font-bold text-gray-700 mb-0">{{ $user->role->label() }}</p>
                                <p class="text-xs text-gray-500 mb-0">{{ $user->detail->organization ?? '-' }}</p>
                            </td>
                            <td class="text-center">
                                 <span class="text-xs text-gray-500">{{ $user->created_at->format('d M Y') }}</span>
                            </td>
                            <td class="text-center">
                                 <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <form method="POST" action="{{ route('pemda.verification.status', $user) }}" class="inline-block" data-confirm="Ubah status {{ $user->name }}?" data-confirm-text="Ya, ubah">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $user->is_active ? 'inactive' : 'active' }}">
                                    <button type="submit" class="border-0 bg-transparent p-0 cursor-pointer transition-colors {{ $user->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-500 hover:text-green-700' }}" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i class="{{ $user->is_active ? 'ri-toggle-fill' : 'ri-toggle-line' }} text-2xl align-middle"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                             <td colspan="6" class="text-center py-8 text-gray-500">Belum ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @php
            $hasPagination = method_exists($records, 'firstItem');
            $from = $hasPagination ? $records->firstItem() : ($records->count() ? 1 : 0);
            $to = $hasPagination ? $records->lastItem() : $records->count();
            $total = $hasPagination ? $records->total() : $records->count();
        @endphp
        
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6 border-t border-gray-200/50 pt-4">
             <p class="text-sm text-gray-500 m-0">
                Menampilkan <span class="font-bold">{{ $from }}</span> - <span class="font-bold">{{ $to }}</span> dari <span class="font-bold">{{ $total }}</span> pengguna
            </p>
            @if ($hasPagination)
                <div>
                     {{ $records->withQueryString()->onEachSide(1)->links('pagination.glass') }}
                </div>
            @endif
        </div>
    </div>
@endsection
