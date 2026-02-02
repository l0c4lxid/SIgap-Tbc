@extends('layouts.soft')

@section('subjudul', 'Pengaturan Kemitraan')

@section('content')
    <div class="">
        {{-- Header Navigation --}}
        <div class="mb-6 flex items-center gap-2">
            <a href="{{ route('pemda.partnership.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-[var(--color-glass-primary)] hover:border-[var(--color-glass-primary)] transition-all">
                <i class="ri-arrow-left-line text-xl"></i>
            </a>
            <div>
                <h5 class="font-bold text-xl text-gray-800 m-0">Pengaturan Kemitraan</h5>
                <p class="text-sm text-gray-500 m-0">Menghubungkan {{ $kelurahan->name }} dengan Puskesmas Induk</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Current Status Card --}}
            <div class="lg:col-span-1 flex flex-col gap-6">
                <div class="glass-card p-6">
                    <h6 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="ri-community-line text-[var(--color-glass-primary)]"></i>
                        Detail Wilayah
                    </h6>
                    
                    <div class="space-y-4">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama Kelurahan</span>
                            <p class="font-bold text-gray-800 text-lg">{{ $kelurahan->name }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Alamat Kantor</span>
                            <p class="text-sm text-gray-600">{{ $kelurahan->detail->address ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Organisasi / Kode</span>
                            <p class="text-sm text-gray-600">{{ $kelurahan->detail->organization ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="glass-card p-6 flex-1 flex flex-col justify-center">
                    <h6 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="ri-link-m text-[var(--color-glass-primary)]"></i>
                        Status Kemitraan
                    </h6>

                    @if($kelurahan->detail->supervisor)
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center h-full flex flex-col justify-center items-center">
                            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="ri-check-double-line text-xl"></i>
                            </div>
                            <span class="block text-sm font-bold text-green-800 uppercase tracking-wider mb-1">Terhubung</span>
                            <p class="text-xs text-green-600">Terhubung dengan puskesmas induk.</p>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center h-full flex flex-col justify-center items-center">
                            <div class="w-12 h-12 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-2">
                                <i class="ri-link-unlink-m text-xl"></i>
                            </div>
                            <span class="block text-sm font-bold text-gray-500 uppercase tracking-wider mb-1">Belum Terhubung</span>
                            <p class="text-xs text-gray-400">Kelurahan ini belum memiliki puskesmas induk.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: Settings Form --}}
            <div class="lg:col-span-2">
                
                {{-- Pending Request Alert --}}
                @php
                    $pendingId = $kelurahan->detail->pending_supervisor_id;
                    $pendingPuskesmas = $pendingId ? $puskesmasList->firstWhere('id', $pendingId) : null;
                    $isPendingTargetSameAsCurrent = $kelurahan->detail->supervisor_id === $pendingId;
                @endphp

                @if($pendingPuskesmas && !$isPendingTargetSameAsCurrent)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <i class="ri-alert-line text-yellow-600 text-xl mt-0.5"></i>
                            <div>
                                <h6 class="font-bold text-yellow-800 mb-1">Permintaan Masuk</h6>
                                <p class="text-sm text-yellow-700 m-0">
                                    <span class="font-bold">{{ $pendingPuskesmas->name }}</span> mengajukan diri sebagai induk.
                                </p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('pemda.partnership.update', $kelurahan) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="puskesmas_id" value="{{ $pendingPuskesmas->id }}">
                            <button type="submit" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg shadow-sm hover:shadow-md transition-all text-sm flex items-center gap-2">
                                <i class="ri-check-line"></i> Setujui
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Main Settings --}}
                <div class="glass-card p-6">
                    <h6 class="font-bold text-gray-800 mb-6 pb-4 border-b border-gray-100">
                        Atur Puskesmas Induk
                    </h6>

                    <form method="POST" action="{{ route('pemda.partnership.update', $kelurahan) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Puskesmas</label>
                            
                            {{-- Custom Searchable Select --}}
                            <div class="relative" id="puskesmas-select-container">
                                <input type="hidden" name="puskesmas_id" id="puskesmas-hidden-input" value="{{ optional($kelurahan->detail->supervisor)->id }}">
                                
                                <button type="button" id="puskesmas-dropdown-trigger" class="w-full px-5 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-left flex items-center justify-between focus:outline-none focus:ring-4 focus:ring-[var(--color-glass-primary)]/10 focus:border-[var(--color-glass-primary)] transition-all hover:bg-gray-100/50">
                                    <div class="flex items-center gap-3 overflow-hidden">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm shrink-0" id="selected-icon">
                                            <i class="ri-hospital-line"></i>
                                        </div>
                                        <span class="block truncate font-medium text-gray-700" id="selected-label">
                                            {{ optional($kelurahan->detail->supervisor)->name ?? '-- Pilih Puskesmas --' }}
                                        </span>
                                    </div>
                                    <i class="ri-arrow-down-s-line text-gray-400 text-lg transition-transform duration-200" id="dropdown-arrow"></i>
                                </button>

                                <div id="puskesmas-dropdown-menu" class="hidden absolute z-50 mt-2 w-full bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden flex flex-col transition-all duration-200 origin-top transform scale-95 opacity-0">
                                    <div class="p-3 border-b border-gray-100 sticky top-0 bg-white z-10">
                                        <div class="relative">
                                            <i class="ri-search-line absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                            <input type="text" id="puskesmas-search" class="w-full pl-9 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[var(--color-glass-primary)] transition-colors" placeholder="Cari puskesmas...">
                                        </div>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto p-2 custom-scrollbar" id="puskesmas-options-list">
                                        {{-- Options will be populated by JS --}}
                                        @foreach($puskesmasList as $p)
                                            <div class="puskesmas-option cursor-pointer select-none rounded-lg px-3 py-2.5 text-sm hover:bg-blue-50 text-gray-700 hover:text-blue-700 flex items-center gap-3 transition-colors" 
                                                 data-value="{{ $p->id }}" 
                                                 data-label="{{ $p->name }}">
                                                <i class="ri-hospital-line text-gray-400 w-5 text-center icon-display"></i>
                                                <span class="font-medium option-text">{{ $p->name }}</span>
                                            </div>
                                        @endforeach
                                        <div id="no-results" class="hidden p-4 text-sm text-gray-400 text-center italic">Tidak ditemukan</div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-2">
                                Memilih puskesmas akan memberikan akses penuh puskesmas tersebut terhadap data kelurahan ini.
                            </p>
                        </div>

                        <style>
                            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
                            .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
                            .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
                            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
                        </style>

                        <div class="flex items-center justify-end gap-3">
                            <button type="submit" class="bg-[var(--color-glass-primary)] hover:opacity-90 text-white font-bold py-2.5 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center gap-2 transform active:scale-95">
                                <i class="ri-save-line"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                    
                    @if($kelurahan->detail->supervisor)
                        <div class="mt-8 pt-6 border-t border-gray-100">
                            <h6 class="font-bold text-red-600 text-sm mb-2">Zona Bahaya</h6>
                            <p class="text-xs text-gray-500 mb-4">Melepaskan kemitraan akan memutus akses data puskesmas saat ini.</p>
                            
                            <form method="POST" action="{{ route('pemda.partnership.detach', $kelurahan) }}" data-confirm="Apakah Anda yakin ingin melepas kemitraan ini? Puskesmas tidak akan bisa melihat data kelurahan ini lagi." data-confirm-text="Ya, Lepas">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 font-semibold text-sm hover:underline hover:text-red-700 flex items-center gap-1 border border-red-200 bg-red-50 px-4 py-2 rounded-lg">
                                    <i class="ri-link-unlink-m"></i> Lepas Kemitraan Saat Ini
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('puskesmas-select-container');
            const trigger = document.getElementById('puskesmas-dropdown-trigger');
            const menu = document.getElementById('puskesmas-dropdown-menu');
            const hiddenInput = document.getElementById('puskesmas-hidden-input');
            const searchInput = document.getElementById('puskesmas-search');
            const optionsList = document.getElementById('puskesmas-options-list');
            const options = document.querySelectorAll('.puskesmas-option');
            const noResults = document.getElementById('no-results');
            const arrow = document.getElementById('dropdown-arrow');
            const selectedLabel = document.getElementById('selected-label');
            const selectedIcon = document.getElementById('selected-icon');
            
            let isOpen = false;

            // Toggle Dropdown
            function toggleDropdown(state = null) {
                isOpen = state !== null ? state : !isOpen;
                
                if (isOpen) {
                    menu.classList.remove('hidden');
                    // Add delay for transition effect
                    requestAnimationFrame(() => {
                        menu.classList.remove('scale-95', 'opacity-0');
                        menu.classList.add('scale-100', 'opacity-100');
                    });
                    arrow.classList.add('rotate-180');
                    trigger.classList.add('bg-white', 'border-[var(--color-glass-primary)]', 'ring-4', 'ring-[var(--color-glass-primary)]/10');
                    searchInput.focus();
                } else {
                    menu.classList.remove('scale-100', 'opacity-100');
                    menu.classList.add('scale-95', 'opacity-0');
                    arrow.classList.remove('rotate-180');
                    trigger.classList.remove('bg-white', 'border-[var(--color-glass-primary)]', 'ring-4', 'ring-[var(--color-glass-primary)]/10');
                    
                    setTimeout(() => {
                        if (!isOpen) menu.classList.add('hidden');
                    }, 200);
                }
            }

            trigger.addEventListener('click', () => toggleDropdown());

            // Close on outside click
            document.addEventListener('click', (e) => {
                if (!container.contains(e.target)) {
                    toggleDropdown(false);
                }
            });

            // Handle Selection
            options.forEach(option => {
                option.addEventListener('click', () => {
                    const value = option.dataset.value;
                    const label = option.dataset.label;
                    
                    hiddenInput.value = value;
                    selectedLabel.textContent = label;
                    selectedLabel.classList.add('text-gray-900', 'font-bold');
                    
                    // Update Active State
                    options.forEach(opt => {
                         const icon = opt.querySelector('.icon-display');
                         opt.classList.remove('bg-blue-50', 'text-blue-700', 'font-bold');
                         if(icon) icon.classList.remove('text-blue-600');
                    });
                    
                    option.classList.add('bg-blue-50', 'text-blue-700', 'font-bold');
                    option.querySelector('.icon-display').classList.add('text-blue-600');

                    toggleDropdown(false);
                    // Reset Search
                    searchInput.value = '';
                    filterOptions('');
                });
            });

            // Search Filter
            function filterOptions(term) {
                const lowerTerm = term.toLowerCase();
                let hasResults = false;

                options.forEach(option => {
                    const label = option.dataset.label.toLowerCase();
                    if (label.includes(lowerTerm)) {
                        option.style.display = 'flex';
                        hasResults = true;
                    } else {
                        option.style.display = 'none';
                    }
                });

                if (hasResults) {
                    noResults.classList.add('hidden');
                } else {
                    noResults.classList.remove('hidden');
                }
            }

            searchInput.addEventListener('input', (e) => {
                filterOptions(e.target.value);
            });
        });
    </script>
    @endpush
@endsection
