@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $isPemda = $user?->role === UserRole::Pemda;
    $isPuskesmas = $user?->role === UserRole::Puskesmas;
    $statusBadge = $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
@endphp

@extends('layouts.soft')

@section('subjudul', 'Tulis atau perbarui berita')

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-6">
            <a href="{{ route('news.index') }}" class="glass-button px-4 py-2 rounded-lg text-sm font-semibold inline-flex items-center gap-2 no-underline text-gray-600 hover:text-[var(--color-glass-primary)] transition-colors">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
        </div>

        <div class="glass-card p-8">
            <div class="flex items-start gap-4 mb-6 border-b border-gray-200/50 pb-6">
                 <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[var(--color-glass-primary)] to-emerald-600 shadow-lg flex items-center justify-center text-white text-xl flex-shrink-0">
                    <i class="ri-megaphone-line"></i>
                </div>
                <div>
                     <h5 class="font-bold text-xl text-gray-800 mb-1">{{ $isEdit ? 'Edit Berita' : 'Tulis Berita Baru' }}</h5>
                    <p class="text-sm text-gray-500 mb-0">
                        Isi judul, unggah gambar utama, dan konten berita atau testimoni. Puskesmas dapat menerbitkan langsung; konten lain menunggu publikasi admin.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ $isEdit ? route('news.update', $post) : route('news.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif
                
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Judul Berita</label>
                    <input type="text" name="title" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" value="{{ old('title', $post->title) }}" placeholder="Judul yang menarik..." required>
                </div>

                <div x-data="{ 
                    photoName: null, 
                    photoPreview: null, 
                    updatePreview() {
                        const file = this.$refs.photo.files[0];
                        if (!file) return;
                        this.photoName = file.name;
                        const reader = new FileReader();
                        reader.onload = (e) => { this.photoPreview = e.target.result; };
                        reader.readAsDataURL(file);
                    }
                }">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Gambar Utama (Opsional)</label>
                    <div class="relative group">
                         <!-- Real Input (Overlay with Opacity 0) -->
                         <!-- This ensures native click behavior works on all devices -->
                         <input type="file" name="image" x-ref="photo" 
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                                accept="image/*"
                                @change="updatePreview()">
                                
                         <!-- Visual Button -->
                         <div class="flex items-center gap-3">
                            <button type="button" 
                                    class="glass-button px-4 py-2 rounded-xl text-sm font-bold inline-flex items-center gap-2 hover:bg-emerald-600 transition-colors">
                                <i class="ri-upload-cloud-2-line text-lg"></i>
                                Pilih Gambar
                            </button>
                            <span class="text-sm text-gray-500 italic" x-text="photoName ?? 'Belum ada file dipilih'"></span>
                         </div>
                    </div>

                    <!-- Image Preview Area -->
                    <div class="mt-4" x-show="photoPreview || {{ $post->image ? 'true' : 'false' }}">
                        <p class="text-xs text-gray-500 mb-2 font-semibold">Preview Gambar:</p>
                        
                        <!-- New Preview -->
                        <div x-show="photoPreview" style="display: none;">
                            <span class="block w-full h-64 bg-cover bg-center bg-no-repeat rounded-xl shadow-md border border-gray-200"
                                  :style="'background-image: url(\'' + photoPreview + '\');'">
                            </span>
                        </div>

                        <!-- Existing Image (Fallback) -->
                        <div x-show="!photoPreview && {{ $post->image ? 'true' : 'false' }}">
                            @if ($post->image)
                                <img src="{{ asset('storage/' . $post->image->path) }}" alt="Gambar berita" class="rounded-xl max-h-64 w-full object-cover shadow-md border border-gray-200">
                            @endif
                        </div>
                    </div>
                    
                    <p class="text-xs text-gray-400 mt-2">Format JPG/PNG, maksimal 2MB. Gambar berkualitas meningkatkan keterbacaan.</p>
                </div>

                <div>
                     <label class="block text-sm font-bold text-gray-700 mb-2">Konten Berita</label>
                    <textarea name="content" rows="12" class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" placeholder="Tuliskan isi berita atau testimoni secara lengkap..." required>{{ old('content', $post->content) }}</textarea>
                    <p class="text-xs text-gray-400 mt-2">Gunakan paragraf yang rapi.</p>
                </div>

                @if ($isEdit && $post->exists)
                     <div class="bg-gray-50/50 rounded-xl p-4 border border-gray-100 flex items-center justify-between">
                        <div>
                             <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold {{ $statusBadge }}">
                                {{ $post->status === 'published' ? 'Sudah tayang' : 'Menunggu publikasi' }}
                            </span>
                            @if ($post->published_at)
                                <span class="text-xs text-gray-500 ml-2">Dipublikasi {{ $post->published_at->translatedFormat('d M Y H:i') }}</span>
                            @endif
                        </div>
                        @if (! $isPemda && ! $isPuskesmas)
                             <p class="text-xs text-gray-500 italic mb-0">Perubahan akan menunggu persetujuan admin kembali.</p>
                        @endif
                    </div>
                @endif

                <div class="flex justify-end pt-6 border-t border-gray-100">
                    <button type="submit" class="glass-button-cta px-8 py-3 rounded-xl font-bold text-white shadow-lg transform hover:-translate-y-0.5 transition-all">
                        {{ $isEdit ? 'Simpan Perubahan' : 'Kirim Berita' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
