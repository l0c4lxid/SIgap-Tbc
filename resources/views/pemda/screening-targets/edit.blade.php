@extends('layouts.soft')

@section('content')
    <div class="glass-card p-6 max-w-2xl mx-auto">
        <div class="mb-6 border-b border-gray-200/50 pb-4">
             <div class="flex items-center gap-2 mb-1">
                 <a href="{{ route('pemda.screening-targets.show', $target) }}" class="text-gray-500 hover:text-[var(--color-glass-primary)] transition">
                    <i class="ri-arrow-left-line text-xl"></i>
                 </a>
                 <h5 class="font-bold text-xl text-gray-800 mb-0">Edit Target Skrining</h5>
             </div>
             <p class="text-sm text-gray-500 mb-0 ml-7">Ubah target total atau suspek.</p>
        </div>

        <form action="{{ route('pemda.screening-targets.update', $target) }}" method="POST"
              data-confirm="Apakah Anda yakin ingin menyimpan perubahan pada target ini?"
              data-confirm-text="Ya, Simpan">
            @csrf
            @method('PUT')
            
            <div class="mb-6 bg-blue-50/50 p-4 rounded-xl border border-blue-100 text-sm text-blue-800">
                <strong>Info:</strong> Target untuk Kelurahan <strong>{{ $target->kelurahan->name ?? '-' }}</strong> pada periode 
                @if($target->period_type == 'monthly')
                    <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $target->month)->translatedFormat('F Y') }}</strong>
                @else
                    <strong>{{ $target->date_from->format('d M') }} - {{ $target->date_to->format('d M Y') }}</strong>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Target Total Skrining <span class="text-red-500">*</span></label>
                    <input type="text" name="target_total_display" id="target_total_display" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-[var(--color-glass-primary)] focus:outline-none" value="{{ old('target_total', $target->target_total) }}" placeholder="Contoh: 1,000" required oninput="formatNumber(this)">
                    <input type="hidden" name="target_total" id="target_total" value="{{ old('target_total', $target->target_total) }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-[var(--color-glass-primary)] focus:outline-none">{{ old('notes', $target->notes) }}</textarea>
                </div>
            </div>



            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200/50">
                <a href="{{ route('pemda.screening-targets.show', $target) }}" class="glass-button bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm font-semibold no-underline">Batal</a>
                <button type="submit" class="glass-button px-6 py-2 rounded-lg text-sm font-semibold">Simpan Perubahan</button>
            </div>
        </form>
    </div>
    <script>
        function formatNumber(input) {
            // Remove non-digits
            let value = input.value.replace(/\D/g, '');
            
            // Set hidden input value
            document.getElementById('target_total').value = value;
            
            // Format display with commas
            if (value) {
                input.value = new Intl.NumberFormat('en-US').format(value);
            } else {
                input.value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const displayInput = document.getElementById('target_total_display');
            if (displayInput.value) {
                formatNumber(displayInput);
            }
        });
    </script>
@endsection
