<?php

namespace App\Http\Controllers;

use App\Models\AuthorProfile;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthorPageController extends Controller
{
    /** Profil penulis yang bisa dilihat siapa saja. */
    public function show(Request $request, AuthorProfile $authorProfile): View
    {
        $user = $request->user();

        if (! $user) {
            session()->put('url.intended', url()->current());
        }

        $books = Book::where('author_id', $authorProfile->user_id)
            ->where('status', 'published')
            ->with('author:user_id,pen_name')
            ->orderByDesc('published_at')
            ->paginate(12);

        $followers = DB::table('follows')->where('author_id', $authorProfile->user_id)->count();

        $isSelf = $user && (int) $user->id === (int) $authorProfile->user_id;
        $canFollow = $user && ! $isSelf && in_array($user->role, ['reader', 'author'], true);
        $isFollowing = $canFollow
            && DB::table('follows')
                ->where('user_id', $user->id)
                ->where('author_id', $authorProfile->user_id)
                ->exists();

        return view('authors.show', compact('authorProfile', 'books', 'followers', 'isSelf', 'canFollow', 'isFollowing'));
    }

    public function follow(Request $request, AuthorProfile $authorProfile): RedirectResponse
    {
        $user = $request->user();

        // AB-14: tidak bisa mengikuti diri sendiri (trigger di database jadi lapis kedua)
        if ((int) $user->id === (int) $authorProfile->user_id) {
            return back()->with('error', 'Anda tidak bisa mengikuti diri sendiri.');
        }

        DB::table('follows')->insertOrIgnore([
            'user_id' => $user->id,
            'author_id' => $authorProfile->user_id,
        ]);

        return back()->with('status', 'Anda sekarang mengikuti '.$authorProfile->pen_name.'.');
    }

    public function unfollow(Request $request, AuthorProfile $authorProfile): RedirectResponse
    {
        DB::table('follows')
            ->where('user_id', $request->user()->id)
            ->where('author_id', $authorProfile->user_id)
            ->delete();

        return back()->with('status', 'Anda berhenti mengikuti '.$authorProfile->pen_name.'.');
    }
}
