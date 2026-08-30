<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

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

        $availableTeams = Team::query()->orderBy('name')->get();

        return view('member.profile.edit', compact('user', 'profile', 'galleryAssets', 'availableTeams'));
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
            'member_default_team_id' => ['nullable', Rule::exists('teams', 'id')],
            'member_view_all_by_default' => ['boolean'],
            'attendance_reminders_email' => ['boolean'],
            'attendance_summaries_email' => ['boolean'],
        ], [], [
            'name' => __('member.profile.name'),
            'phone' => __('member.profile.phone'),
            'public_bio' => __('member.profile.bio'),
            'jersey_number' => __('member.profile.jersey_number'),
            'current_password' => __('member.profile.current_password'),
            'new_password' => __('member.profile.new_password'),
            'member_default_team_id' => __('member.profile.section_settings.default_team'),
        ]);

        // Update User
        $preferences = $user->notification_preferences ?? [];
        data_set($preferences, 'attendance_reminders.mail', $request->boolean('attendance_reminders_email'));
        data_set($preferences, 'attendance_summaries.mail', $request->boolean('attendance_summaries_email'));

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'member_default_team_id' => $request->member_default_team_id,
            'member_view_all_by_default' => $request->boolean('member_view_all_by_default'),
            'notification_preferences' => $preferences,
        ]);

        if ($request->filled('new_password')) {
            $user->update([
                'password' => $request->new_password,
            ]);
        }

        // Update PlayerProfile (if exists)
        if ($profile) {
            $profile->update([
                'public_bio' => $request->public_bio,
                'jersey_number' => $request->jersey_number,
            ]);
        }

        return back()->with('status', __('member.profile.update_success'));
    }

    /**
     * Nahrání nového avataru uživatelem (member sekce).
     *
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

        return back()->with('status', __('member.profile.avatar_updated'));
    }

    /**
     * Výběr avataru z veřejné galerie (MediaAsset) – zkopíruje soubor k uživateli.
     *
     * @deprecated Nahrazeno Livewire komponentou AvatarModal
     */
    public function selectAvatarFromAsset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'media_asset_id' => [
                'required',
                Rule::exists('media_assets', 'id')->where(function ($query) {
                    $query->where('is_public', true)
                        ->orWhere('uploaded_by_id', auth()->id());
                }),
            ],
        ]);

        $asset = MediaAsset::findOrFail($data['media_asset_id']);
        $media = $asset->getFirstMedia('default');

        if (! $media) {
            return back()->with('error', __('member.profile.avatar_error'));
        }

        $user = auth()->user();
        $path = $media->getPath();

        $user
            ->addMedia($path)
            ->usingFileName('avatar-from-asset-'.time().'.'.$media->extension)
            ->toMediaCollection('avatar');

        return back()->with('status', __('member.profile.avatar_from_gallery'));
    }
}
