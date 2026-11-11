<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\AuthorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('author.profile.edit', [
            'profile' => $this->profile($request),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);

        $data = $request->validate([
            'pen_name' => ['required', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'pen_name.required' => 'Nama pena wajib diisi.',
            'pen_name.max' => 'Nama pena maksimal 100 karakter.',
            'bio.max' => 'Bio maksimal 1000 karakter.',
            'photo.image' => 'Foto harus berupa gambar.',
            'photo.mimes' => 'Foto harus berformat JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $profile->pen_name = $data['pen_name'];
        $profile->bio = $data['bio'] ?? null;

        if ($request->hasFile('photo')) {
            // foto baru menggantikan foto lama
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            $profile->photo = $request->file('photo')->store('authors', 'public');
        } elseif ($request->boolean('remove_photo') && $profile->photo) {
            Storage::disk('public')->delete($profile->photo);
            $profile->photo = null;
        }

        $profile->save();

        return redirect()->route('author.profile.edit')
            ->with('status', 'Profil penulis berhasil diperbarui.');
    }

    private function profile(Request $request): AuthorProfile
    {
        // kunci utama author_profiles adalah user_id
        return AuthorProfile::findOrFail($request->user()->id);
    }
}
