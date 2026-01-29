@extends('layouts.soft')

@section('subjudul', 'Detail verifikasi pengguna')

@section('content')
    <div class="mb-6 flex justify-between items-center">
         <a href="{{ route('pemda.verification') }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 no-underline">
            <i class="ri-arrow-left-line"></i> Kembali
        </a>
         <form method="POST" action="{{ route('pemda.verification.destroy', $user) }}" data-confirm="Hapus {{ $user->name }}?" data-confirm-text="Ya, hapus">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                <i class="ri-delete-bin-line"></i> Hapus User
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
             <div class="glass-card p-6">
                 <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200/50">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[var(--color-glass-primary)] to-[var(--color-glass-secondary)] flex items-center justify-center text-white shadow-lg shadow-green-500/30">
                        <i class="ri-user-settings-line text-xl"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-lg text-gray-800 mb-0">Informasi Pengguna</h5>
                        <p class="text-sm text-gray-500 mb-0">Kelola identitas dan status pengguna.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('pemda.verification.update', $user) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                         <div class="md:col-span-2">
                             <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" name="name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('name', $user->name) }}" required>
                             @error('name')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                         <div>
                             <label class="block text-sm font-semibold text-gray-700 mb-2">Peran</label>
                            <input type="text" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-100/50 text-gray-500 cursor-not-allowed" value="{{ $user->role->label() }}" disabled>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Status Akun</label>
                            <label class="flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 cursor-pointer hover:bg-white/80 transition-colors">
                                <input type="checkbox" name="is_active" value="1" class="rounded text-[var(--color-glass-primary)] focus:ring-[var(--color-glass-primary)]" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">Aktifkan pengguna ini</span>
                            </label>
                        </div>

                         <div class="md:col-span-2">
                             <label class="block text-sm font-semibold text-gray-700 mb-2">Instansi / Organisasi</label>
                            <input type="text" name="organization" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('organization', $user->detail->organization ?? '') }}" {{ $user->role === \App\Enums\UserRole::Pemda ? 'disabled' : '' }}>
                             @error('organization')
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="md:col-span-2">
                             <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat</label>
                            <input type="text" name="address" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('address', $user->detail->address ?? '') }}">
                        </div>

                        @if ($supervisorLabel)
                             <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ $supervisorLabel }}</label>
                                <select name="supervisor_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all">
                                    <option value="">Pilih</option>
                                    @foreach ($supervisorOptions as $option)
                                        <option value="{{ $option->id }}" {{ old('supervisor_id', $user->detail->supervisor_id ?? null) == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="md:col-span-2">
                             <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                            <textarea name="notes" rows="3" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all">{{ old('notes', $user->detail->notes ?? '') }}</textarea>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="glass-button px-6 py-2 rounded-lg font-bold text-sm shadow-md hover:shadow-lg transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Credentials --}}
             <div class="glass-card p-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-200/50">
                    <div class="w-10 h-10 rounded-lg bg-gray-800 flex items-center justify-center text-white shadow-lg">
                        <i class="ri-lock-password-line text-xl"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-lg text-gray-800 mb-0">Kredensial Login</h5>
                        <p class="text-sm text-gray-500 mb-0">Ubah username & password.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('pemda.verification.credentials', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor HP (Username)</label>
                         <input type="text" name="phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-gray-800 transition-all font-mono" value="{{ old('phone', $user->phone) }}" required>
                         @error('phone')
                            <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru (opsional)</label>
                        <input type="password" name="password" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-gray-800 transition-all" autocomplete="new-password">
                    </div>
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-gray-800 transition-all" autocomplete="new-password">
                    </div>
                    
                    <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white px-4 py-2.5 rounded-lg text-sm font-semibold shadow-md transition-all mt-2">
                        Update Kredensial
                    </button>
                </form>
            </div>

            {{-- Summary --}}
            <div class="glass-card p-6">
                <div class="flex justify-between items-center mb-4">
                     <h6 class="font-bold text-gray-800 mb-0">Ringkasan</h6>
                     <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-500">Nama</span>
                        <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-500">Peran</span>
                         <span class="font-semibold text-gray-800">{{ $user->role->label() }}</span>
                    </div>
                     <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-500">Dibuat</span>
                         <span class="font-semibold text-gray-800">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                     <div class="flex justify-between border-b border-gray-100 pb-2">
                        <span class="text-gray-500">Instansi</span>
                         <span class="font-semibold text-gray-800">{{ $user->detail->organization ?? '-' }}</span>
                    </div>
                     @if ($supervisorLabel)
                         <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-gray-500">{{ $supervisorLabel }}</span>
                             <span class="font-semibold text-gray-800">{{ optional($user->detail?->supervisor)->name ?? '-' }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
