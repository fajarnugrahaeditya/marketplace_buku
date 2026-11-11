<?php

namespace App\Http\Controllers;

use App\Models\AuthorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BecomeAuthorController extends Controller
{
    public function create(): View
    {
        return view('author.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pen_name' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'rights_agreed' => ['accepted'],
        ], [
            'pen_name.required' => 'Nama pena wajib diisi.',
            'pen_name.max' => 'Nama pena maksimal 100 karakter.',
            'bio.max' => 'Bio maksimal 1000 karakter.',
            'rights_agreed.accepted' => 'Anda harus menyetujui pernyataan hak distribusi.',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user, $data) {
            $profile = new AuthorProfile();
            $profile->user_id = $user->id;
            $profile->pen_name = $data['pen_name'];
            $profile->bio = $data['bio'] ?? null;
            $profile->rights_agreed_at = now(); // bukti persetujuan pernyataan hak distribusi
            $profile->save();

            // role diubah lewat kode yang eksplisit, bukan lewat mass assignment
            $user->forceFill(['role' => 'author'])->save();
        });

        return redirect()->route('author.dashboard')
            ->with('status', 'Selamat, Anda sekarang terdaftar sebagai penulis.');
    }
}
