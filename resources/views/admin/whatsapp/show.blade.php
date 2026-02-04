@extends('layouts.soft')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 m-0">Detail Pesan #{{ $outbox->id }}</h2>
            <p class="text-sm text-gray-500 mt-1">Informasi lengkap status pengiriman pesan WhatsApp.</p>
        </div>
        <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors flex items-center gap-2 no-underline shadow-sm">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
        <div class="p-6 md:p-8 space-y-8">
            
            <!-- Top Status Section -->
            <div class="flex flex-wrap gap-6 p-6 bg-gray-50 rounded-2xl border border-gray-200/60">
                <div class="flex-1 min-w-[200px]">
                    <p class="text-xs text-uppercase font-semibold text-gray-500 mb-1">Status Pengiriman</p>
                    @php
                        $statusClasses = match($outbox->status) {
                            'sent' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'failed' => 'bg-red-100 text-red-800 border-red-200',
                            default => 'bg-gray-100 text-gray-800 border-gray-200',
                        };
                        $statusIcon = match($outbox->status) {
                            'sent' => 'ri-check-double-line',
                            'pending' => 'ri-time-line',
                            'failed' => 'ri-error-warning-line',
                            default => 'ri-question-line',
                        };
                    @endphp
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-bold border {{ $statusClasses }}">
                        <i class="{{ $statusIcon }} text-lg"></i>
                        {{ ucfirst($outbox->status) }}
                    </span>
                    @if($outbox->attempts > 0)
                        <span class="text-xs text-gray-500 ml-2">({{ $outbox->attempts }}x percobaan)</span>
                    @endif
                </div>

                <div class="flex-1 min-w-[200px]">
                    <p class="text-xs text-uppercase font-semibold text-gray-500 mb-1">Tipe Pesan</p>
                    @if($outbox->type == 'otp')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-purple-100 text-purple-700 border border-purple-200">
                            <i class="ri-key-2-line"></i> OTP Code
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-blue-50 text-blue-700 border border-blue-200">
                            <i class="ri-notification-3-line"></i> Notifikasi
                        </span>
                    @endif
                </div>

                <div class="w-full h-px bg-gray-200 md:hidden"></div>

                <div class="flex-1 min-w-[200px]">
                    <p class="text-xs text-uppercase font-semibold text-gray-500 mb-1">Waktu Dibuat</p>
                    <div class="flex items-center gap-2 text-gray-700 font-medium">
                        <i class="ri-calendar-line text-gray-400"></i>
                        {{ $outbox->created_at->format('d M Y, H:i:s') }}
                    </div>
                </div>

                @if($outbox->sent_at)
                <div class="flex-1 min-w-[200px]">
                    <p class="text-xs text-uppercase font-semibold text-gray-500 mb-1">Waktu Terkirim</p>
                    <div class="flex items-center gap-2 text-emerald-700 font-medium">
                        <i class="ri-check-line text-emerald-500"></i>
                        {{ $outbox->sent_at->format('d M Y, H:i:s') }}
                    </div>
                </div>
                @endif
            </div>

            <!-- Detail Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Info Kolom Kiri -->
                <div class="space-y-6">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-3">
                            <i class="ri-user-line text-blue-500"></i> Informasi Penerima
                        </h4>
                        <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                            <div class="mb-4">
                                <label class="text-xs text-gray-400 font-medium uppercase">Nomor Tujuan</label>
                                <p class="text-lg font-mono font-bold text-gray-800 m-0 tracking-wide">{{ $outbox->to_phone }}</p>
                            </div>
                            @if($outbox->contact_name)
                            <div>
                                <label class="text-xs text-gray-400 font-medium uppercase">Nama Kontak</label>
                                <p class="text-gray-800 font-medium m-0">{{ $outbox->contact_name }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($outbox->error_message)
                    <div>
                        <h4 class="text-sm font-bold text-red-700 flex items-center gap-2 mb-3">
                            <i class="ri-error-warning-fill text-red-500"></i> Pesan Error
                        </h4>
                        <div class="bg-red-50 p-4 rounded-xl border border-red-200 text-red-800 text-sm font-mono whitespace-pre-wrap break-words">
                            {{ $outbox->error_message }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Info Kolom Kanan (Message Content) -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-3">
                        <i class="ri-message-2-line text-blue-500"></i> Isi Pesan
                    </h4>
                    <div class="relative">
                         <div class="bg-[#e5ddd5] rounded-xl p-4 min-h-[160px] border border-gray-200 shadow-inner" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); opacity: 0.95;">
                             <div class="bg-white p-3 rounded-lg shadow-sm rounded-tr-none max-w-[90%] float-right relative text-sm text-gray-800 leading-relaxed whitespace-pre-wrap">
                                 {{ $outbox->message }}
                                 <div class="text-[10px] text-gray-400 text-right mt-2 flex items-center justify-end gap-1">
                                     {{ $outbox->created_at->format('H:i') }}
                                     <i class="ri-check-double-line {{ $outbox->status == 'sent' ? 'text-blue-500' : 'text-gray-400' }}"></i>
                                 </div>
                             </div>
                             <div class="clear-both"></div>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Metadata Section (Collapsible) -->
            @if(!empty($outbox->metadata))
            <div class="border-t border-gray-100 pt-6">
                <details class="group">
                    <summary class="flex items-center gap-2 cursor-pointer text-sm font-bold text-gray-500 hover:text-blue-600 transition-colors list-none">
                        <i class="ri-code-s-slash-line text-lg bg-gray-100 p-1 rounded group-open:bg-blue-100 group-open:text-blue-600 transition-colors"></i>
                        <span>Lihat Metadata Teknis</span>
                        <i class="ri-arrow-down-s-line ml-auto transform transition-transform group-open:rotate-180"></i>
                    </summary>
                    <div class="mt-4 bg-gray-900 rounded-xl p-4 overflow-x-auto shadow-inner">
                        <pre class="text-xs text-emerald-400 font-mono m-0">{{ json_encode($outbox->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </details>
            </div>
            @endif

            <!-- Action Buttons Footer -->
            @if(in_array($outbox->status, ['failed', 'pending']))
            <div class="border-t border-gray-100 pt-6 flex flex-wrap gap-4 justify-end">
                @if($outbox->status == 'pending')
                    <form action="{{ route('admin.whatsapp.cancel', $outbox) }}" method="POST" onsubmit="return confirm('Yakin batalkan pesan ini?')">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 bg-red-50 text-red-600 border border-red-200 rounded-xl font-bold hover:bg-red-100 transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                            <i class="ri-close-circle-line"></i> Batalkan Pesan
                        </button>
                    </form>
                @endif
                
                <form action="{{ route('admin.whatsapp.retry', $outbox) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-xl font-bold hover:bg-amber-100 transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                        <i class="ri-refresh-line"></i> Kirim Ulang Sekarang
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
