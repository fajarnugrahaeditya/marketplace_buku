<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookReviewController extends Controller
{
    private const FILTERS = ['pending', 'published', 'rejected', 'taken_down', 'all'];

    /** Daftar buku untuk ditinjau; bawaan menampilkan yang menunggu tinjau. */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = is_string($status) && in_array($status, self::FILTERS, true) ? $status : 'pending';

        $books = Book::query()
            ->with('author:user_id,pen_name')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            // buku yang menunggu tinjau: yang paling lama diajukan tampil dulu
            ->when($status === 'pending', fn ($q) => $q->orderBy('updated_at'), fn ($q) => $q->orderByDesc('updated_at'))
            ->paginate(15)
            ->withQueryString();

        $counts = Book::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.books.index', [
            'books' => $books,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function show(Book $book): View
    {
        $book->load(['author.user', 'categories', 'files']);

        $openReports = Report::where('book_id', $book->id)->where('status', 'open')->count();

        return view('admin.books.show', compact('book', 'openReports'));
    }

    /** Admin membuka PDF pratinjau atau PDF lengkap dari disk privat untuk keperluan tinjau. */
    public function file(Book $book, string $type)
    {
        abort_unless(in_array($type, ['preview', 'full'], true), 404);

        $file = $book->files()->where('type', $type)->first();
        abort_unless($file && Storage::disk('local')->exists($file->path), 404, 'File tidak ditemukan.');

        return Storage::disk('local')->response(
            $file->path,
            Str::slug($book->title).'-'.$type.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function approve(Book $book): RedirectResponse
    {
        abort_unless($book->status === 'pending', 403);

        $book->status = 'published';
        $book->published_at = now();
        $book->reviewed_at = now();
        $book->rejected_reason = null;
        $book->save();

        return redirect()->route('admin.books.index')
            ->with('status', 'Buku "'.$book->title.'" disetujui dan sekarang tampil di katalog.');
    }

    public function reject(Request $request, Book $book): RedirectResponse
    {
        abort_unless($book->status === 'pending', 403);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
            'reason.min' => 'Alasan penolakan minimal 10 karakter agar penulis paham apa yang perlu diperbaiki.',
            'reason.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $book->status = 'rejected';
        $book->rejected_reason = $data['reason'];
        $book->reviewed_at = now();
        $book->save();

        return redirect()->route('admin.books.index')
            ->with('status', 'Buku "'.$book->title.'" ditolak. Penulis bisa memperbaiki lalu mengajukan ulang.');
    }

    /** Takedown buku yang sudah terbit (laporan pembaca atau temuan admin). */
    public function takedown(Request $request, Book $book): RedirectResponse
    {
        abort_unless($book->status === 'published', 403);

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'reason.required' => 'Alasan takedown wajib diisi.',
            'reason.min' => 'Alasan takedown minimal 10 karakter.',
            'reason.max' => 'Alasan takedown maksimal 1000 karakter.',
        ]);

        $book->status = 'taken_down';
        $book->takedown_reason = $data['reason'];
        $book->save();

        // laporan yang masih terbuka untuk buku ini dianggap sudah ditindaklanjuti
        Report::where('book_id', $book->id)->where('status', 'open')->update(['status' => 'reviewed']);

        return redirect()->route('admin.books.show', $book)
            ->with('status', 'Buku di-takedown. Pemilik tidak bisa mengunduhnya lagi.');
    }

    /** Pulihkan buku yang sebelumnya di-takedown. */
    public function restore(Book $book): RedirectResponse
    {
        abort_unless($book->status === 'taken_down', 403);

        $book->status = 'published';
        $book->takedown_reason = null;
        $book->save();

        return redirect()->route('admin.books.show', $book)
            ->with('status', 'Buku dipulihkan dan tampil lagi di katalog.');
    }
}
