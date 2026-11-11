<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Models\Book;
use App\Models\BookFile;
use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request): View
    {
        $books = Book::where('author_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('author.books.index', compact('books'));
    }

    public function create(): View
    {
        return view('author.books.create', [
            'categories' => $this->categoryTree(),
            'minPrice' => $this->minPrice(),
        ]);
    }

    public function store(BookRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            $book = new Book();
            $book->fill(Arr::only($data, ['title', 'synopsis', 'price', 'license_type', 'source_url']));
            $book->author_id = $request->user()->id;
            $book->slug = $this->uniqueSlug($data['title']);
            $book->cover = $request->file('cover')->store('covers', 'public');
            $book->status = 'draft';
            $book->save();

            $book->categories()->sync($data['category_ids']);

            $this->saveFile($book, 'full', $request->file('full_file'));
            if ($request->hasFile('preview_file')) {
                $this->saveFile($book, 'preview', $request->file('preview_file'));
            }
        });

        return redirect()->route('author.buku.index')
            ->with('status', 'Buku tersimpan sebagai draft. Terbitkan atau ajukan tinjau dari daftar di bawah.');
    }

    public function edit(Request $request, Book $book): View
    {
        $this->own($request, $book);
        $this->ensureEditable($book);

        return view('author.books.edit', [
            'book' => $book->load(['categories', 'fullFile', 'previewFile']),
            'categories' => $this->categoryTree(),
            'minPrice' => $this->minPrice(),
        ]);
    }

    public function update(BookRequest $request, Book $book): RedirectResponse
    {
        $this->own($request, $book);
        $this->ensureEditable($book);

        $data = $request->validated();
        $notice = 'Buku berhasil diperbarui.';

        DB::transaction(function () use ($request, $book, $data, &$notice) {
            $oldPrice = $book->price;

            $book->fill(Arr::only($data, ['title', 'synopsis', 'price', 'license_type', 'source_url']));

            if ($request->hasFile('cover')) {
                Storage::disk('public')->delete($book->cover);
                $book->cover = $request->file('cover')->store('covers', 'public');
            }

            // AB-12: buku terbit yang berubah dari gratis ke berbayar, atau berbayar dengan
            // file diganti, harus ditinjau ulang admin sebelum tampil lagi.
            $paidNow = (int) $data['price'] > 0;
            if ($book->status === 'published' && $paidNow && ($oldPrice === 0 || $request->hasFile('full_file'))) {
                $book->status = 'pending';
                $book->reviewed_at = null;
                $notice = 'Perubahan memerlukan tinjau ulang admin. Buku tidak tampil di katalog sampai disetujui.';
            }

            $book->save();
            $book->categories()->sync($data['category_ids']);

            if ($request->hasFile('full_file')) {
                $this->saveFile($book, 'full', $request->file('full_file'));
            }
            if ($request->hasFile('preview_file')) {
                $this->saveFile($book, 'preview', $request->file('preview_file'));
            }
        });

        return redirect()->route('author.buku.index')->with('status', $notice);
    }

    /** Buku gratis: terbit langsung. */
    public function publish(Request $request, Book $book): RedirectResponse
    {
        $this->own($request, $book);
        abort_unless(in_array($book->status, ['draft', 'rejected'], true) && $book->isFree(), 403);

        $book->status = 'published';
        $book->published_at = now();
        $book->rejected_reason = null;
        $book->save();

        return redirect()->route('author.buku.index')->with('status', 'Buku diterbitkan.');
    }

    /** Buku berbayar: diajukan ke admin untuk ditinjau. */
    public function submit(Request $request, Book $book): RedirectResponse
    {
        $this->own($request, $book);
        abort_unless(in_array($book->status, ['draft', 'rejected'], true) && ! $book->isFree(), 403);

        $book->status = 'pending';
        $book->reviewed_at = null;
        $book->rejected_reason = null;
        $book->save();

        return redirect()->route('author.buku.index')
            ->with('status', 'Buku diajukan. Admin akan meninjau sebelum buku tampil di katalog.');
    }

    public function archive(Request $request, Book $book): RedirectResponse
    {
        $this->own($request, $book);
        abort_unless($book->status === 'published', 403);

        $book->status = 'archived';
        $book->save();

        return redirect()->route('author.buku.index')->with('status', 'Buku diarsipkan dan tidak tampil lagi di katalog.');
    }

        public function restore(Request $request, Book $book): RedirectResponse
    {
        $this->own($request, $book);
        abort_unless($book->status === 'archived', 403);

        $book->status = 'published';
        $book->published_at = $book->published_at ?? now();
        $book->save();

        return redirect()->route('author.buku.index')
            ->with('status', 'Buku diterbitkan kembali dan tampil lagi di katalog.');
    }

    public function destroy(Request $request, Book $book): RedirectResponse
    {
        $this->own($request, $book);
        abort_unless(in_array($book->status, ['draft', 'rejected'], true), 403);

        DB::transaction(function () use ($book) {
            Storage::disk('local')->deleteDirectory("books/{$book->id}");
            Storage::disk('public')->delete($book->cover);
            $book->delete(); // book_files dan book_category ikut terhapus lewat foreign key
        });

        return redirect()->route('author.buku.index')->with('status', 'Buku dihapus.');
    }

    // ------------------------------------------------------------------

    private function own(Request $request, Book $book): void
    {
        abort_unless((int) $book->author_id === (int) $request->user()->id, 404);
    }

    private function ensureEditable(Book $book): void
    {
        abort_if(
            in_array($book->status, ['taken_down', 'archived'], true),
            403,
            'Buku dengan status ini tidak bisa diedit.'
        );
    }

    private function minPrice(): int
    {
        return (int) Setting::get('min_price', 10000);
    }

    private function categoryTree()
    {
        return Category::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'buku';
        $slug = $base;
        $i = 2;

        while (Book::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** File PDF disimpan di disk 'local' (privat), tidak bisa dibuka lewat URL. */
    private function saveFile(Book $book, string $type, UploadedFile $file): void
    {
        $old = BookFile::where('book_id', $book->id)->where('type', $type)->first();

        $path = $file->storeAs("books/{$book->id}", $type.'-'.Str::random(10).'.pdf', 'local');

        BookFile::updateOrCreate(
            ['book_id' => $book->id, 'type' => $type],
            ['path' => $path, 'size' => $file->getSize(), 'format' => 'pdf']
        );

        if ($old) {
            Storage::disk('local')->delete($old->path);
        }
    }
}
