@extends('layouts.soft')

@section('content')
    <div class="glass-card p-6 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 border-b border-gray-200/50 pb-4">
            <div>
                 <div class="flex items-center gap-2 mb-1">
                     <a href="{{ route('pemda.screening-targets.index') }}" class="text-gray-500 hover:text-[var(--color-glass-primary)] transition">
                        <i class="ri-arrow-left-line text-xl"></i>
                     </a>
                     <h5 class="font-bold text-xl text-gray-800 mb-0">Detail Target Skrining</h5>
                 </div>
                 <p class="text-sm text-gray-500 mb-0 ml-7">Kelurahan {{ $target->kelurahan->name ?? '-' }}</p>
            </div>
            
             <div class="flex gap-2">
                 @if($target->status === 'active')
                    <a href="{{ route('pemda.screening-targets.edit', $target) }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 no-underline">
                        <i class="ri-edit-line"></i> Edit Target
                    </a>
                    
                    <form action="{{ route('pemda.screening-targets.destroy', $target) }}" method="POST" 
                        data-confirm="Apakah Anda yakin ingin mengarsipkan target ini? Data tidak akan hilang permanen." 
                        data-confirm-text="Ya, Arsipkan">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="glass-button bg-red-50 text-red-600 hover:bg-red-100 border-red-200 px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
                            <i class="ri-archive-line"></i> Arsipkan
                        </button>
                    </form>
                @else
                    <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 font-semibold text-sm">Diarsipkan</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4">
                <p class="text-xs font-bold text-blue-600 uppercase mb-1">Periode</p>
                <h5 class="text-lg font-bold text-blue-800 mb-0">
                    @if($target->period_type == 'monthly')
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $target->month)->translatedFormat('F Y') }}
                    @else
                        {{ $target->date_from?->format('d M') ?? '-' }} - {{ $target->date_to?->format('d M Y') ?? '-' }}
                    @endif
                </h5>
            </div>
             <div class="bg-green-50/50 border border-green-100 rounded-xl p-4">
                <p class="text-xs font-bold text-green-600 uppercase mb-1">Target Total</p>
                <div class="flex items-end gap-2">
                    <h5 class="text-2xl font-bold text-green-800 mb-0">{{ number_format($target->target_total) }}</h5>
                    <span class="text-xs text-green-600 mb-1">skrining</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Realisasi: {{ number_format($target->actual_total) }} ({{ $target->progress_percent }}%)
                </div>
            </div>
             <div class="bg-yellow-50/50 border border-yellow-100 rounded-xl p-4">
                 <p class="text-xs font-bold text-yellow-600 uppercase mb-1">Target Suspek</p>
                 <div class="flex items-end gap-2">
                    <h5 class="text-2xl font-bold text-yellow-800 mb-0">{{ number_format($target->target_suspek ?? 0) }}</h5>
                    <span class="text-xs text-yellow-600 mb-1">suspek</span>
                </div>
                 <div class="mt-2 text-xs text-gray-500">
                    Realisasi: {{ number_format($target->actual_suspek) }}
                </div>
            </div>


        </div>

        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="flex justify-between text-sm mb-1">
                <span class="font-semibold text-gray-600">Progress Kelurahan</span>
                <span class="font-bold text-[var(--color-glass-primary)]">{{ $target->progress_percent }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 overflow-hidden">
                <div class="bg-[var(--color-glass-primary)] h-4 rounded-full transition-all duration-500" style="width: {{ min(100, $target->progress_percent) }}%"></div>
            </div>
        </div>

        {{-- Allocations Table --}}
        <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
            <h5 class="font-bold text-lg text-gray-800 mb-0">Alokasi Target per Kader</h5>
            @if($target->status === 'active')
                <button type="button" onclick="toggleEditAllocations()" class="glass-button text-xs px-3 py-1.5 rounded flex items-center gap-1" id="btn-edit-allocations">
                    <i class="ri-edit-line"></i> Edit Alokasi Manual
                </button>
            @endif
        </div>

        <form action="{{ route('pemda.screening-targets.update-allocations', $target->id) }}" method="POST" id="form-allocations"
              data-confirm="Apakah Anda yakin ingin menyimpan perubahan alokasi ini?"
              data-confirm-text="Ya, Simpan">
            @csrf
            @method('PUT')
            
            <div class="overflow-x-auto">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th>RW</th>
                            <th class="text-center w-32">Target RW</th>
                            <th class="text-center w-32">Realisasi</th>
                            <th>Capaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rwAllocations as $rw)
                            <tr class="hover:bg-white/30 transition-colors">
                                <td>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 text-sm">RW {{ $rw['rw_code'] }}</span>
                                        <span class="text-xs text-gray-500">{{ $rw['kader_count'] }} Kader</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="view-mode">
                                        <span class="font-bold text-gray-700">{{ number_format($rw['allocated_total']) }}</span>
                                    </div>
                                    <div class="edit-mode hidden">
                                        {{-- RW Code as key --}}
                                        <input type="number" name="allocations[{{ $rw['rw_code'] }}][allocated_total]" value="{{ $rw['allocated_total'] }}" class="w-24 px-2 py-1 text-center rounded border border-gray-300 text-sm">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="font-bold text-gray-700">{{ number_format($rw['actual_total']) }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden min-w-[80px]">
                                            <div class="bg-[var(--color-glass-primary)] h-2 rounded-full" style="width: {{ min(100, $rw['progress_percent']) }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold text-gray-600 w-10 text-right">{{ $rw['progress_percent'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">Tidak ada data RW ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>



            <div class="edit-mode hidden mt-4 flex justify-end gap-2">
                <button type="button" onclick="toggleEditAllocations()" class="glass-button bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm font-semibold">Batal</button>
                <button type="submit" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script>
        function toggleEditAllocations() {
            const views = document.querySelectorAll('.view-mode');
            const edits = document.querySelectorAll('.edit-mode');
            const btn = document.getElementById('btn-edit-allocations');
            
            views.forEach(el => el.classList.toggle('hidden'));
            edits.forEach(el => el.classList.toggle('hidden'));
            
            if (btn) {
                btn.classList.toggle('hidden');
            }
        }
    </script>
@endsection
