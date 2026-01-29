@extends('layouts.soft')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Select2 Glass Theme Override */
        .select2-container--default .select2-selection--single {
            background-color: rgba(255, 255, 255, 0.5);
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem; /* rounded-xl */
            height: 42px; /* Match input height */
            display: flex;
            align-items: center;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #374151; /* text-gray-700 */
            padding-left: 1rem;
            line-height: normal;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
            right: 10px;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: var(--color-glass-primary);
            box-shadow: 0 0 0 2px rgba(var(--color-glass-primary-rgb), 0.2);
        }
        .select2-dropdown {
            background-color: rgba(255, 255, 255, 0.95);
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .select2-search__field {
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: var(--color-glass-primary);
            color: white;
        }
    </style>
@endpush

@section('content')
    <div class="glass-card p-6 max-w-4xl mx-auto">
        <div class="mb-6 border-b border-gray-200/50 pb-4">
            <h5 class="font-bold text-xl text-gray-800 mb-1">Buat Target Skrining Baru</h5>
            <p class="text-sm text-gray-500 mb-0">Atur target skrining untuk kelurahan pada periode tertentu.</p>
        </div>

        <form action="{{ route('pemda.screening-targets.store') }}" method="POST"
              data-confirm="Apakah Anda yakin ingin menyimpan target skrining ini?"
              data-confirm-text="Ya, Simpan">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Kelurahan Selection --}}
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Kelurahan <span class="text-red-500">*</span></label>
                    <select name="kelurahan_user_id" id="select-kelurahan" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-[var(--color-glass-primary)] focus:outline-none" required>
                        <option value="">-- Pilih Kelurahan --</option>
                        @foreach($kelurahans as $kelurahan)
                            <option value="{{ $kelurahan->id }}" {{ old('kelurahan_user_id') == $kelurahan->id ? 'selected' : '' }}>
                                {{ $kelurahan->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('kelurahan_user_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Period Type --}}
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Periode <span class="text-red-500">*</span></label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="period_type" value="monthly" class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]" {{ old('period_type', 'monthly') == 'monthly' ? 'checked' : '' }} onchange="togglePeriodFields()">
                            <span class="text-sm text-gray-700">Bulanan</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="period_type" value="custom" class="text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]" {{ old('period_type') == 'custom' ? 'checked' : '' }} onchange="togglePeriodFields()">
                            <span class="text-sm text-gray-700">Custom Range</span>
                        </label>
                    </div>
                </div>

                {{-- Month Picker --}}
                <div class="col-span-2 md:col-span-1" id="field-monthly">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Bulan <span class="text-red-500">*</span></label>
                    <input type="month" name="month" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-[var(--color-glass-primary)] focus:outline-none" value="{{ old('month', date('Y-m')) }}">
                    @error('month') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Target Total Skrining (Moved Here) --}}
                <div class="col-span-2 md:col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Target Total Skrining <span class="text-red-500">*</span></label>
                    <input type="text" name="target_total_display" id="target_total_display" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-[var(--color-glass-primary)] focus:outline-none" value="{{ old('target_total') }}" placeholder="Contoh: 1,000" required oninput="formatNumber(this)">
                    <input type="hidden" name="target_total" id="target_total" value="{{ old('target_total') }}">
                    <p class="text-xs text-gray-500 mt-1">Jumlah total pasien yang akan diskrining oleh semua kader.</p>
                    @error('target_total') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Custom Date Range --}}
                <div class="col-span-2 md:col-span-2 grid grid-cols-2 gap-4 hidden" id="field-custom">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Dari Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date_from" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-[var(--color-glass-primary)] focus:outline-none" value="{{ old('date_from') }}">
                        @error('date_from') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sampai Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="date_to" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-[var(--color-glass-primary)] focus:outline-none" value="{{ old('date_to') }}">
                        @error('date_to') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Notes --}}
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan (Opsional)</label>
                    <textarea name="notes" rows="3" class="w-full px-4 py-2 rounded-xl border border-gray-200 bg-white/50 focus:ring-2 focus:ring-[var(--color-glass-primary)] focus:outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200/50">
                <a href="{{ route('pemda.screening-targets.index') }}" class="glass-button bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm font-semibold no-underline">Batal</a>
                <button type="submit" class="glass-button px-6 py-2 rounded-lg text-sm font-semibold">Simpan Target</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    {{-- jQuery (Required for Select2) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#select-kelurahan').select2({
                placeholder: "-- Pilih Kelurahan --",
                allowClear: true,
                width: '100%'
            });

            togglePeriodFields();
            
            // Init format if value exists
            const displayInput = document.getElementById('target_total_display');
            if (displayInput.value) {
                formatNumber(displayInput);
            }
        });

        function togglePeriodFields() {
            const type = document.querySelector('input[name="period_type"]:checked').value;
            if (type === 'monthly') {
                document.getElementById('field-monthly').classList.remove('hidden');
                document.getElementById('field-custom').classList.add('hidden');
            } else {
                document.getElementById('field-monthly').classList.add('hidden');
                document.getElementById('field-custom').classList.remove('hidden');
            }
        }
        
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
    </script>
@endpush
