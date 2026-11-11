<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Library;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Dashboard pembaca. */
    public function reader(Request $request): View
    {
        $user = $request->user();

        $libraryCount = Library::where('user_id', $user->id)->count();
        $downloadTotal = (int) Library::where('user_id', $user->id)->sum('download_count');

        $followingIds = DB::table('follows')->where('user_id', $user->id)->pluck('author_id');

        $recentLibrary = Library::where('user_id', $user->id)
            ->with('book.author:user_id,pen_name')
            ->latest()
            ->take(3)
            ->get();

        $fromFollowed = $followingIds->isEmpty()
            ? collect()
            : Book::where('status', 'published')
                ->whereIn('author_id', $followingIds)
                ->with('author:user_id,pen_name')
                ->orderByDesc('published_at')
                ->take(6)
                ->get();

        return view('dashboards.reader', [
            'libraryCount' => $libraryCount,
            'downloadTotal' => $downloadTotal,
            'followingCount' => $followingIds->count(),
            'recentLibrary' => $recentLibrary,
            'fromFollowed' => $fromFollowed,
        ]);
    }

    /** Dashboard penulis. */
    public function author(Request $request): View
    {
        $user = $request->user();

        $statusCounts = Book::where('author_id', $user->id)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $downloadTotal = (int) Library::whereIn('book_id', Book::where('author_id', $user->id)->select('id'))
            ->sum('download_count');

        $followers = DB::table('follows')->where('author_id', $user->id)->count();

        // Buku yang ditolak atau di-takedown butuh perhatian penulis
        $attention = Book::where('author_id', $user->id)
            ->whereIn('status', ['rejected', 'taken_down'])
            ->latest('updated_at')
            ->get();

        $topBooks = Book::where('author_id', $user->id)
            ->select('books.*', DB::raw('(select coalesce(sum(download_count), 0) from library where library.book_id = books.id) as downloads'))
            ->orderByDesc('downloads')
            ->orderBy('title')
            ->take(5)
            ->get()
            ->filter(fn ($book) => (int) $book->downloads > 0)
            ->values();

        return view('dashboards.author', [
            'penName' => $user->authorProfile?->pen_name ?? $user->name,
            'statusCounts' => $statusCounts,
            'downloadTotal' => $downloadTotal,
            'followers' => $followers,
            'attention' => $attention,
            'topBooks' => $topBooks,
        ]);
    }

    /** Dashboard admin: ringkasan pengguna dan buku. */
    public function admin(): View
    {
        $roles = User::selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role');
        $bookStatus = Book::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('dashboards.admin', [
            'roles' => $roles,
            'bookStatus' => $bookStatus,
            'totalBooks' => (int) $bookStatus->sum(),
        ]);
    }
}
