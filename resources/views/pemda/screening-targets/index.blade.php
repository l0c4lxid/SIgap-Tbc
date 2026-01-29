@extends('layouts.soft')

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-8">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Target Skrining Berjenjang</h5>
                 <p class="text-sm text-gray-500 mb-0">Kelola target skrining untuk Kelurahan dan Kader.</p>
            </div>
            
             <div class="flex flex-col md:flex-row gap-3 w-full xl:w-auto">
                 <a href="{{ route('pemda.screening-targets.create') }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 md:w-auto w-full no-underline">
                    <i class="ri-add-line"></i> Buat Target Baru
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('pemda.screening-targets.index') }}" class="mb-6" data-auto-submit>
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 md:max-w-xs">
                     <label class="text-xs text-gray-500 mb-1 block">Kelurahan</label>
                     <select name="kelurahan_id" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)]">
                        <option value="">Semua Kelurahan</option>
                        @foreach($kelurahans as $kelurahan)
                            <option value="{{ $kelurahan->id }}" {{ request('kelurahan_id') == $kelurahan->id ? 'selected' : '' }}>
                                {{ $kelurahan->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 md:max-w-xs">
                     <label class="text-xs text-gray-500 mb-1 block">Bulan (Untuk Target Bulanan)</label>
                    <input type="month" name="month" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)]" value="{{ request('month') }}">
                </div>
                <div class="flex-1 md:max-w-xs">
                     <label class="text-xs text-gray-500 mb-1 block">Status</label>
                     <select name="status" class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-white/50 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)]">
                        <option value="active" {{ request('status', 'active') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                        <option value="">Semua</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="glass-button px-6 py-2 rounded-lg text-sm font-semibold h-[38px]">Filter</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>Kelurahan</th>
                        <th>Periode</th>
                        <th class="text-center">Target Total</th>
                        <th class="text-center">Realisasi</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($targets as $target)
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>
                                <div class="flex flex-col">
                                    <span class="font-bold text-gray-800 text-sm">{{ $target->kelurahan->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500">Mode: {{ $target->allocation_mode == 'auto_even' ? 'Otomatis' : 'Manual' }}</span>
                                </div>
                            </td>
                            <td>
                                @if($target->period_type == 'monthly')
                                    <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $target->month)->translatedFormat('F Y') }}
                                    </span>
                                @else
                                    <div class="flex flex-col text-xs text-gray-600">
                                        <span>{{ $target->date_from->format('d M Y') }}</span>
                                        <span>s/d {{ $target->date_to->format('d M Y') }}</span>
                                    </div>
                                @endif
                            </td>
                            <td class="text-center font-bold text-gray-700">
                                {{ number_format($target->target_total) }}
                                @if($target->target_suspek)
                                    <div class="text-xs text-gray-500 font-normal">Suspek: {{ number_format($target->target_suspek) }}</div>
                                @endif
                            </td>
                            <td class="text-center font-bold text-gray-700">
                                {{ number_format($target->actual_total ?? 0) }}
                                @if($target->target_suspek)
                                    <div class="text-xs text-gray-500 font-normal">Suspek: {{ number_format($target->actual_suspek ?? 0) }}</div>
                                @endif
                            </td>
                            <td class="w-48">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2.5 overflow-hidden">
                                        <div class="bg-[var(--color-glass-primary)] h-2.5 rounded-full" style="width: {{ min(100, $target->progress_percent ?? 0) }}%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-[var(--color-glass-primary)]">{{ $target->progress_percent ?? 0 }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $target->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($target->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('pemda.screening-targets.show', $target) }}" class="glass-button text-xs px-3 py-1.5 rounded flex items-center gap-1">
                                        Detail
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">Belum ada target skrining dibuat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-6">
            {{ $targets->withQueryString()->links('pagination.glass') }}
        </div>
    </div>
@endsection
