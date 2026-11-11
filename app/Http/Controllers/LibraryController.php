<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Library;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LibraryController extends Controller
{
    /** Pustaka Saya: semua buku yang dimiliki pengguna. */
    public function index(Request $request): View
    {
        $items = Library::where('user_id', $request->user()->id)
            ->with('book.author:user_id,pen_name')
            ->latest()
            ->paginate(12);

        return view('library.index', compact('items'));
    }

    /** Ambil buku gratis: masuk ke Pustaka Saya tanpa melewati pembayaran. */
    public function claim(Request $request, Book $book): RedirectResponse
    {
        abort_unless($book->status === 'published' && $book->isFree(), 404);

        $user = $request->user();
        $back = redirect()->route('catalog.show', ['book' => $book->slug]);

        // AB-07: penulis tidak bisa mengambil bukunya sendiri
        if ((int) $book->author_id === (int) $user->id) {
            return $back->with('error', 'Anda tidak bisa mengambil buku milik sendiri.');
        }

        try {
            $entry = Library::firstOrCreate(
                ['user_id' => $user->id, 'book_id' => $book->id],
                ['order_id' => null, 'download_count' => 0]
            );
        } catch (UniqueConstraintViolationException) {
            $entry = null;
        }

        // AB-06: buku yang sama tidak bisa dimiliki dua kali
        if (! $entry || ! $entry->wasRecentlyCreated) {
            return $back->with('error', 'Buku ini sudah ada di Pustaka Saya.');
        }

        return redirect()->route('library.index')
            ->with('status', 'Buku berhasil ditambahkan ke Pustaka Saya.');
    }

    /** Unduh PDF lengkap: hanya untuk pemilik buku (S-05). */
    public function download(Request $request, Book $book)
    {
        $entry = Library::where('user_id', $request->user()->id)
            ->where('book_id', $book->id)
            ->firstOrFail();

        // AB-13: buku yang di-takedown tidak bisa diunduh lagi
        abort_if($book->status === 'taken_down', 403, 'Buku ini telah di-takedown dan tidak dapat diunduh.');

        $file = $book->fullFile;
        abort_unless($file && Storage::disk('local')->exists($file->path), 404, 'File buku tidak ditemukan.');

        $entry->increment('download_count');

        return Storage::disk('local')->download($file->path, Str::slug($book->title).'.pdf');
    }
}
