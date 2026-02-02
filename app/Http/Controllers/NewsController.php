<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Contracts\View\View as ContractView;

class NewsController extends Controller
{
    public function publicIndex(Request $request): View
    {
        $search = $request->input('q', '');

        $postsQuery = NewsPost::query()
            ->with(['author', 'image'])
            ->where('status', 'published')
            ->orderByDesc('published_at');

        if ($search !== '') {
            $term = '%' . $search . '%';
            $postsQuery->where(function ($query) use ($term) {
                $query->where('title', 'like', $term)
                    ->orWhere('summary', 'like', $term);
            });
        }

        $posts = $postsQuery->paginate(9)->withQueryString();

        // Pinned Material Logic
        $materiDir = storage_path('app/public/materi/kader/pages');
        $pinnedMaterial = null;
        
        if (file_exists($materiDir)) {
            $files = scandir($materiDir);
            $pages = [];
            foreach ($files as $file) {
                if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp'])) {
                    $pages[] = asset('storage/materi/kader/pages/' . $file);
                }
            }
            sort($pages);
            $pinnedMaterial = $pages[0] ?? null;
        }

        // Fallback if no specific page image found, but we want to show the pin (since PDF likely exists)
        if (!$pinnedMaterial) {
             // Check if PDF exists to justify showing the link
             if (file_exists(public_path('pdf/Lembar balik.pdf'))) {
                 $pinnedMaterial = asset('assets/img/situba-logo.png');
             }
        }

        return view('blog.index', [
            'posts' => $posts,
            'search' => $search,
            'pinnedMaterial' => $pinnedMaterial,
        ]);
    }

    public function publicShow(NewsPost $newsPost): ContractView
    {
        abort_unless($newsPost->status === 'published', 404);

        $newsPost->loadMissing(['author', 'image']);

        return view('blog.show', [
            'post' => $newsPost,
        ]);
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $statusFilter = $request->input('status', 'all');
        $search = $request->input('q', '');

        $postsQuery = NewsPost::query()
            ->with(['author', 'author.detail', 'publisher', 'image'])
            ->visibleForUser($user);

        if ($statusFilter !== 'all') {
            $postsQuery->where('status', $statusFilter);
        }

        if ($search !== '') {
            $term = '%' . $search . '%';
            $postsQuery->where(function ($query) use ($term, $user) {
                $query->where('title', 'like', $term)
                    ->orWhere('summary', 'like', $term);

                if ($user->role === UserRole::Pemda) {
                    $query->orWhereHas('author', fn ($author) => $author->where('name', 'like', $term));
                }
            });
        }

        $posts = $postsQuery->latest()->paginate(10)->withQueryString();

        $statsQuery = NewsPost::query()->visibleForUser($user);
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'published' => (clone $statsQuery)->where('status', 'published')->count(),
        ];

        return view('news.index', [
            'posts' => $posts,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        return view('news.form', [
            'post' => new NewsPost(),
            'isEdit' => false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $post = NewsPost::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'status' => 'pending',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');
            $post->image()->create(['path' => $path]);
        }

        return redirect()
            ->route('news.index')
            ->with('status', 'Berita berhasil dikirim. Publikasikan saat siap tayang.');
    }

    public function edit(Request $request, NewsPost $newsPost): View
    {
        $this->assertCanEdit($newsPost, $request->user());

        return view('news.form', [
            'post' => $newsPost,
            'isEdit' => true,
        ]);
    }

    public function update(Request $request, NewsPost $newsPost): RedirectResponse
    {
        $user = $request->user();
        $this->assertCanEdit($newsPost, $user);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $newsPost->fill($data);

        if ($user->role !== UserRole::Pemda) {
            $newsPost->status = 'pending';
            $newsPost->published_at = null;
            $newsPost->published_by = null;
        }

        $newsPost->save();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news', 'public');

            if ($newsPost->image) {
                Storage::disk('public')->delete($newsPost->image->path);
                $newsPost->image()->delete();
            }

            $newsPost->image()->create(['path' => $path]);
        }

        return redirect()
            ->route('news.index')
            ->with('status', 'Berita berhasil diperbarui.');
    }

    public function destroy(Request $request, NewsPost $newsPost): RedirectResponse
    {
        $this->assertCanEdit($newsPost, $request->user());

        if ($newsPost->image) {
            Storage::disk('public')->delete($newsPost->image->path);
        }

        $newsPost->delete();

        return redirect()
            ->route('news.index')
            ->with('status', 'Berita berhasil dihapus.');
    }

    public function publish(Request $request, NewsPost $newsPost): RedirectResponse
    {
        $this->assertCanPublish($request->user(), $newsPost);

        $newsPost->update([
            'status' => 'published',
            'published_at' => now(),
            'published_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('news.index')
            ->with('status', 'Berita berhasil dipublikasikan ke blog.');
    }

    public function unpublish(Request $request, NewsPost $newsPost): RedirectResponse
    {
        $this->assertCanPublish($request->user(), $newsPost);

        $newsPost->update([
            'status' => 'pending',
            'published_at' => null,
            'published_by' => null,
        ]);

        return redirect()
            ->route('news.index')
            ->with('status', 'Berita dikembalikan ke draft.');
    }

    private function assertCanEdit(NewsPost $newsPost, User $user): void
    {
        $isOwner = $newsPost->user_id === $user->id;
        $isPemda = $user->role === UserRole::Pemda;

        abort_unless($isOwner || $isPemda, 403);

        if ($newsPost->status === 'published' && ! $isPemda) {
            abort(403, 'Hanya Pemda yang dapat mengubah berita terpublikasi.');
        }
    }

    private function assertCanPublish(User $user, NewsPost $newsPost): void
    {
        $isPemda = $user->role === UserRole::Pemda;
        $isOwnerPuskesmas = $user->role === UserRole::Puskesmas && $newsPost->user_id === $user->id;

        abort_unless($isPemda || $isOwnerPuskesmas, 403);
    }
}
