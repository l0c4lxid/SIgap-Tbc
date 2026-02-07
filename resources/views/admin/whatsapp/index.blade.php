@extends('layouts.soft')

@section('title', 'Pusat WhatsApp - Pemda')

@section('content')
<div class="h-[calc(100vh-8rem)] flex flex-col gap-4">

    <!-- Connection Status -->
    <div id="connection-status-container" class="flex items-center justify-between bg-white/60 backdrop-blur-md border border-white/40 shadow-sm rounded-2xl p-4 shrink-0">
        <div class="flex items-center gap-3">
            @php
                $isServiceUp = isset($nodeHealth['status']) && $nodeHealth['status'] && $nodeHealth['status'] !== 'error';
                $isWaConnected = $isServiceUp && ($nodeHealth['waConnected'] ?? false);
                
                // Initialize default values (Offline state)
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
            <div id="message-list-container" class="flex-1 overflow-y-auto">
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
                            @php
                                $isInbox = isset($msg->is_inbox) && $msg->is_inbox;
                                $phone = $msg->phone ?? ($isInbox ? $msg->from_phone : $msg->to_phone);
                                $timestamp = $isInbox ? $msg->received_at : $msg->created_at;
                                $messageText = $msg->message ?? ($isInbox && $msg->media_path ? '[Media]' : '');
                            @endphp
                            
                            <a href="{{ route('pemda.whatsapp.show', $phone) }}" 
                               class="message-link block p-4 hover:bg-gray-50 transition-colors border-l-4 {{ (isset($phone) && isset($selectedMessage) && $selectedMessage->phone == $phone) ? 'bg-blue-50/50 border-blue-500' : 'border-transparent' }} no-underline group relative">
                                
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-bold text-gray-900 text-sm">{{ $phone }}</span>
                                    <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $timestamp->format('d/m H:i') }}</span>
                                </div>
                                
                                <p class="text-xs text-gray-500 line-clamp-2 mb-2 leading-relaxed">
                                    {{ $messageText }}
                                </p>

                                <div class="flex items-center justify-between">
                                    @if($isInbox)
                                        {{-- Inbox message indicator --}}
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold text-blue-600 bg-blue-50">
                                            <i class="ri-arrow-down-line"></i> Masuk
                                        </span>
                                    @else
                                        {{-- Outbox status --}}
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
                                    @endif

                                    <!-- Quick Actions (Hover) -->
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @if(in_array($msg->status, ['failed', 'pending']))
                                        <form action="{{ route('pemda.whatsapp.retry', $msg) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-gray-200 text-amber-500 hover:bg-amber-50 cursor-pointer" title="Retry" onclick="event.stopPropagation();">
                                                <i class="ri-refresh-line"></i>
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('pemda.whatsapp.destroy', $msg) }}" method="POST" class="inline" data-confirm="Hapus pesan ini?" data-confirm-text="Ya, hapus">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-6 h-6 flex items-center justify-center rounded bg-white border border-gray-200 text-red-500 hover:bg-red-50 cursor-pointer" title="Hapus" onclick="event.stopPropagation();">
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
    const messageContainer = document.getElementById('message-list-container');
    const detailContainerRef = document.getElementById('detail-container'); // Ref for updating content
    const connectionContainer = document.getElementById('connection-status-container');
    
    // --- Socket.IO Integration ---
    const nodeUrl = "{{ config('services.whatsapp.node_url') }}"; // e.g., http://localhost:3000
    const socket = io(nodeUrl, {
        reconnectionDelayMax: 10000,
    });

    socket.on('connect', () => {
        console.log('Connected to WhatsApp Service via Socket.IO');
    });

    // Real-time Connection Status
    socket.on('connection.update', (status) => {
        // Update status UI immediately if possible, or trigger refresh
        console.log('Connection update:', status);
        refreshDashboard(); 
    });

    // Real-time Message Handling
    socket.on('message.upsert', (msg) => {
        console.log('New message:', msg);
        
        // 1. If chat is open for this number, append message
        const currentPhone = window.currentChatPhone;
        
        // Normalize phone
        const msgPhone = msg.from.split('@')[0].replace(/\D/g, '');
        const activePhone = currentPhone ? currentPhone.replace(/\D/g, '') : null;

        if (activePhone && (msgPhone.includes(activePhone) || activePhone.includes(msgPhone))) {
            appendMessageToChat(msg);
        }

        // 2. Refresh list/counts if needed (or we can partial update)
        if (!window.isRefreshing) {
            refreshDashboard();
        }
        
        // 3. Desktop Notification
        if (msg.is_inbox) {
             notifyNewMessage(msg);
        }
    });

    socket.on('message.update', (update) => {
        console.log('Message update:', update);
        // Find message bubble by ID
        const bubbles = document.querySelectorAll(`.message-bubble[data-id="${update.id}"]`);
        bubbles.forEach(bubble => {
            const iconContainer = bubble.querySelector('.status-icon-container');
            if (iconContainer) {
                // Update icon based on status
               let iconClass = 'ri-time-line text-gray-400'; // pending
               if (update.status === 'sent') iconClass = 'ri-check-line text-gray-400';
               else if (update.status === 'delivered') iconClass = 'ri-check-double-line text-gray-400'; // if we supported it
               else if (update.status === 'read') iconClass = 'ri-check-double-line text-blue-500';
               
               iconContainer.innerHTML = `<i class="${iconClass} text-xs"></i>`;
            }
        });
    });

    function appendMessageToChat(msg) {
        const chatContainerOuter = document.getElementById('chat-container');
        if (!chatContainerOuter) return;
        const chatContainer = chatContainerOuter.querySelector('.space-y-4') || chatContainerOuter;

        const isMe = !msg.is_inbox;
        
        // DEDUPLICATION: Check if we already have this message (by ID or content match for optimistic)
        // For optimistic messages, we might not have the ID yet, so we check for "pending" messages with same text
        if (isMe && msg.text) {
             const pendingBubbles = Array.from(chatContainer.querySelectorAll('.message-bubble[data-status="pending"]'));
             const duplicate = pendingBubbles.find(b => b.querySelector('.message-text')?.textContent.trim() === msg.text.trim());
             
             if (duplicate) {
                 // Found the optimistic message! Update it interactions
                 duplicate.setAttribute('data-id', msg.id);
                 duplicate.setAttribute('data-status', 'sent'); // Verify status
                 // Update icon
                 const iconContainer = duplicate.querySelector('.status-icon-container');
                 if(iconContainer) iconContainer.innerHTML = `<i class="ri-check-line text-gray-400 text-xs"></i>`;
                 return; // Don't append new one
             }
        }
        // Also check by ID if we already have it
        if (document.querySelector(`.message-bubble[data-id="${msg.id}"]`)) return;

        const alignClass = isMe ? 'justify-end' : 'justify-start';
        const bgClass = isMe ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white border border-gray-100/50 text-gray-800 rounded-tl-none';
        const date = new Date(msg.timestamp);
        const timeStr = date.getHours().toString().padStart(2, '0') + ':' + date.getMinutes().toString().padStart(2, '0');
        
        let contentHtml = '';
        if (msg.media) {
             contentHtml += `
                <div class="mb-2 rounded-lg overflow-hidden border border-gray-100 bg-gray-50 max-w-full">
                     <a href="/admin/media/${msg.media.file}" target="_blank" class="flex items-center gap-3 p-3 no-underline group hover:bg-gray-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0">
                             <i class="ri-file-text-line text-lg"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                             <p class="text-xs font-bold text-gray-700 truncate m-0">${msg.media.name || 'Dokumen'}</p>
                             <p class="text-[10px] text-gray-400 m-0 uppercase">${msg.media.mime?.split('/')[1] || 'FILE'}</p>
                        </div>
                        <i class="ri-download-line text-gray-400 group-hover:text-blue-600"></i>
                     </a>
                </div>
             `;
        }
        
        if (msg.text) {
             contentHtml += `<div class="text-sm leading-relaxed whitespace-pre-wrap break-words message-text">${msg.text}</div>`;
        }

        const html = `
            <div class="flex w-full ${alignClass} animate-fade-in-up">
                <div class="relative max-w-[85%] sm:max-w-[70%] group">
                    <div class="px-4 py-3 rounded-2xl shadow-sm ${bgClass} relative z-10 message-bubble" data-id="${msg.id}" data-status="sent">
                        ${contentHtml}
                        <div class="flex items-center justify-end gap-1 mt-1 opacity-70 status-icon-container">
                            <span class="text-[10px] font-medium">${timeStr}</span>
                            ${isMe ? '<i class="ri-check-line text-gray-400 text-xs"></i>' : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        chatContainer.insertAdjacentHTML('beforeend', html);
        scrollToBottom();
    }

    function scrollToBottom() {
        const chatContainer = document.getElementById('chat-container');
        if (chatContainer) {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    }
    
    // --- Existing Logic ---

    // Attach event listeners to message links (function to be reusable)
    function attachMessageLinkListeners() {
        const messageLinks = document.querySelectorAll('.message-link');
        messageLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Set current phone for socket
                const phoneSpan = this.querySelector('.font-bold');
                if (phoneSpan) {
                     window.currentChatPhone = phoneSpan.textContent.trim();
                }
                
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
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    detailContainer.innerHTML = html;
                    
                    // Re-execute scripts in detail view if any inline scripts (like the chat polling)
                    const scripts = detailContainer.querySelectorAll('script');
                    scripts.forEach(script => {
                        const newScript = document.createElement('script');
                        Array.from(script.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                        newScript.appendChild(document.createTextNode(script.innerHTML));
                        script.parentNode.replaceChild(newScript, script);
                    });
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
    }

    // Initial attach
    attachMessageLinkListeners();
    // Initialize current chat phone if available (on page load with open chat)
    const selectedPhoneElement = document.querySelector('.message-link.bg-blue-50\\/50 .font-bold');
    if (selectedPhoneElement) {
        window.currentChatPhone = selectedPhoneElement.textContent.trim();
    }


    // Handle Back Button (Popstate)
    window.addEventListener('popstate', function() {
        location.reload();
    });

    // Auto-refresh Dashboard (List & Stats) - Reduced frequency since we have socket
    // Keep it as a fallback
    let dashboardPollInterval = setInterval(refreshDashboard, 30000); 

    async function refreshDashboard() {
        if (window.isRefreshing) return;
        window.isRefreshing = true;
        try {
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (res.ok) {
                const html = await res.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // Update Status
                const newStatus = doc.getElementById('connection-status-container');
                if (newStatus && connectionContainer) {
                    connectionContainer.innerHTML = newStatus.innerHTML;
                }

                // Update List
                const newList = doc.getElementById('message-list-container');
                if (newList && messageContainer) {
                    messageContainer.innerHTML = newList.innerHTML;
                    attachMessageLinkListeners(); // Re-attach events
                    // Note: checkNewMessages is redundant with socket
                }
            }
        } catch (error) {
            console.error('Dashboard refresh failed', error);
        } finally {
             window.isRefreshing = false;
        }
    }
    
    // === DESKTOP NOTIFICATIONS ===
    let notificationPermission = Notification.permission;
    
    // Request notification permission
    if (notificationPermission === 'default') {
        Notification.requestPermission().then(permission => {
            notificationPermission = permission;
        });
    }
    
    function notifyNewMessage(msg) {
        if (notificationPermission === 'granted') {
             const phone = msg.from.split('@')[0];
             const preview = msg.text || (msg.media ? '[Media]' : 'Pesan baru');
             
             new Notification('WhatsApp Message', {
                body: `${phone}: ${preview.substring(0, 50)}...`,
                icon: '/favicon.ico',
                tag: 'wa-message',
                requireInteraction: false
            });
            
            // Play sound
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSgFHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE=');
                audio.play().catch(() => {});
            } catch (e) {}
        }
    }
    
    // Cleanup
    window.addEventListener('beforeunload', () => {
        clearInterval(dashboardPollInterval);
        socket.disconnect();
    });
});
</script>
@endpush
@endsection
