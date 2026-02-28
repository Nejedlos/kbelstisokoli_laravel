<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use App\Models\MediaAsset;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $profile = $user->playerProfile;
        $galleryAssets = MediaAsset::query()
            ->where('is_public', true)
            ->latest('id')
            ->limit(12)
            ->get();

        return view('member.profile.edit', compact('user', 'profile', 'galleryAssets'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->playerProfile;

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'public_bio' => ['nullable', 'string', 'max:1000'],
            'jersey_number' => ['nullable', 'string', 'max:5'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        // Update User
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        if ($request->filled('new_password')) {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
        }

        // Update PlayerProfile (if exists)
        if ($profile) {
            $profile->update([
                'public_bio' => $request->public_bio,
                'jersey_number' => $request->jersey_number,
            ]);
        }

        return back()->with('status', 'Váš profil byl úspěšně aktualizován.');
    }
    /**
     * Nahrání nového avataru uživatelem (member sekce).
     * @deprecated Nahrazeno Livewire komponentou AvatarModal
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'file', 'image', 'max:5120'], // 5MB
        ]);

        $user = auth()->user();

        // Uložení do media kolekce "avatar" (singleFile)
        if ($request->file('avatar')) {
            $user
                ->addMediaFromRequest('avatar')
                ->usingFileName('avatar-'.time().'.'.$request->file('avatar')->getClientOriginalExtension())
                ->toMediaCollection('avatar');
        }

        return back()->with('status', 'Avatar byl aktualizován.');
    }

    /**
     * Výběr avataru z veřejné galerie (MediaAsset) – zkopíruje soubor k uživateli.
     * @deprecated Nahrazeno Livewire komponentou AvatarModal
     */
    public function selectAvatarFromAsset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'media_asset_id' => ['required', Rule::exists('media_assets', 'id')],
        ]);

        $asset = MediaAsset::findOrFail($data['media_asset_id']);
        $media = $asset->getFirstMedia('default');

        if (!$media) {
            return back()->with('error', 'Vybraný obrázek nemá připojené médium.');
        }

        $user = auth()->user();
        $path = $media->getPath();

        $user
            ->addMedia($path)
            ->usingFileName('avatar-from-asset-'.time().'.'.$media->extension)
            ->toMediaCollection('avatar');

        return back()->with('status', 'Avatar byl nastaven z galerie.');
    }
}
