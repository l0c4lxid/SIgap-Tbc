@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $isPemda = $user?->role === UserRole::Pemda;
    $statusBadge = $post->status === 'published' ? 'bg-success' : 'bg-warning text-dark';
@endphp

@extends('layouts.soft')

@section('content')
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex align-items-start gap-3">
                    <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                        <i class="fa fa-bullhorn text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-1">{{ $isEdit ? 'Edit Berita' : 'Tulis Berita Baru' }}</h5>
                        <p class="text-sm text-muted mb-0">
                            Isi judul, unggah gambar utama, dan konten berita atau testimoni. Berita akan tayang setelah dipublikasikan oleh Pemda.
                        </p>
                    </div>
                </div>
                <hr class="horizontal dark opacity-10 my-0">
                <div class="card-body">
                    <form method="POST" action="{{ $isEdit ? route('news.update', $post) : route('news.store') }}" enctype="multipart/form-data">
                        @csrf
                        @if ($isEdit)
                            @method('PUT')
                        @endif
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Judul</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $post->title) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Gambar utama (opsional)</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                                @if ($post->image)
                                    <div class="mt-2">
                                        <p class="text-xs text-muted mb-1">Gambar saat ini:</p>
                                        <img src="{{ asset('storage/' . $post->image->path) }}" alt="Gambar berita" class="img-fluid rounded" style="max-height: 200px;">
                                    </div>
                                @endif
                                <p class="text-xs text-muted mt-1 mb-0">Format JPG/PNG, maksimal 2MB.</p>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Konten</label>
                                <textarea name="content" rows="8" class="form-control" placeholder="Isi lengkap berita atau testimoni" required>{{ old('content', $post->content) }}</textarea>
                                <p class="text-xs text-muted mt-2">Gunakan paragraf rapi agar mudah dibaca. Foto/video tambahan dapat disisipkan oleh tim konten.</p>
                            </div>
                            @if ($isEdit && $post->exists)
                                <div class="col-12">
                                    <div class="d-inline-flex align-items-center gap-2 px-3 py-2 border rounded">
                                        <span class="badge {{ $statusBadge }}">
                                            {{ $post->status === 'published' ? 'Sudah tayang' : 'Menunggu Pemda' }}
                                        </span>
                                        @if ($post->published_at)
                                            <span class="text-xs text-muted">Dipublikasi {{ $post->published_at->translatedFormat('d M Y H:i') }}</span>
                                        @endif
                                    </div>
                                    @unless ($isPemda)
                                        <p class="text-xs text-muted mt-2 mb-0">Perubahan akan diajukan kembali ke Pemda sebelum tayang.</p>
                                    @endunless
                                </div>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('news.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                {{ $isEdit ? 'Simpan Perubahan' : 'Kirim Berita' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
