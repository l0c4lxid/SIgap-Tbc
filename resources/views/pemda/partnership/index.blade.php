@extends('layouts.soft')

@section('subjudul', 'Kemitraan Wilayah')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div>
                <h5 class="font-bold text-xl text-gray-800 mb-1">Kemitraan Wilayah</h5>
                <p class="text-sm text-gray-500 mb-0">Kelola hubungan kerja antara Kelurahan dan Puskesmas.</p>
            </div>
            <form method="GET" action="{{ route('pemda.partnership.index') }}" class="flex w-full md:w-auto gap-2" data-auto-submit>
                <div class="relative flex-1 md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ri-search-line text-gray-400"></i>
                    </div>
                    <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari kelurahan..." value="{{ $search ?? '' }}">
                </div>
                <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto min-h-[400px]">
            <table class="glass-table w-full">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Kelurahan</th>
                        <th>Puskesmas Induk</th>
                        <th>Permintaan Pending</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = $kelurahans->firstItem();
                    @endphp
                    @forelse ($kelurahans as $kelurahan)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $firstNumber + $loop->index }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800">{{ $kelurahan->name }}</span>
                                    <span class="text-xs text-gray-500">{{ optional($kelurahan->detail)->address ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($kelurahan->detail?->supervisor)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                        {{ $kelurahan->detail->supervisor->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">Belum terhubung</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $pendingId = $kelurahan->detail?->pending_supervisor_id;
                                    $pendingPuskesmas = $pendingId ? $puskesmasList->firstWhere('id', $pendingId) : null;
                                @endphp

                                @if($pendingPuskesmas)
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-yellow-600">Dari: {{ $pendingPuskesmas->name }}</span>
                                    </div>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Approve Pending --}}
                                    @if($pendingPuskesmas)
                                        <form method="POST" action="{{ route('pemda.partnership.update', $kelurahan) }}" title="Setujui Permintaan">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="puskesmas_id" value="{{ $pendingPuskesmas->id }}">
                                            <button type="submit" class="w-8 h-8 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 flex items-center justify-center transition-colors">
                                                <i class="ri-check-line"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('pemda.partnership.edit', $kelurahan) }}" class="glass-button px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-2 no-underline hover:text-[var(--color-glass-primary)]">
                                        <i class="ri-settings-4-line"></i> Atur
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">Tidak ada data kelurahan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            {{ $kelurahans->links('pagination.glass') }}
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleDropdown(id) {
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(el => {
                if (el.id !== id) el.classList.add('hidden');
            });
            
            const dropdown = document.getElementById(id);
            dropdown.classList.toggle('hidden');
        }

        // Close on click outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.relative')) {
                 document.querySelectorAll('.dropdown-menu').forEach(el => {
                    el.classList.add('hidden');
                });
            }
        });
    </script>
    @endpush
@endsection
