@extends('layouts.soft')

@section('subjudul', 'Daftar skrining puskesmas')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="glass-card p-6">
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-8">
            <div>
                <h5 class="font-bold text-xl text-gray-800 mb-1">Monitoring Skrining Pasien</h5>
                <p class="text-sm text-gray-500 mb-0">Pantau laporan skrining yang dicatat kader mitra puskesmas.</p>
            </div>
            
            <form method="GET" action="{{ route('puskesmas.screenings') }}" class="w-full xl:w-auto flex flex-col gap-4" data-auto-submit>
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
                     <a href="{{ route('puskesmas.screenings.export.excel', request()->query()) }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold flex items-center justify-center gap-2 md:w-auto w-full no-underline">
                        <i class="ri-file-excel-line"></i> Export
                    </a>
                    
                    <div class="relative flex-1 md:min-w-[300px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ri-search-line text-gray-400"></i>
                        </div>
                        <input type="text" name="q" class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] text-sm" placeholder="Cari nama, NIK, alamat..." value="{{ $search ?? '' }}">
                    </div>
                    
                    <button type="submit" class="glass-button px-6 py-2 rounded-lg text-sm font-semibold">Terapkan</button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pasien</th>
                        <th>Kader</th>
                        <th>Status Skrining</th>
                        <th>Tanggal</th>
                        <th>Kontak</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : 1;
                    @endphp
                    @forelse ($screenings as $screening)
                        @php
                            $answers = collect($screening->answers ?? []);
                            $positiveCount = $answers
                                ->filter(fn ($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
                                ->count();

                            if ($positiveCount >= 1) {
                                $statusBadge = ['label' => 'Suspek TBC', 'class' => 'bg-red-100 text-red-800'];
                            } else {
                                $statusBadge = ['label' => 'Aman', 'class' => 'bg-green-100 text-green-800'];
                            }

                            $waNumber = preg_replace('/[^0-9]/', '', $screening->patient_phone ?? '');
                            if (Str::startsWith($waNumber, '0')) {
                                $waNumber = '62'.substr($waNumber, 1);
                            }

                            $waMessage = rawurlencode('Halo '.$screening->patient_name.'. Kami dari puskesmas ingin menindaklanjuti skrining TBC Anda. Silakan datang untuk pemeriksaan lanjutan.');
                            $waLink = $waNumber ? 'https://wa.me/'.$waNumber.'?text='.$waMessage : null;
                        @endphp
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                            <td>
                                <div class="flex flex-col">
                                    <a href="{{ route('puskesmas.screenings.show', $screening) }}" class="font-bold text-gray-800 hover:text-[var(--color-glass-primary)] transition-colors no-underline">
                                        {{ $screening->patient_name ?? '-' }}
                                    </a>
                                    <span class="text-xs text-gray-500">NIK: {{ $screening->patient_nik ?? '-' }}</span>
                                    <span class="text-[10px] text-gray-400 mt-1">
                                        {{ $screening->patient_address_kelurahan ?? '-' }} 
                                        (RT {{ $screening->patient_address_rt ?? '-' }} / RW {{ $screening->patient_address_rw ?? '-' }})
                                    </span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-700">{{ $screening->kader?->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500">{{ $screening->kader?->phone ?? '-' }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="flex flex-col items-start gap-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadge['class'] }}">
                                        {{ $statusBadge['label'] }}
                                    </span>
                                    <span class="text-xs text-gray-500 ml-1">{{ $positiveCount }} indikasi positif</span>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs text-gray-500 font-medium">{{ $screening->created_at->format('d M Y') }}</span>
                                <span class="text-[10px] text-gray-400 block">{{ $screening->created_at->format('H:i') }}</span>
                            </td>
                            <td>
                                <div class="flex flex-col gap-2">
                                    <span class="text-xs text-gray-600">{{ $screening->patient_phone ?? '-' }}</span>
                                    @if ($waLink)
                                        <a href="{{ $waLink }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 hover:text-green-700 no-underline bg-green-50 hover:bg-green-100 px-2 py-1 rounded-md transition-colors w-fit">
                                            <i class="ri-whatsapp-line"></i> Chat
                                        </a>
                                    @endif
                                </div>
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
