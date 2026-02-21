<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $profile = $user->playerProfile;

        return view('member.profile.edit', compact('user', 'profile'));
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
}
