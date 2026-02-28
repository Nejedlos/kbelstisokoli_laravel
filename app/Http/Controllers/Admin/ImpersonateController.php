<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class ImpersonateController extends Controller
{
    /**
     * Search for users (AJAX).
     */
    public function search(Request $request)
    {
        /** @var User $admin */
        $admin = Auth::user();

        if (!$admin || !$admin->can('impersonate_users')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $request->get('q');

        $usersQuery = User::query()
            ->where('is_active', true)
            ->where('id', '!=', $admin->id);

        if (!empty($query)) {
            $usersQuery->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhere('club_member_id', 'like', "%{$query}%");
            });
        } else {
            // Výchozí výsledky (např. poslední přihlášení nebo prostě poslední uživatelé)
            $usersQuery->latest('last_login_at');
        }

        $users = $usersQuery->limit(10)
            ->get(['id', 'name', 'email']);

        return response()->json([
            'results' => $users->map(fn($user) => [
                'id' => $user->id,
                'text' => $user->name . ' (' . $user->email . ')',
            ]),
        ]);
    }

    /**
     * Start impersonating a user.
     */
    public function start(Request $request, $userId)
    {
        /** @var User $admin */
        $admin = Auth::user();

        // Kontrola oprávnění
        if (!$admin || !$admin->can('impersonate_users')) {
            return redirect()->back()->with('error', 'Nemáte oprávnění k impersonaci.');
        }

        // Najít cílového uživatele
        $userToImpersonate = User::findOrFail($userId);

        // Zamezit impersonaci sebe sama
        if ($admin->id === $userToImpersonate->id) {
            return redirect()->back()->with('error', 'Nemůžete impersonovat sami sebe.');
        }

        // Uložit ID původního admina do session
        if (!$request->session()->has('impersonated_by')) {
            $request->session()->put('impersonated_by', $admin->id);
        }

        // Přihlásit se jako nový uživatel
        Auth::login($userToImpersonate);

        // Určit cílovou cestu na základě oprávnění uživatele
        $targetRoute = $userToImpersonate->canAccessAdmin()
            ? route('filament.admin.pages.dashboard')
            : route('member.dashboard');

        return redirect()->to($targetRoute)
            ->with('success', 'Nyní vystupujete jako ' . $userToImpersonate->name . '.');
    }

    /**
     * Stop impersonating and return to original admin.
     */
    public function stop(Request $request)
    {
        if (!$request->session()->has('impersonated_by')) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        $originalAdminId = $request->session()->pull('impersonated_by');
        $originalAdmin = User::find($originalAdminId);

        if ($originalAdmin) {
            Auth::login($originalAdmin);

            $targetRoute = $originalAdmin->canAccessAdmin()
                ? route('filament.admin.pages.dashboard')
                : route('member.dashboard');

            return redirect()->to($targetRoute)
                ->with('success', 'Vrátili jste se ke svému účtu.');
        }

        return redirect()->route('login')->with('error', 'Nepodařilo se obnovit původní sezení.');
    }
}
