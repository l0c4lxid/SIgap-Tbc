@extends('layouts.soft')

@section('subjudul', 'Wilayah kelurahan kader')

@section('content')
    <div class="glass-card p-6 mb-6">
         <h5 class="font-bold text-xl text-gray-800 mb-1">Mitra</h5>
         <p class="text-sm text-gray-500 mb-0">Informasi puskesmas induk dan daftar kelurahan mitra kader.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Puskesmas Induk Card --}}
        <div class="glass-card p-6 h-full">
            <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-200/50">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-[var(--color-glass-primary)]">
                    <i class="ri-hospital-line text-xl"></i>
                </div>
                <div>
                     <h6 class="font-bold text-lg text-gray-800 mb-0">Puskesmas Induk</h6>
                    <p class="text-xs text-gray-500 mb-0">Info puskesmas yang menaungi kader.</p>
                </div>
            </div>

            @if (!$hasPuskesmas)
                 <div class="flex items-center gap-2 text-gray-500 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <i class="ri-information-line text-lg"></i>
                    <span class="text-sm">Puskesmas induk belum ditetapkan.</span>
                </div>
            @else
                <div class="space-y-4">
                     <div class="flex items-start gap-3">
                        <div class="text-[var(--color-glass-primary)] mt-0.5"><i class="ri-building-4-line"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0 font-medium">Nama Puskesmas</p>
                             <p class="font-bold text-gray-800 text-base mb-0">{{ $puskesmas?->name ?? '-' }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ optional($puskesmas?->detail)->organization ?? '-' }}</p>
                        </div>
                    </div>
                     <div class="flex items-start gap-3">
                         <div class="text-[var(--color-glass-primary)] mt-0.5"><i class="ri-map-pin-2-line"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0 font-medium">Alamat</p>
                             <p class="text-sm text-gray-800 mb-0">{{ optional($puskesmas?->detail)->address ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                         <div class="text-[var(--color-glass-primary)] mt-0.5"><i class="ri-phone-line"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0 font-medium">Kontak</p>
                             <p class="text-sm text-gray-800 mb-0">{{ $puskesmas?->phone ?? '-' }}</p>
                        </div>
                    </div>
                     <div class="flex items-start gap-3">
                         <div class="text-[var(--color-glass-primary)] mt-0.5"><i class="ri-shield-check-line"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0 font-medium">Status</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 mt-1">Aktif</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Kelurahan Mitra Card --}}
        <div class="glass-card p-6 h-full">
             <div class="flex items-center gap-3 mb-4 pb-3 border-b border-gray-200/50">
                 <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                    <i class="ri-map-pin-line text-xl"></i>
                </div>
                <div>
                     <h6 class="font-bold text-lg text-gray-800 mb-0">Kelurahan Mitra</h6>
                    <p class="text-xs text-gray-500 mb-0">Kelurahan tempat kader bertugas.</p>
                </div>
            </div>

             @if (!$hasPuskesmas)
                <p class="text-sm text-gray-500">Puskesmas induk belum ditetapkan.</p>
            @elseif (!$kelurahan)
                 <div class="flex items-center gap-2 text-gray-500 bg-gray-50 p-4 rounded-xl border border-gray-100">
                     <i class="ri-error-warning-line text-lg"></i>
                    <span class="text-sm">Kelurahan mitra belum ditemukan.</span>
                </div>
            @else
                 <div class="space-y-4">
                     <div class="flex items-start gap-3">
                        <div class="text-blue-600 mt-0.5"><i class="ri-community-line"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0 font-medium">Nama Kelurahan</p>
                             <p class="font-bold text-gray-800 text-base mb-0">{{ $kelurahan->name }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ optional($kelurahan->detail)->organization ?? '-' }}</p>
                        </div>
                    </div>
                     <div class="flex items-start gap-3">
                         <div class="text-blue-600 mt-0.5"><i class="ri-map-pin-2-line"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0 font-medium">Alamat</p>
                             <p class="text-sm text-gray-800 mb-0">{{ optional($kelurahan->detail)->address ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                         <div class="text-blue-600 mt-0.5"><i class="ri-phone-line"></i></div>
                        <div>
                            <p class="text-xs text-gray-500 mb-0 font-medium">Kontak</p>
                             <p class="text-sm text-gray-800 mb-0">{{ $kelurahan->phone ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
