@extends('layouts.soft')

@section('subjudul', 'Puskesmas mitra kader')

@section('content')
     <div class="glass-card p-6">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200/50">
             <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[var(--color-glass-primary)] to-[var(--color-glass-secondary)] flex items-center justify-center text-white shadow-lg">
                <i class="ri-hospital-line text-2xl"></i>
            </div>
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">Puskesmas Induk</h5>
                 <p class="text-sm text-gray-500 mb-0">Informasi puskesmas yang menaungi kader ini.</p>
            </div>
        </div>

        @if ($puskesmas)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                     <p class="text-xs uppercase font-bold text-gray-500 mb-1 tracking-wider">Nama Puskesmas</p>
                    <h6 class="font-bold text-2xl text-gray-800 mb-1">{{ $puskesmas->name }}</h6>
                    <p class="text-sm text-gray-500">{{ $puskesmas->detail->organization ?? 'Puskesmas' }}</p>
                </div>
                 <div>
                     <p class="text-xs uppercase font-bold text-gray-500 mb-1 tracking-wider">Kontak</p>
                    <div class="flex items-center gap-2 mb-2">
                         <i class="ri-phone-line text-gray-400"></i>
                         <span class="text-gray-800 font-medium">{{ $puskesmas->phone }}</span>
                    </div>
                     <div class="flex items-center gap-2">
                        <i class="ri-shield-check-line text-gray-400"></i>
                        <span class="text-sm text-gray-600">Status Akun:</span>
                         @if ($puskesmas->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">Belum Aktif</span>
                        @endif
                    </div>
                </div>
                <div class="md:col-span-2">
                     <p class="text-xs uppercase font-bold text-gray-500 mb-1 tracking-wider">Alamat</p>
                    <div class="flex items-start gap-2">
                        <i class="ri-map-pin-2-line text-gray-400 mt-0.5"></i>
                        <p class="text-gray-800 mb-0">{{ $puskesmas->detail->address ?? '-' }}</p>
                    </div>
                </div>
            </div>
        @else
             <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-center gap-3">
                <i class="ri-alert-line text-yellow-600 text-xl"></i>
                <span class="text-yellow-700 font-medium">Kader belum terhubung dengan puskesmas mana pun. Hubungi admin untuk mengatur relasi.</span>
            </div>
        @endif
    </div>
@endsection
