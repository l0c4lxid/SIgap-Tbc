@if(isset($selectedMessage))
    <!-- Chat Header -->
    <div class="flex items-center justify-between p-4 bg-white border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold">
                <i class="ri-whatsapp-line text-xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900">{{ $phone ?? $selectedMessage->to_phone ?? $selectedMessage->phone }}</h3>
                <p class="text-xs text-gray-500">WhatsApp</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="refreshChat()" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition" title="Refresh Chat">
                <i class="ri-refresh-line text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Chat Content -->
    <div class="flex-1 overflow-y-auto p-4 bg-[#e5ddd5] relative" id="chat-container" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); opacity: 0.95;">
        
        <div class="space-y-4 pb-4">
            @forelse($conversation as $msg)
                <div class="flex {{ $msg->source === 'outbox' ? 'justify-end' : 'justify-start' }} group">
                    <div class="max-w-[80%] min-w-[120px] rounded-lg shadow-sm p-2 relative {{ $msg->source === 'outbox' ? 'bg-[#d9fdd3] rounded-tr-none' : 'bg-white rounded-tl-none' }}">
                        
                        <!-- Delete Button (Hover) -->
                        <div class="absolute -top-2 {{ $msg->source === 'outbox' ? '-left-8' : '-right-8' }} opacity-0 group-hover:opacity-100 transition-opacity">
                            <form action="{{ $msg->source === 'outbox' ? route('pemda.whatsapp.destroy', $msg->id) : route('pemda.whatsapp.inbox.destroy', $msg->id) }}" method="POST" class="inline" data-confirm="Hapus pesan ini?" data-confirm-text="Ya, hapus">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-6 h-6 flex items-center justify-center rounded-full bg-red-500 text-white hover:bg-red-600 shadow-md transition" title="Hapus">
                                    <i class="ri-delete-bin-line text-xs"></i>
                                </button>
                            </form>
                        </div>

                        <!-- Message Content -->
                        <div class="pb-4 px-1">
                            @php
                                $hasMedia = $msg->source === 'inbox' && $msg->media_path;
                                $mediaType = $hasMedia ? explode('/', $msg->media_type ?? '')[0] : null;
                                $mediaUrl = $hasMedia && !empty($msg->wa_message_id)
                                    ? route('pemda.whatsapp.inbox.media', $msg->wa_message_id)
                                    : null;
                            @endphp

                            @if($hasMedia)
                                {{-- Media Messages --}}
                                @if($mediaType === 'image')
                                    <div class="mb-2 rounded-lg overflow-hidden max-w-xs">
                                        <img src="{{ $mediaUrl }}" 
                                             alt="Image" 
                                             class="w-full h-auto cursor-pointer"
                                             onclick="window.open(this.src, '_blank')">
                                    </div>
                                @elseif($mediaType === 'video')
                                    <div class="mb-2 rounded-lg overflow-hidden max-w-xs">
                                        <video controls class="w-full h-auto">
                                            <source src="{{ $mediaUrl }}" type="{{ $msg->media_type }}">
                                            Video tidak dapat diputar
                                        </video>
                                    </div>
                                @elseif($mediaType === 'audio')
                                    <div class="mb-2">
                                        <audio controls class="w-full max-w-xs">
                                            <source src="{{ $mediaUrl }}" type="{{ $msg->media_type }}">
                                            Audio tidak dapat diputar
                                        </audio>
                                    </div>
                                @elseif(str_contains($msg->media_type ?? '', 'sticker'))
                                    <div class="mb-2">
                                        <img src="{{ $mediaUrl }}" 
                                             alt="Sticker" 
                                             class="w-32 h-32 object-contain">
                                    </div>
                                @else
                                    {{-- Document/File --}}
                                    <a href="{{ $mediaUrl }}" 
                                       target="_blank"
                                       class="flex items-center gap-2 p-2 bg-gray-100 rounded-lg mb-2 hover:bg-gray-200 transition no-underline">
                                        <i class="ri-file-line text-2xl text-gray-600"></i>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-medium text-gray-800 truncate m-0">{{ $msg->media_path }}</p>
                                            <p class="text-[10px] text-gray-500 m-0">{{ $msg->media_type }}</p>
                                        </div>
                                        <i class="ri-download-line text-gray-600"></i>
                                    </a>
                                @endif

                                {{-- Caption/Text if present --}}
                                @if($msg->message && $msg->message !== '[Sticker]')
                                    <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap break-words">
                                        {{ $msg->message }}
                                    </div>
                                @endif
                            @else
                                {{-- Text-only Message --}}
                                <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap break-words">
                                    {{ $msg->message }}
                                </div>
                            @endif
                        </div>

                        <!-- Metadata -->
                        <div class="absolute bottom-1 right-2 flex items-center gap-1">
                            <span class="text-[10px] text-gray-500">
                                {{ $msg->created_at->format('H:i') }}
                            </span>
                            @if($msg->source === 'outbox')
                                @php
                                    $statusIcon = match($msg->status) {
                                        'sent' => 'ri-check-line text-gray-400',
                                        'delivered' => 'ri-check-double-line text-gray-400',
                                        'read' => 'ri-check-double-line text-blue-500',
                                        'failed' => 'ri-error-warning-fill text-red-500',
                                        'pending' => 'ri-time-line text-gray-400',
                                        default => 'ri-time-line text-gray-400',
                                    };
                                @endphp
                                <i class="{{ $statusIcon }} text-xs"></i>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <span class="px-3 py-1 bg-white/80 rounded-full text-xs text-gray-500 shadow-sm">Belum ada riwayat chat</span>
                </div>
            @endforelse
            <div id="scroll-anchor"></div>
        </div>
    </div>

    <!-- Send Message Form (WhatsApp Style) -->
    <div class="p-4 bg-gray-50 border-t border-gray-200">
        <form id="quick-send-form" class="flex items-end gap-2">
            @csrf
            <input type="hidden" name="phone" value="{{ $phone ?? $selectedMessage->phone }}" id="chat-phone">
            
            <!-- Attachment Button (Optional - for future) -->
            <button type="button" class="p-3 text-gray-500 hover:text-gray-700 hover:bg-gray-200 rounded-full transition" title="Attach File (Coming Soon)">
                <i class="ri-attachment-2 text-xl"></i>
            </button>

            <!-- Message Input -->
            <div class="flex-1 relative">
                <textarea 
                    name="message" 
                    id="message-input"
                    rows="1" 
                    class="w-full px-4 py-3 pr-12 bg-white border border-gray-300 rounded-lg resize-none focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    placeholder="Ketik pesan..."
                    maxlength="1000"
                    required
                ></textarea>
                <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" title="Emoji (Coming Soon)">
                    <i class="ri-emotion-happy-line text-xl"></i>
                </button>
            </div>

            <!-- Send Button -->
            <button type="submit" class="p-3 bg-emerald-500 text-white rounded-full hover:bg-emerald-600 transition disabled:opacity-50 disabled:cursor-not-allowed" id="send-btn">
                <i class="ri-send-plane-fill text-xl"></i>
            </button>
        </form>
        
        <!-- Typing Indicator (Hidden by default) -->
        <div id="typing-indicator" class="hidden mt-2 text-xs text-gray-500">
            <i class="ri-loader-4-line animate-spin"></i> Mengirim...
        </div>
    </div>

    <!-- Script for Auto Refresh & Scroll -->
    <script>
        // Define global functions (safe to redeclare)
        window.scrollToBottom = function() {
            const container = document.getElementById('chat-container');
            if(container) {
                container.scrollTop = container.scrollHeight;
            }
        }

        window.refreshChat = async function() {
            const header = document.getElementById('chat-header');
            if(!header || window.isRefreshing) return;

            window.isRefreshing = true;
            const outboxId = header.getAttribute('data-outbox-id');
            // If outboxId is missing/empty, we can't refresh properly
            if (!outboxId) {
                window.isRefreshing = false;
                return;
            }

            const url = `{{ route('pemda.whatsapp.show', ':id') }}`.replace(':id', outboxId);

            try {
                const res = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if(res.ok) {
                    const html = await res.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Replace content
                    const newContent = doc.getElementById('chat-container');
                    const container = document.getElementById('chat-container');
                    
                    if(container && newContent) {
                        // Smart scroll: only scroll if user was already near bottom
                        const wasNearBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
                        
                        // Check if content changed to avoid unnecessary renders
                        if (container.innerHTML !== newContent.innerHTML) {
                            container.innerHTML = newContent.innerHTML;
                            
                            if(wasNearBottom) {
                                window.scrollToBottom();
                            }
                        }
                    }
                }
            } catch (error) {
                console.error('Failed to refresh chat', error);
            } finally {
                window.isRefreshing = false;
            }
        }

        // Cleanup previous interval globally
        if (window.chatPollInterval) clearInterval(window.chatPollInterval);

        // === SCOPED EXECUTION ===
        {
            // Initial scroll
            window.scrollToBottom();

            // Cleanup on navigate
            window.removeEventListener('beforeunload', cleanupChat); 
            function cleanupChat() {
                if (window.chatPollInterval) clearInterval(window.chatPollInterval);
            }
            window.addEventListener('beforeunload', cleanupChat);

            // === SEND MESSAGE FUNCTIONALITY ===
            const messageInput = document.getElementById('message-input');
            const quickSendForm = document.getElementById('quick-send-form');

            // Auto-expand textarea
            if (messageInput) {
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
                });

                // Submit on Enter (Shift+Enter for new line)
                messageInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        if (quickSendForm) quickSendForm.dispatchEvent(new Event('submit'));
                    }
                });
            }

            // Quick Send Form Handler
            if (quickSendForm) {
                quickSendForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const sendBtn = document.getElementById('send-btn');
                    const typingIndicator = document.getElementById('typing-indicator');
                    const phone = document.getElementById('chat-phone').value;
                    const message = messageInput.value.trim();
                    
                    if (!message) return;
                    
                    // Show sending state
                    if(sendBtn) sendBtn.disabled = true;
                    if(typingIndicator) typingIndicator.classList.remove('hidden');
                    
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                        if (!csrfToken) throw new Error('CSRF token missing');
                        
                        const payload = {
                            to_phone: phone,
                            message: message,
                            type: 'notif',
                            instant_send: true
                        };
                        
                        const response = await fetch('{{ route("pemda.whatsapp.send") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });
                        
                        let data;
                        try { data = await response.json(); } catch(e) { throw new Error('Invalid server response'); }
                        
                        if (response.ok && data.success) {
                            // Clear input
                            if(messageInput) {
                                messageInput.value = '';
                                messageInput.style.height = 'auto';
                            }
                            
                            // Add message to chat immediately (optimistic UI)
                            appendMessage({
                                id: data.data ? data.data.message_id : null, // Fix: access nested data from controller
                                message: message,
                                source: 'outbox',
                                status: 'pending',
                                created_at: new Date()
                            });
                            
                            // Scroll to bottom
                            window.scrollToBottom();
                        } else {
                            throw new Error(data.message || 'Gagal mengirim pesan');
                        }
                    } catch (error) {
                        console.error('Send error:', error);
                        alert(error.message || 'Gagal mengirim pesan');
                    } finally {
                        if(sendBtn) sendBtn.disabled = false;
                        if(typingIndicator) typingIndicator.classList.add('hidden');
                    }
                });
            }

            // Append message to chat (optimistic UI)
            function appendMessage(msg) {
                const chatContainer = document.getElementById('chat-container');
                if (!chatContainer) return;
                
                const messageDiv = document.createElement('div');
                messageDiv.className = 'flex justify-end mb-3';
                
                const time = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                const statusIcon = msg.status === 'pending' ? 'ri-time-line text-gray-400' : 'ri-check-line text-gray-400';
                
                // Helper to escape HTML
                const safeMessage = document.createElement('div');
                safeMessage.textContent = msg.message;
                
                messageDiv.innerHTML = `
                    <div class="relative max-w-[70%] bg-[#d9fdd3] rounded-lg px-3 py-2 shadow-sm message-bubble" data-id="${msg.id || ''}" data-status="${msg.status}">
                        <div class="text-sm text-gray-800 leading-relaxed whitespace-pre-wrap break-words pr-16 message-text">
                            ${safeMessage.innerHTML}
                        </div>
                        <div class="absolute bottom-1 right-2 flex items-center gap-1 opacity-70 status-icon-container">
                            <span class="text-[10px] text-gray-500">${time}</span>
                            <i class="${statusIcon} text-xs"></i>
                        </div>
                    </div>
                `;
                
                chatContainer.appendChild(messageDiv);
            }
        }
    </script>
@else
    <!-- Empty State -->
    <div class="flex-1 flex flex-col items-center justify-center bg-gray-50/30 p-8 text-center h-full">
        <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center mb-6 shadow-sm border border-gray-100">
            <i class="ri-whatsapp-line text-5xl text-gray-300"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-800">Pilih Pesan</h3>
        <p class="text-gray-500 max-w-sm mt-2">Pilih salah satu pesan dari daftar di sebelah kiri untuk melihat detail percakapan.</p>
    </div>
@endif
