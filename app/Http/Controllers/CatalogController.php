<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Library;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /** Beranda = katalog buku yang terbit (terbuka untuk tamu). */
    public function index(Request $request): View
    {
        $q = $this->text($request, 'q');
        $kategori = $this->text($request, 'kategori');
        $jenis = in_array($this->text($request, 'jenis'), ['gratis', 'berbayar'], true)
            ? $this->text($request, 'jenis') : '';
        $urut = in_array($this->text($request, 'urut'), ['terbaru', 'termurah', 'termahal'], true)
            ? $this->text($request, 'urut') : 'terbaru';
        $hargaMin = $this->number($request, 'harga_min');
        $hargaMax = $this->number($request, 'harga_max');

        $query = Book::query()
            ->where('status', 'published')
            ->with('author:user_id,pen_name');

        if ($q !== '') {
            $like = '%'.addcslashes($q, '%_\\').'%';
            $query->where(function ($w) use ($like) {
                $w->where('title', 'like', $like)
                    ->orWhereHas('author', fn ($a) => $a->where('pen_name', 'like', $like));
            });
        }

        if ($kategori !== '') {
            $category = Category::with('children')->where('slug', $kategori)->first();
            if ($category) {
                // kategori induk mencakup semua kategori anaknya
                $ids = $category->parent_id === null
                    ? $category->children->pluck('id')->push($category->id)->all()
                    : [$category->id];
                $query->whereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids));
            }
        }

        if ($jenis === 'gratis') {
            $query->where('price', 0);
        } elseif ($jenis === 'berbayar') {
            $query->where('price', '>', 0);
        }

        if ($hargaMin !== null) {
            $query->where('price', '>=', $hargaMin);
        }
        if ($hargaMax !== null) {
            $query->where('price', '<=', $hargaMax);
        }

        match ($urut) {
            'termurah' => $query->orderBy('price')->orderByDesc('id'),
            'termahal' => $query->orderByDesc('price')->orderByDesc('id'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };

        return view('catalog.index', [
            'books' => $query->paginate(12)->withQueryString(),
            'categories' => Category::whereNull('parent_id')
                ->with(['children' => fn ($c) => $c->orderBy('name')])
                ->orderBy('name')
                ->get(),
            'filters' => [
                'q' => $q,
                'kategori' => $kategori,
                'jenis' => $jenis,
                'urut' => $urut,
                'harga_min' => $hargaMin,
                'harga_max' => $hargaMax,
            ],
        ]);
    }

    public function show(Request $request, Book $book): View
    {
        abort_unless($book->status === 'published', 404);

        $book->load(['author', 'categories', 'previewFile']);

        $user = $request->user();

        // Tamu yang menekan "Masuk" dikembalikan ke halaman buku ini setelah login
        if (! $user) {
            session()->put('url.intended', url()->current());
        }

        $owned = $user
            ? Library::where('user_id', $user->id)->where('book_id', $book->id)->exists()
            : false;
        $isOwnBook = $user && (int) $book->author_id === (int) $user->id;

        return view('catalog.show', compact('book', 'owned', 'isOwnBook'));
    }

    /** Pratinjau PDF: wajib login, dibuka langsung di browser. */
    public function preview(Book $book)
    {
        abort_unless($book->status === 'published', 404);

        $file = $book->previewFile;
        abort_unless($file && Storage::disk('local')->exists($file->path), 404, 'Pratinjau tidak tersedia.');

        return Storage::disk('local')->response(
            $file->path,
            Str::slug($book->title).'-pratinjau.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function text(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? trim($value) : '';
    }

    private function number(Request $request, string $key): ?int
    {
        $value = $request->query($key);

        return is_string($value) && ctype_digit($value) ? (int) $value : null;
    }
}
