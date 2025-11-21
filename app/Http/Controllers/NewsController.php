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

        return view('blog.index', [
            'posts' => $posts,
            'search' => $search,
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
            ->with('status', 'Berita berhasil dikirim. Menunggu publikasi Pemda.');
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
        abort_if($request->user()->role !== UserRole::Pemda, 403);

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
        abort_if($request->user()->role !== UserRole::Pemda, 403);

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
}
