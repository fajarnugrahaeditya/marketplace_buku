<?php

use App\Http\Controllers\BecomeAuthorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Author\BookController as AuthorBookController;
use App\Http\Controllers\AuthorPageController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Author\ProfileController as AuthorProfileController;

// Setelah login, arahkan sesuai role
Route::get('/dashboard', function (Request $request) {
    return match ($request->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'author' => redirect()->route('author.dashboard'),
        default => redirect()->route('catalog.index'),
    };
})->middleware(['auth', 'role:reader,author,admin'])->name('dashboard');

Route::middleware(['auth', 'role:reader,author,admin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Pembaca yang ingin menjadi penulis
Route::middleware(['auth', 'role:reader'])->group(function () {
    Route::get('/jadi-penulis', [BecomeAuthorController::class, 'create'])->name('author.register');
    Route::post('/jadi-penulis', [BecomeAuthorController::class, 'store'])->name('author.register.store');
    Route::get('/dashboard-saya', [DashboardController::class, 'reader'])->name('reader.dashboard');
});

Route::middleware(['auth', 'role:author'])->prefix('penulis')->name('author.')->group(function () {
    Route::get('/', [DashboardController::class, 'author'])->name('dashboard');
    Route::get('profil', [AuthorProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profil', [AuthorProfileController::class, 'update'])->name('profile.update');
    Route::resource('buku', AuthorBookController::class)
        ->except('show')
        ->parameters(['buku' => 'book']);

    Route::post('buku/{book}/terbitkan', [AuthorBookController::class, 'publish'])->name('buku.publish');
    Route::post('buku/{book}/ajukan', [AuthorBookController::class, 'submit'])->name('buku.submit');
    Route::post('buku/{book}/arsipkan', [AuthorBookController::class, 'archive'])->name('buku.archive');
    Route::post('buku/{book}/terbitkan-ulang', [AuthorBookController::class, 'restore'])->name('buku.restore');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'admin'])->name('dashboard');
});
// Publik: katalog, detail buku, profil penulis
Route::get('/', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/buku/{book:slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/profil-penulis/{authorProfile}', [AuthorPageController::class, 'show'])->name('authors.show');

// Pratinjau PDF: wajib login
Route::middleware(['auth', 'role:reader,author,admin'])->group(function () {
    Route::get('/buku/{book:slug}/pratinjau', [CatalogController::class, 'preview'])->name('catalog.preview');
});

// Pembaca dan penulis: ambil buku, Pustaka Saya, unduh, ikuti penulis
Route::middleware(['auth', 'role:reader,author'])->group(function () {
    Route::post('/buku/{book:slug}/ambil', [LibraryController::class, 'claim'])->name('library.claim');
    Route::get('/pustaka', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/pustaka/{book}/unduh', [LibraryController::class, 'download'])->name('library.download');
    Route::post('/profil-penulis/{authorProfile}/ikuti', [AuthorPageController::class, 'follow'])->name('authors.follow');
    Route::delete('/profil-penulis/{authorProfile}/ikuti', [AuthorPageController::class, 'unfollow'])->name('authors.unfollow');
});
require __DIR__.'/auth.php';