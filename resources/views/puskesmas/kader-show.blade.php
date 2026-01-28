@extends('layouts.soft')

@section('subjudul', 'Detail kader puskesmas')

@section('content')
    <div class="mb-6">
        <a href="{{ route('puskesmas.kaders') }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 no-underline">
            <i class="ri-arrow-left-line"></i> Kembali ke daftar kader
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Profile Card --}}
        <div class="lg:col-span-2 glass-card p-6 h-full">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h5 class="font-bold text-xl text-gray-800 mb-1">{{ $kader->name }}</h5>
                    <p class="text-sm text-gray-500 mb-0">Detail kader mitra puskesmas Anda.</p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $kader->is_active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    {{ $kader->is_active ? 'Aktif' : 'Tidak Aktif' }}
                </span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-bold mb-1">Nomor HP</p>
                    <p class="text-gray-800 font-semibold mb-0">{{ $kader->phone }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-bold mb-1">Organisasi/Instansi</p>
                    <p class="text-gray-800 font-semibold mb-0">{{ $kader->detail->organization ?? 'Kader' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-bold mb-1">Catatan</p>
                    <p class="text-gray-800 mb-0">{{ $kader->detail->notes ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wide font-bold mb-1">Terdaftar sejak</p>
                    <p class="text-gray-800 mb-0">{{ $kader->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>

        {{-- Action Card --}}
        <div class="glass-card p-6 h-full flex flex-col justify-between">
            <div>
                 <h6 class="font-bold text-lg text-gray-800 mb-4 border-b border-gray-100 pb-2">Kelola Status</h6>
                 <p class="text-sm text-gray-600 mb-6">Aktif/nonaktifkan akses kader ini ke aplikasi.</p>
            </div>
            
            <form method="POST" action="{{ route('puskesmas.kaders.status', $kader) }}" data-confirm="Apakah anda yakin ingin mengubah status aktif kader ini?" data-confirm-text="Ya, ubah status">
                @csrf
                <input type="hidden" name="status" value="{{ $kader->is_active ? 'inactive' : 'active' }}">
                <button type="submit" class="w-full py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl {{ $kader->is_active ? 'bg-white text-red-500 border border-red-200 hover:bg-red-50' : 'glass-button' }}">
                    {{ $kader->is_active ? 'Nonaktifkan Kader' : 'Aktifkan Kader' }}
                </button>
            </form>
        </div>
    </div>

    {{-- Screenings List --}}
    <div class="glass-card p-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
            <div>
                 <h6 class="font-bold text-lg text-gray-800 mb-1">Skrining yang Dicatat</h6>
                 <p class="text-sm text-gray-500 mb-0">Daftar skrining yang diinput oleh kader ini.</p>
            </div>
             <span class="bg-gray-100 text-gray-600 text-xs font-bold px-3 py-1 rounded-full">
                {{ number_format($totalScreenings) }} total
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="glass-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pasien</th>
                        <th>NIK</th>
                        <th>Alamat</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $firstNumber = method_exists($screenings, 'firstItem') ? $screenings->firstItem() : 1;
                    @endphp
                    @forelse ($screenings as $screening)
                        @php
                            $positiveCount = collect($screening->answers ?? [])
                                ->filter(fn ($answer, $key) => str_starts_with((string) $key, 'gejala_') && $answer === 'ya')
                                ->count();
                        @endphp
                        <tr class="hover:bg-white/30 transition-colors">
                            <td>{{ $firstNumber ? $firstNumber + $loop->index : $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('puskesmas.screenings.show', $screening) }}" class="font-bold text-gray-800 hover:text-[var(--color-glass-primary)] no-underline transition-colors">
                                    {{ $screening->patient_name ?? '-' }}
                                </a>
                            </td>
                            <td class="text-gray-600">{{ $screening->patient_nik ?? '-' }}</td>
                            <td class="text-gray-600">
                                {{ $screening->patient_address_domisili ?? $screening->patient_address ?? '-' }}
                            </td>
                            <td class="text-gray-600">{{ $screening->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $positiveCount ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                    {{ $positiveCount ? 'Suspek' : 'Aman' }}
                                </span>
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

        @if (method_exists($screenings, 'firstItem'))
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mt-6 border-t border-gray-200/50 pt-4">
                 <p class="text-sm text-gray-500 m-0">
                    Menampilkan <span class="font-bold">{{ $screenings->firstItem() ?? 0 }}</span> - <span class="font-bold">{{ $screenings->lastItem() ?? 0 }}</span> dari <span class="font-bold">{{ $screenings->total() }}</span> skrining
                </p>
                <div>
                     {{ $screenings->withQueryString()->onEachSide(1)->links('pagination.glass') }}
                </div>
            </div>
        @endif
    </div>

    {{-- Removed redundant session script --}}
@endsection
