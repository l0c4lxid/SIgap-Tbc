@if(isset($selectedMessage))
    <!-- Detail Header -->
    <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-white sticky top-0 z-10">
        <div class="flex items-center gap-3">
            <a href="{{ route('pemda.whatsapp.index', request()->except('outbox')) }}" class="lg:hidden p-2 -ml-2 text-gray-500 hover:bg-gray-100 rounded-lg">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <div>
                <h3 class="text-base font-bold text-gray-800 m-0">Detail Pesan #{{ $selectedMessage->id }}</h3>
                <p class="text-xs text-gray-500 m-0">{{ $selectedMessage->created_at->format('d F Y, H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($selectedMessage->status === 'pending')
                <form action="{{ route('pemda.whatsapp.cancel', $selectedMessage) }}" method="POST" onsubmit="return confirm('Batalkan?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg text-xs font-bold hover:bg-red-100 transition">
                        Batalkan
                    </button>
                </form>
            @endif
             @if(in_array($selectedMessage->status, ['failed', 'pending']))
                <form action="{{ route('pemda.whatsapp.retry', $selectedMessage) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold hover:bg-amber-100 transition">
                        Retry
                    </button>
                </form>
            @endif
             <form action="{{ route('pemda.whatsapp.destroy', $selectedMessage) }}" method="POST" onsubmit="return confirm('Hapus pesan ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition" title="Hapus">
                    <i class="ri-delete-bin-line text-lg"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Detail Content -->
    <div class="flex-1 overflow-y-auto p-6 bg-gray-50/30">
        
        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Status</p>
                @php
                    $statusColor = match($selectedMessage->status) {
                        'sent' => 'text-emerald-600',
                        'pending' => 'text-amber-600',
                        'failed' => 'text-red-600',
                        default => 'text-gray-600',
                    };
                @endphp
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ str_replace('text-', 'bg-', $statusColor) }}"></span>
                    <span class="font-bold {{ $statusColor }}">{{ ucfirst($selectedMessage->status) }}</span>
                </div>
                @if($selectedMessage->attempts > 0)
                    <p class="text-xs text-gray-400 mt-1">{{ $selectedMessage->attempts }}x percobaan</p>
                @endif
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Tipe</p>
                <span class="font-bold text-gray-800">{{ $selectedMessage->type == 'otp' ? 'OTP Code' : 'Notifikasi' }}</span>
            </div>
            @if($selectedMessage->sent_at)
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <p class="text-[10px] uppercase font-bold text-gray-400 mb-1">Terkirim</p>
                <span class="font-bold text-emerald-600">{{ $selectedMessage->sent_at->format('H:i:s') }}</span>
            </div>
            @endif
        </div>

        <!-- Message Bubble -->
        <div class="mb-6">
             <h4 class="text-xs font-bold text-gray-500 uppercase mb-3 px-1">Konten Pesan</h4>
             <div class="bg-[#e5ddd5] rounded-xl p-4 border border-gray-200 shadow-inner min-h-[120px]" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); opacity: 0.95;">
                 <div class="bg-white p-3 rounded-lg shadow-sm rounded-tr-none max-w-2xl float-right relative text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">
                     {{ $selectedMessage->message }}
                     <div class="text-[10px] text-gray-400 text-right mt-2 flex items-center justify-end gap-1">
                         {{ $selectedMessage->created_at->format('H:i') }}
                         <i class="ri-check-double-line {{ $selectedMessage->status == 'sent' ? 'text-blue-500' : 'text-gray-400' }}"></i>
                     </div>
                 </div>
                 <div class="clear-both"></div>
             </div>
        </div>

        <!-- Technical Info -->
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50/50 border-b border-gray-100">
                 <h4 class="text-xs font-bold text-gray-500 uppercase m-0">Detail Teknis</h4>
            </div>
            <div class="p-4 space-y-3">
                 <div class="grid grid-cols-3 gap-2 text-sm">
                    <span class="text-gray-500">Nomor Tujuan</span>
                    <span class="col-span-2 font-mono font-bold text-gray-800">{{ $selectedMessage->to_phone }}</span>
                 </div>
                 @if($selectedMessage->contact_name)
                 <div class="grid grid-cols-3 gap-2 text-sm">
                    <span class="text-gray-500">Nama Kontak</span>
                    <span class="col-span-2 text-gray-800">{{ $selectedMessage->contact_name }}</span>
                 </div>
                 @endif
                 
                 @if($selectedMessage->error_message)
                 <div class="mt-4 p-3 bg-red-50 rounded-lg border border-red-100 text-xs text-red-700 font-mono break-all">
                     <strong class="block mb-1 text-red-800">Error Log:</strong>
                     {{ $selectedMessage->error_message }}
                 </div>
                 @endif

                 @if(!empty($selectedMessage->metadata))
                 <div class="mt-4">
                    <details class="group">
                        <summary class="text-xs font-bold text-blue-600 cursor-pointer list-none flex items-center gap-1">
                            <span>Show Raw Metadata</span>
                            <i class="ri-arrow-down-s-line group-open:rotate-180 transition-transform"></i>
                        </summary>
                        <pre class="mt-2 bg-gray-900 text-emerald-400 p-3 rounded-lg text-xs overflow-x-auto font-mono">{{ json_encode($selectedMessage->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </details>
                 </div>
                 @endif
            </div>
        </div>

    </div>
@else
    <!-- Empty State -->
    <div class="flex-1 flex flex-col items-center justify-center bg-gray-50/30 p-8 text-center h-full">
        <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-gray-100">
            <i class="ri-whatsapp-line text-5xl text-gray-300"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Pilih Pesan</h3>
        <p class="text-gray-500 max-w-sm mt-2">Pilih salah satu pesan dari daftar di sebelah kiri untuk melihat detail, status pengiriman, dan opsi lainnya.</p>
    </div>
@endif
