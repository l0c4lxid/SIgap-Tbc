@php
    use App\Enums\UserRole;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $isPemda = $user?->role === UserRole::Pemda;
    $isPuskesmas = $user?->role === UserRole::Puskesmas;
@endphp

@extends('layouts.soft')

@section('subjudul', 'Daftar berita dan testimoni')

@section('content')
    <div class="glass-card p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                 <h5 class="font-bold text-xl text-gray-800 mb-1">{{ $isPemda ? 'Semua Berita / Testimoni' : 'Berita Saya' }}</h5>
                 <p class="text-sm text-gray-500 mb-0">
                    Kirim berita atau testimoni untuk blog. Puskesmas bisa menerbitkan langsung, dan Pemda tetap dapat meninjau semua konten.
                </p>
            </div>
            <a href="{{ route('news.create') }}" class="glass-button px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-[var(--color-glass-primary)] shadow-md hover:opacity-90 transition-all no-underline">
                <i class="ri-add-line me-1"></i> Tulis Berita
            </a>
        </div>
        
         <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white/50 rounded-xl p-4 border border-gray-100 flex items-center gap-4">
                 <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-xl">
                    <i class="ri-article-line"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0 font-medium">Total berita</p>
                    <h5 class="font-bold text-gray-800 text-lg mb-0">{{ number_format($stats['total']) }}</h5>
                </div>
            </div>
            <div class="bg-white/50 rounded-xl p-4 border border-gray-100 flex items-center gap-4">
                 <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center text-yellow-600 text-xl">
                    <i class="ri-time-line"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0 font-medium">Menunggu publikasi</p>
                    <h5 class="font-bold text-gray-800 text-lg mb-0">{{ number_format($stats['pending']) }}</h5>
                </div>
            </div>
            <div class="bg-white/50 rounded-xl p-4 border border-gray-100 flex items-center gap-4">
                 <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-600 text-xl">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-0 font-medium">Sudah tayang</p>
                    <h5 class="font-bold text-gray-800 text-lg mb-0 text-[var(--color-glass-primary)]">{{ number_format($stats['published']) }}</h5>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('news.index') }}" class="flex flex-col md:flex-row gap-4 items-center mb-6">
             <div class="relative w-full md:w-auto">
                <select name="status" class="appearance-none w-full md:w-48 px-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all cursor-pointer">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua status</option>
                    <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu publikasi</option>
                    <option value="published" {{ $statusFilter === 'published' ? 'selected' : '' }}>Sudah tayang</option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-gray-500">
                     <i class="ri-arrow-down-s-line"></i>
                </div>
            </div>
            
            <div class="relative flex-grow w-full md:w-auto">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="ri-search-line"></i>
                </span>
                <input type="text" name="q" value="{{ $search }}" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 bg-white/50 focus:outline-none focus:ring-2 focus:ring-[var(--color-glass-primary)] transition-all" placeholder="Cari judul atau penulis...">
            </div>
            
            <button type="submit" class="glass-button px-6 py-2.5 rounded-lg font-bold text-gray-600 bg-white/50 hover:bg-white border border-gray-200 shadow-sm w-full md:w-auto">
                Terapkan
            </button>
        </form>

        <div class="space-y-4">
            @forelse ($posts as $post)
                @php
                    $isOwner = $post->user_id === ($user?->id);
                    $canModify = $isPemda || $isOwner;
                    $canEdit = $canModify && ($isPemda || $post->status !== 'published');
                    $canPublish = $isPemda || ($isPuskesmas && $isOwner);
                @endphp
                <div class="bg-white/40 rounded-xl border border-white/60 p-4 hover:shadow-sm transition-all relative group">
                    <div class="flex flex-col md:flex-row justify-between gap-4">
                        <div class="flex-grow">
                             <div class="flex items-center gap-3 mb-2">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $post->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $post->status === 'published' ? 'Tayang' : 'Menunggu Publikasi' }}
                                </span>
                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                    <i class="ri-time-line"></i> {{ $post->created_at->translatedFormat('d M Y H:i') }}
                                </span>
                            </div>
                            
                            <h6 class="font-bold text-lg text-gray-800 mb-2">{{ $post->title }}</h6>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                {{ Str::limit(strip_tags($post->content), 180) }}
                            </p>
                            
                             <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                                <span class="flex items-center gap-1"><i class="ri-user-line"></i> {{ $post->author->name ?? 'Tidak diketahui' }}</span>
                                 @if ($post->published_at)
                                    <span class="flex items-center gap-1 text-green-600"><i class="ri-check-double-line"></i> Publikasi {{ $post->published_at->translatedFormat('d M Y H:i') }}</span>
                                @endif
                                @if ($post->publisher)
                                    <span class="flex items-center gap-1"><i class="ri-megaphone-line"></i> oleh {{ $post->publisher->name }}</span>
                                @endif
                                @if ($post->status === 'published')
                                    <a href="{{ route('blog.show', $post) }}" class="text-[var(--color-glass-primary)] font-semibold hover:underline flex items-center gap-1" target="_blank">
                                        Lihat di Blog <i class="ri-external-link-line"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-row md:flex-col gap-2 items-end justify-start md:justify-center">
                             @if ($canEdit)
                                <div class="flex gap-2">
                                    <a href="{{ route('news.edit', $post) }}" class="p-2 rounded-lg bg-white/50 hover:bg-white text-blue-600 border border-transparent hover:border-gray-200 transition-all" title="Edit">
                                        <i class="ri-edit-line"></i>
                                    </a>
                                    <form action="{{ route('news.destroy', $post) }}" method="POST" data-confirm="Hapus berita ini?" data-confirm-text="Ya, hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-white/50 hover:bg-white text-red-600 border border-transparent hover:border-gray-200 transition-all" title="Hapus">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif

                             @if ($canPublish)
                                @if ($post->status === 'pending')
                                    <form action="{{ route('news.publish', $post) }}" method="POST" data-confirm="Publikasikan berita ke blog?" data-confirm-text="Ya, publikasikan">
                                        @csrf
                                        <button type="submit" class="glass-button px-4 py-2 rounded-lg text-xs font-bold bg-green-100 text-green-800 hover:bg-green-200 border-green-200 w-full whitespace-nowrap">
                                            <i class="ri-megaphone-line me-1"></i> Publikasikan
                                        </button>
                                    </form>
                                @else
                                     <form action="{{ route('news.unpublish', $post) }}" method="POST" data-confirm="Tarik berita ini dari publikasi?" data-confirm-text="Ya, tarik">
                                        @csrf
                                        <button type="submit" class="glass-button px-4 py-2 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 border-gray-200 w-full whitespace-nowrap">
                                            <i class="ri-arrow-go-back-line me-1"></i> Tarik Draft
                                        </button>
                                    </form>
                                @endif
                            @elseif(!$canEdit)
                                <span class="text-xs text-gray-400 italic text-end">Menunggu admin</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                     <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="ri-article-line text-2xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Belum ada berita.</p>
                    <p class="text-sm text-gray-400">Kirim berita baru dan publikasikan saat siap tayang.</p>
                </div>
            @endforelse
        </div>
         @if($posts->hasPages())
            <div class="mt-6">
                {{ $posts->onEachSide(1)->links('pagination.glass') }}
            </div>
        @endif
    </div>
@endsection
