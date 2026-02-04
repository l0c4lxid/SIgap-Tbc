@extends('layouts.soft')

@section('title', 'Pusat WhatsApp - Pemda')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header & Actions -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 m-0">Pusat WhatsApp</h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-500">Status Service:</span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($nodeHealth['waConnected'] ?? false) ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ ($nodeHealth['waConnected'] ?? false) ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                    {{ ($nodeHealth['waConnected'] ?? false) ? 'Terhubung' : 'Terputus' }}
                </span>
            </div>
        </div>
        <a href="{{ route('admin.whatsapp.create') }}" class="btn-primary flex items-center gap-2 px-4 py-2 rounded-xl text-white font-semibold transition-all hover:scale-[1.02] active:scale-[0.98] shadow-lg shadow-blue-500/30 bg-gradient-to-r from-blue-500 to-indigo-600 no-underline">
            <i class="ri-add-line text-lg"></i>
            <span>Kirim Pesan Baru</span>
        </a>
    </div>

    <!-- Stats Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <!-- Total -->
        <div class="bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-2xl">
                <i class="ri-chat-history-line"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium m-0">Total Pesan</p>
                <p class="text-xl font-bold text-gray-800 m-0">{{ $stats['total'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Sent -->
        <div class="bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-2xl">
                <i class="ri-check-double-line"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium m-0">Berhasil</p>
                <p class="text-xl font-bold text-gray-800 m-0">{{ $stats['sent'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl">
                <i class="ri-time-line"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium m-0">Antrian</p>
                <p class="text-xl font-bold text-gray-800 m-0">{{ $stats['pending'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Failed -->
        <div class="bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-2xl">
                <i class="ri-error-warning-line"></i>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium m-0">Gagal</p>
                <p class="text-xl font-bold text-gray-800 m-0">{{ $stats['failed'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
        
        <!-- Toolbar & Filter -->
        <div class="p-4 border-b border-gray-100 bg-gray-50/50">
            <form method="GET" action="{{ route('admin.whatsapp.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Status</label>
                    <div class="relative">
                        <i class="ri-filter-3-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="status" class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tipe</label>
                    <div class="relative">
                        <i class="ri-price-tag-3-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select name="type" class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none appearance-none cursor-pointer">
                            <option value="">Semua Tipe</option>
                            <option value="notif" {{ request('type') == 'notif' ? 'selected' : '' }}>Notifikasi</option>
                            <option value="otp" {{ request('type') == 'otp' ? 'selected' : '' }}>OTP</option>
                        </select>
                    </div>
                </div>

                <div class="md:col-span-4">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Cari</label>
                    <div class="relative">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nomor HP atau isi pesan..." class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    </div>
                </div>

                <div class="md:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-900 transition-colors border-none cursor-pointer">
                        Filter
                    </button>
                    <a href="{{ route('admin.whatsapp.index') }}" class="px-3 py-2 border border-gray-200 bg-white text-gray-600 rounded-lg hover:bg-gray-50 transition-colors no-underline flex items-center justify-center">
                        <i class="ri-refresh-line"></i>
                    </a>
                </div>
            </form>
        </div>

        @if($messages->isEmpty())
            <div class="py-12 flex flex-col items-center justify-center text-center p-4">
                <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                    <i class="ri-inbox-line text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 m-0">Belum ada data pesan</h3>
                <p class="text-gray-500 text-sm mt-1 max-w-sm">Coba ubah filter pencarian atau buat pesan baru.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 border-b border-gray-100 text-xs uppercase font-medium text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Penerima</th>
                            <th class="px-6 py-4">Tipe</th>
                            <th class="px-6 py-4 w-1/3">Pesan</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($messages as $msg)
                        <tr class="hover:bg-gray-50/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        WA
                                    </div>
                                    <div class="font-medium text-gray-900">{{ $msg->to_phone }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($msg->type == 'otp')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-medium bg-purple-50 text-purple-700 border border-purple-100">
                                        OTP
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                        Notif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="line-clamp-2 text-gray-500 max-w-md m-0">{{ $msg->message }}</p>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = match($msg->status) {
                                        'sent' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'failed' => 'bg-red-50 text-red-700 border-red-100',
                                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                                    };
                                    $statusIcon = match($msg->status) {
                                        'sent' => 'ri-check-double-line',
                                        'pending' => 'ri-time-line',
                                        'failed' => 'ri-error-warning-line',
                                        default => 'ri-forbid-line',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusClasses }}">
                                    <i class="{{ $statusIcon }}"></i>
                                    {{ ucfirst($msg->status) }}
                                </span>
                                @if($msg->attempts > 0)
                                    <span class="text-[10px] text-gray-400 ml-1">({{ $msg->attempts }}x)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-gray-900 font-medium text-xs">{{ $msg->created_at->format('d M Y') }}</span>
                                    <span class="text-gray-400 text-[10px]">{{ $msg->created_at->format('H:i') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.whatsapp.show', $msg) }}" class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors" title="Detail">
                                        <i class="ri-eye-line text-lg"></i>
                                    </a>
                                    
                                    @if(in_array($msg->status, ['failed', 'pending']))
                                        <form action="{{ route('admin.whatsapp.retry', $msg) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors border-none bg-transparent cursor-pointer" title="Kirim Ulang">
                                                <i class="ri-refresh-line text-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($msg->status == 'pending')
                                        <form action="{{ route('admin.whatsapp.cancel', $msg) }}" method="POST" onsubmit="return confirm('Batalkan pengiriman?')" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition-colors border-none bg-transparent cursor-pointer" title="Batalkan">
                                                <i class="ri-close-circle-line text-lg"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($messages->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $messages->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
