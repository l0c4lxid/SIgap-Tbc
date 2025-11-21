@php
    use App\Enums\UserRole;
    use Illuminate\Support\Str;

    $user = auth()->user();
    $isPemda = $user?->role === UserRole::Pemda;
@endphp

@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="mb-1">{{ $isPemda ? 'Semua Berita / Testimoni' : 'Berita Saya' }}</h5>
                        <p class="text-sm text-muted mb-0">
                            Kirim berita atau testimoni untuk blog. Berita akan dipublikasikan oleh Pemda setelah ditinjau.
                        </p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('news.create') }}" class="btn btn-sm btn-primary">
                            <i class="fa fa-plus me-1"></i> Tulis Berita
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3 bg-light h-100">
                                <p class="text-xs text-muted mb-1">Total berita</p>
                                <h5 class="mb-0">{{ number_format($stats['total']) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Menunggu publikasi</p>
                                <h5 class="mb-0 text-warning">{{ number_format($stats['pending']) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <p class="text-xs text-muted mb-1">Sudah tayang</p>
                                <h5 class="mb-0 text-success">{{ number_format($stats['published']) }}</h5>
                            </div>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('news.index') }}" class="row g-2 align-items-end mb-4">
                        <div class="col-md-4">
                            <label class="form-label text-sm mb-1">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua status</option>
                                <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Menunggu publikasi</option>
                                <option value="published" {{ $statusFilter === 'published' ? 'selected' : '' }}>Sudah tayang</option>
                            </select>
                        </div>
                        <div class="col-md-6 col-lg-5">
                            <label class="form-label text-sm mb-1">Cari judul</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control" value="{{ $search }}" placeholder="Judul atau penulis">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                Terapkan
                            </button>
                        </div>
                    </form>

                    <div class="d-flex flex-column gap-3">
                        @forelse ($posts as $post)
                            @php
                                $isOwner = $post->user_id === ($user?->id);
                                $canModify = $isPemda || $isOwner;
                                $canEdit = $canModify && ($isPemda || $post->status !== 'published');
                            @endphp
                            <div class="border rounded-3 p-3 shadow-sm">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge {{ $post->status === 'published' ? 'bg-success' : 'bg-warning text-dark' }} text-uppercase">
                                                {{ $post->status === 'published' ? 'Tayang' : 'Menunggu Pemda' }}
                                            </span>
                                            <span class="text-xs text-muted">
                                                Dibuat {{ $post->created_at->translatedFormat('d M Y H:i') }}
                                            </span>
                                        </div>
                                        <h6 class="mb-1">{{ $post->title }}</h6>
                                        <p class="text-sm text-muted mb-2">
                                            {{ Str::limit(strip_tags($post->content), 180) }}
                                        </p>
                                        <div class="d-flex flex-wrap gap-3 align-items-center text-xs text-muted">
                                            <span><i class="fa fa-user me-1"></i>Penulis: {{ $post->author->name ?? 'Tidak diketahui' }}</span>
                                            @if ($post->published_at)
                                                <span><i class="fa fa-check-circle me-1 text-success"></i>Dipublikasi {{ $post->published_at->translatedFormat('d M Y H:i') }}</span>
                                            @endif
                                            @if ($post->publisher)
                                                <span><i class="fa fa-bullhorn me-1"></i>Oleh {{ $post->publisher->name }}</span>
                                            @endif
                                            @if ($post->status === 'published')
                                                <a href="{{ route('blog.show', $post) }}" class="text-xs text-primary fw-semibold">
                                                    <i class="fa fa-link me-1"></i>Lihat di blog
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        @if ($canEdit)
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('news.edit', $post) }}" class="btn btn-outline-secondary">Edit</a>
                                                <form action="{{ route('news.destroy', $post) }}" method="POST" data-confirm="Hapus berita ini?" data-confirm-text="Ya, hapus">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger">Hapus</button>
                                                </form>
                                            </div>
                                        @endif
                                        @if ($isPemda)
                                            @if ($post->status === 'pending')
                                                <form action="{{ route('news.publish', $post) }}" method="POST" data-confirm="Publikasikan berita ke blog?" data-confirm-text="Ya, publikasikan">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                                        <i class="fa fa-bullhorn me-1"></i> Publikasikan
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('news.unpublish', $post) }}" method="POST" data-confirm="Tarik berita ini dari publikasi?" data-confirm-text="Ya, tarik">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                                                        <i class="fa fa-rotate-left me-1"></i> Tarik ke draft
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="text-xs text-muted text-end">Menunggu persetujuan Pemda untuk tayang.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <p class="mb-1">Belum ada berita.</p>
                                <p class="text-sm mb-0">Kirim berita baru dan tunggu persetujuan Pemda untuk dipublikasikan.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-4">
                        {{ $posts->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
