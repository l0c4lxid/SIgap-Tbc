@extends('layouts.soft')

@section('title', 'Pusat WhatsApp - Pemda')

@section('content')
<div class="h-[calc(100vh-8rem)] flex flex-col gap-4">

    <!-- Connection Status -->
    <div class="flex items-center justify-between bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-4 shrink-0">
        <div class="flex items-center gap-3">
            @php
                $isServiceUp = isset($nodeHealth['status']) && $nodeHealth['status'] && $nodeHealth['status'] !== 'error';
                $isWaConnected = $isServiceUp && ($nodeHealth['waConnected'] ?? false);
                
                $statusIcon = 'ri-signal-wifi-off-line';
                $statusBg = 'bg-red-50 text-red-500';
                $statusTitle = 'Layanan Offline';
                $statusDesc = 'Terputus: ' . ($nodeHealth['error'] ?? 'Tidak dapat menghubungi layanan');

                if ($isServiceUp) {
                    if ($isWaConnected) {
                        $statusIcon = 'ri-whatsapp-line';
                        $statusBg = 'bg-emerald-50 text-emerald-500';
                        $statusTitle = 'WhatsApp Terhubung';
                        $statusDesc = 'Siap mengirim pesan';
                    } else {
                        $statusIcon = 'ri-qr-code-line';
                        $statusBg = 'bg-amber-50 text-amber-500';
                        $statusTitle = 'Gateway Terhubung (Belum Scan)';
                        $statusDesc = 'Scan QR Code untuk mengaktifkan pengiriman';
                    }
                }
            @endphp

             <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl {{ $statusBg }}">
                <i class="{{ $statusIcon }}"></i>
            </div>
            <div>
                <h2 class="text-base font-bold text-gray-800 m-0">{{ $statusTitle }}</h2>
                <p class="text-xs text-gray-500 m-0">{{ $statusDesc }}</p>
            </div>
        </div>
        
        @if($isServiceUp)
            @if($isWaConnected)
                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Online
                </span>
            @else
                <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Belum Scan
                </span>
            @endif
        @else
             <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-red-500"></span> Offline
            </span>
        @endif
    </div>
    
    <!-- Header Stats (Collapsed on mobile, expanded on desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 shrink-0">
        <!-- Total -->
        <div class="bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl">
                <i class="ri-chat-history-line"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium m-0">Total</p>
                <p class="text-lg font-bold text-gray-800 m-0 leading-tight">{{ $stats['total'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Sent -->
        <div class="bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl">
                <i class="ri-check-double-line"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium m-0">Berhasil</p>
                <p class="text-lg font-bold text-gray-800 m-0 leading-tight">{{ $stats['sent'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl">
                <i class="ri-time-line"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium m-0">Antrian</p>
                <p class="text-lg font-bold text-gray-800 m-0 leading-tight">{{ $stats['pending'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Failed -->
        <div class="bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-3 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-xl">
                <i class="ri-error-warning-line"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium m-0">Gagal</p>
                <p class="text-lg font-bold text-gray-800 m-0 leading-tight">{{ $stats['failed'] ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Main Split View -->
    <div class="flex-1 flex gap-4 min-h-0">
        <!-- Left Column: List -->
        <div class="w-full lg:w-[400px] xl:w-[450px] flex flex-col bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden {{ isset($selectedMessage) ? 'hidden lg:flex' : 'flex' }}">
            
            <!-- Toolbar -->
            <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800 m-0">Pesan Keluar</h2>
                    <a href="{{ route('pemda.whatsapp.create') }}" class="btn-sm bg-blue-600 text-white rounded-lg px-3 py-1.5 text-xs font-bold hover:bg-blue-700 transition flex items-center gap-1 no-underline">
                        <i class="ri-add-line"></i> Baru
                    </a>
                </div>
                
                <form method="GET" action="{{ route('pemda.whatsapp.index') }}" class="flex gap-2">
                    <div class="relative flex-1">
                        <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor/isi..." class="w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                    </div>
                    <button type="submit" class="p-2 bg-gray-100 text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-200 transition cursor-pointer">
                        <i class="ri-filter-3-line"></i>
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'type', 'date_from']))
                        <a href="{{ route('pemda.whatsapp.index') }}" class="p-2 bg-red-50 text-red-600 rounded-lg border border-red-100 hover:bg-red-100 transition flex items-center justify-center no-underline">
                            <i class="ri-close-line"></i>
                        </a>
                    @endif
                </form>

                <!-- Status Filter Chips -->
                <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
                    <a href="{{ route('pemda.whatsapp.index', array_merge(request()->query(), ['status' => null])) }}" 
                       class="px-3 py-1 rounded-full text-[10px] font-bold whitespace-nowrap border transition-colors no-underline {{ !request('status') ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-500 border-gray-200 hover:border-gray-800' }}">
                        Semua
                    </a>
                    <a href="{{ route('pemda.whatsapp.index', array_merge(request()->query(), ['status' => 'pending'])) }}" 
                       class="px-3 py-1 rounded-full text-[10px] font-bold whitespace-nowrap border transition-colors no-underline {{ request('status') == 'pending' ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-white text-gray-500 border-gray-200 hover:border-amber-400' }}">
                        Pending
                    </a>
                    <a href="{{ route('pemda.whatsapp.index', array_merge(request()->query(), ['status' => 'sent'])) }}" 
                       class="px-3 py-1 rounded-full text-[10px] font-bold whitespace-nowrap border transition-colors no-underline {{ request('status') == 'sent' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-white text-gray-500 border-gray-200 hover:border-emerald-400' }}">
                        Berhasil
                    </a>
                    <a href="{{ route('pemda.whatsapp.index', array_merge(request()->query(), ['status' => 'failed'])) }}" 
                       class="px-3 py-1 rounded-full text-[10px] font-bold whitespace-nowrap border transition-colors no-underline {{ request('status') == 'failed' ? 'bg-red-100 text-red-800 border-red-200' : 'bg-white text-gray-500 border-gray-200 hover:border-red-400' }}">
                        Gagal
                    </a>
                </div>
            </div>

            <!-- List Content -->
            <div class="flex-1 overflow-y-auto">
                @if($messages->isEmpty())
                    <div class="h-full flex flex-col items-center justify-center text-center p-6">
                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                            <i class="ri-inbox-line text-2xl text-gray-300"></i>
                        </div>
                        <p class="text-sm text-gray-500">Tidak ada pesan ditemukan</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($messages as $msg)
                            <a href="{{ route('pemda.whatsapp.show', array_merge(request()->query(), ['outbox' => $msg->id])) }}" 
                               class="message-link block p-4 hover:bg-gray-50 transition-colors border-l-4 {{ (isset($selectedMessage) && $selectedMessage->id == $msg->id) ? 'bg-blue-50/50 border-blue-500' : 'border-transparent' }} no-underline group relative">
                                
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-bold text-gray-900 text-sm">{{ $msg->to_phone }}</span>
                                    <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $msg->created_at->format('d/m H:i') }}</span>
                                </div>
                                
                                <p class="text-xs text-gray-500 line-clamp-2 mb-2 leading-relaxed">
                                    {{ $msg->message }}
                                </p>

                                <div class="flex items-center justify-between">
                                    @php
                                        $statusClass = match($msg->status) {
                                            'sent' => 'text-emerald-600 bg-emerald-50',
                                            'pending' => 'text-amber-600 bg-amber-50',
                                            'failed' => 'text-red-600 bg-red-50',
                                            default => 'text-gray-600 bg-gray-50',
                                        };
                                        $statusIcon = match($msg->status) {
                                            'sent' => 'ri-check-double-line',
                                            'pending' => 'ri-time-line',
                                            'failed' => 'ri-error-warning-fill',
                                            default => 'ri-question-line',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold {{ $statusClass }}">
                                        <i class="{{ $statusIcon }}"></i> {{ ucfirst($msg->status) }}
                                    </span>

                                    <!-- Quick Actions (Hover) -->
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if(in_array($msg->status, ['failed', 'pending']))
                                            <form action="{{ route('pemda.whatsapp.retry', $msg) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-gray-200 text-amber-500 hover:bg-amber-50 cursor-pointer" title="Retry">
                                                    <i class="ri-refresh-line"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('pemda.whatsapp.destroy', $msg) }}" method="POST" onsubmit="return confirm('Hapus pesan ini?')" class="inline z-10">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-gray-200 text-red-500 hover:bg-red-50 cursor-pointer" title="Hapus">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($messages->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $messages->links('vendor.pagination.tailwind') }} 
                    <!-- Ensure you have a compact pagination view or standard tailwind one -->
                </div>
            @endif
        </div>

        <!-- Right Column: Detail -->
        <div id="detail-container" class="flex-1 bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden flex flex-col {{ isset($selectedMessage) ? 'flex' : 'hidden lg:flex' }}">
            @include('admin.whatsapp.detail-partial')
        </div>
    </div>
</div>

<style>
/* Hide scrollbar for Chrome, Safari and Opera */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
/* Hide scrollbar for IE, Edge and Firefox */
.no-scrollbar {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailContainer = document.getElementById('detail-container');
    const messageLinks = document.querySelectorAll('.message-link');
    
    messageLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // UI Update: Active State
            messageLinks.forEach(l => {
                l.classList.remove('bg-blue-50/50', 'border-blue-500');
                l.classList.add('border-transparent');
            });
            this.classList.remove('border-transparent');
            this.classList.add('bg-blue-50/50', 'border-blue-500');

            // Show loading state
            detailContainer.classList.remove('hidden');
            detailContainer.innerHTML = `
                <div class="flex-1 flex flex-col items-center justify-center bg-gray-50/30 p-8 text-center h-full">
                    <div class="w-16 h-16 border-4 border-blue-500/30 border-t-blue-500 rounded-full animate-spin mb-4"></div>
                    <p class="text-gray-500 font-medium">Memuat detail...</p>
                </div>
            `;

            // Update URL
            const url = this.href;
            history.pushState(null, '', url);

            // Fetch Data
            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                detailContainer.innerHTML = html;
            })
            .catch(err => {
                console.error('Error loading detail:', err);
                detailContainer.innerHTML = `
                    <div class="flex-1 flex flex-col items-center justify-center bg-red-50 p-8 text-center h-full">
                        <i class="ri-error-warning-fill text-4xl text-red-500 mb-2"></i>
                        <p class="text-red-700 font-bold">Gagal memuat data.</p>
                        <p class="text-red-500 text-sm">Silakan coba lagi nanti.</p>
                    </div>
                `;
            });
        });
    });

    // Handle Back Button (Popstate)
    window.addEventListener('popstate', function() {
        location.reload();
    });
});
</script>
@endpush
@endsection

