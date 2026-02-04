@extends('layouts.soft')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 m-0">Kirim Pesan Baru</h2>
            <p class="text-sm text-gray-500 mt-1">Buat dan jadwalkan pesan WhatsApp untuk kader atau masyarakat.</p>
        </div>
        <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-xl text-sm font-medium hover:bg-gray-50 transition-colors flex items-center gap-2 no-underline shadow-sm">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
            <i class="ri-error-warning-fill text-red-500 text-xl mt-0.5"></i>
            <div>
                <h4 class="text-sm font-bold text-red-800 m-0">Terdapat Kesalahan Input</h4>
                <ul class="list-disc pl-4 mt-1 text-sm text-red-700 mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Form Section -->
        <div class="space-y-6">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
                <form method="POST" action="{{ route('admin.whatsapp.send') }}" id="waForm" class="space-y-6">
                    @csrf
                    
                    <!-- Phone Input -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor WhatsApp Tujuan <span class="text-red-500">*</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-gray-500 font-medium z-10 pointer-events-none">+62</span>
                            <input 
                                type="text" 
                                name="phone" 
                                id="phone"
                                value="{{ old('phone') }}"
                                class="w-full pl-12 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all font-medium text-gray-900 placeholder-gray-400"
                                placeholder="812xxxxx (Tanpa 0)"
                                required
                            >
                        </div>
                        <p class="text-xs text-gray-500 mt-1.5 flex items-center gap-1.5">
                            <i class="ri-information-line text-blue-500"></i> Masukkan angka saja, tanpa 0 atau 62 di depan.
                        </p>
                    </div>

                    <!-- Message Input -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                             <label class="block text-sm font-semibold text-gray-700">Isi Pesan <span class="text-red-500">*</span></label>
                             <span id="charCount" class="text-[10px] bg-gray-100 px-2 py-0.5 rounded-full text-gray-500 font-medium">0 / 4096</span>
                        </div>
                        <textarea 
                            name="message" 
                            id="message" 
                            rows="8" 
                            class="w-full p-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-gray-900 placeholder-gray-400 text-sm leading-relaxed resize-none"
                            placeholder="Tulis pesan anda disini..."
                            required
                        >{{ old('message') }}</textarea>
                    </div>

                    <!-- Schedule Toggle -->
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-200/60">
                         <div class="flex items-center justify-between">
                            <label for="toggleSchedule" class="text-sm font-semibold text-gray-700 cursor-pointer select-none">Jadwalkan Pengiriman</label>
                            <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="toggle" id="toggleSchedule" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer border-gray-300 left-0 transition-all duration-300"/>
                                <label for="toggleSchedule" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer transition-colors duration-300"></label>
                            </div>
                        </div>
                        
                        <div id="scheduleBox" class="hidden mt-4 pt-4 border-t border-gray-200/60 animate-fade-in-down">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Delay (Menit)</label>
                            <div class="relative">
                                <i class="ri-timer-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input type="number" name="delay_minutes" min="0" max="1440" placeholder="Contoh: 30" class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Pesan akan dikirim otomatis setelah waktu delay.</p>
                        </div>
                    </div>

                    <div class="pt-2 grid grid-cols-2 gap-3">
                        <button type="submit" name="send_now" value="0" class="col-span-1 px-4 py-3 bg-white border border-blue-500 text-blue-600 rounded-xl font-bold hover:bg-blue-50 transition-colors shadow-sm cursor-pointer">
                            <i class="ri-time-line mr-1"></i> Antrikan
                        </button>
                        <button type="submit" name="send_now" value="1" class="col-span-1 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-xl font-bold hover:shadow-lg hover:from-emerald-600 hover:to-teal-600 transition-all shadow-emerald-500/20 cursor-pointer border-none">
                            <i class="ri-send-plane-fill mr-1"></i> Kirim Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview & Templates -->
        <div class="space-y-6">
            
            <!-- Phone Preview -->
             <div class="bg-gray-100 border border-gray-200 rounded-[2rem] p-3 shadow-xl max-w-sm mx-auto w-full relative overflow-hidden">
                <!-- Phone Notch Area -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-black rounded-b-xl z-20"></div>
                
                <!-- Screen -->
                <div class="bg-[#e5ddd5] rounded-[1.5rem] h-[500px] overflow-hidden flex flex-col relative">
                     <!-- WhatsApp Header -->
                     <div class="bg-[#008069] p-3 pt-8 pb-2 flex items-center gap-3 text-white shadow-sm z-10">
                        <i class="ri-arrow-left-line"></i>
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-[#008069]">
                            <i class="ri-user-fill"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold m-0 leading-tight">Penerima</p>
                        </div>
                        <div class="flex gap-4 pr-1">
                             <i class="ri-videocam-fill text-lg"></i>
                             <i class="ri-phone-fill text-lg"></i>
                             <i class="ri-more-2-fill text-lg"></i>
                        </div>
                     </div>

                     <!-- Chat Area -->
                     <div class="flex-1 p-3 overflow-y-auto bg-repeat" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); opacity: 0.9;">
                         <!-- Bubble -->
                         <div class="flex justify-end mb-2">
                             <div class="bg-[#d9fdd3] text-gray-800 p-2 px-3 rounded-lg rounded-tr-none shadow-sm max-w-[85%] text-[13px] relative">
                                 <p id="previewText" class="m-0 whitespace-pre-wrap leading-snug break-words">Preview pesan...</p>
                                 <div class="text-[10px] text-gray-500 text-right mt-1 flex items-center justify-end gap-1">
                                     <span id="previewTime">14:00</span>
                                     <i class="ri-check-double-line text-blue-500 text-sm"></i>
                                 </div>
                             </div>
                         </div>
                     </div>
                </div>
             </div>

             <!-- Quick Templates -->
             <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-4">
                 <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                     <i class="ri-flashlight-line text-amber-500"></i> Template Cepat
                 </h3>
                 <div class="grid grid-cols-1 gap-2">
                     <button type="button" class="template-btn text-left p-3 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-200 transition-all group bg-transparent cursor-pointer"
                        data-template="Halo [Nama],\n\nKami dari SITUBA ingin menginformasikan bahwa [Isi Pesan].\n\nTerima kasih.">
                         <span class="block text-xs font-bold text-gray-700 group-hover:text-blue-600">Info Standar</span>
                         <span class="block text-xs text-gray-400 truncate mt-0.5">Halo [Nama], Kami dari SITUBA...</span>
                     </button>
                     
                     <button type="button" class="template-btn text-left p-3 rounded-xl hover:bg-gray-50 border border-transparent hover:border-gray-200 transition-all group bg-transparent cursor-pointer"
                        data-template="Mengingatkan jadwal:\n\n📅 [Tanggal]\n⏰ [Jam]\n📍 [Lokasi]\n\nMohon hadir tepat waktu.">
                         <span class="block text-xs font-bold text-gray-700 group-hover:text-blue-600">Reminder Jadwal</span>
                         <span class="block text-xs text-gray-400 truncate mt-0.5">Mengingatkan jadwal: [Tanggal]...</span>
                     </button>
                 </div>
             </div>
        </div>
    </div>
</div>

<style>
/* Custom Toggle Switch Style */
.toggle-checkbox:checked {
  right: 0;
  border-color: #10B981;
}
.toggle-checkbox:checked + .toggle-label {
  background-color: #10B981;
}
.toggle-checkbox { right: auto; left: 0; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('message');
    const previewText = document.getElementById('previewText');
    const previewTime = document.getElementById('previewTime');
    const charCount = document.getElementById('charCount');
    const toggleSchedule = document.getElementById('toggleSchedule');
    const scheduleBox = document.getElementById('scheduleBox');

    // Live Preview
    messageInput.addEventListener('input', function() {
        if (this.value.trim() === '') {
            previewText.textContent = 'Preview pesan...';
            previewText.classList.add('italic', 'text-gray-400');
        } else {
            previewText.textContent = this.value;
            previewText.classList.remove('italic', 'text-gray-400');
        }

        const len = this.value.length;
        charCount.textContent = `${len} / 4096`;
        if(len > 4096) charCount.classList.replace('bg-gray-100', 'bg-red-100 text-red-600');
        else charCount.classList.replace('bg-red-100', 'bg-gray-100 text-gray-500');

        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        previewTime.textContent = `${hours}:${minutes}`;
    });

    // Toggle Schedule
    toggleSchedule.addEventListener('change', function() {
        if(this.checked) {
            scheduleBox.classList.remove('hidden');
        } else {
            scheduleBox.classList.add('hidden');
        }
    });

    // Template Buttons
    document.querySelectorAll('.template-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tpl = this.getAttribute('data-template');
            messageInput.value = tpl;
            messageInput.dispatchEvent(new Event('input'));
            messageInput.focus();
        });
    });
});
</script>
@endsection
