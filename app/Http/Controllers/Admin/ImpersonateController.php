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
                'text' => $user->name,
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

        if (!$admin) {
            return redirect()->route('login');
        }

        $adminId = $admin->id;

        \Illuminate\Support\Facades\Log::debug('Impersonate.start.init', [
            'admin_id' => $adminId,
            'user_to_impersonate_id' => $userId,
            'session_id' => $request->session()->getId(),
        ]);

        // Kontrola oprávnění
        if (!$admin || !$admin->can('impersonate_users')) {
            \Illuminate\Support\Facades\Log::warning('Impersonate.start.unauthorized', ['admin_id' => $admin?->id]);
            return redirect()->back()->with('error', 'Nemáte oprávnění k impersonaci.');
        }

        // Najít cílového uživatele
        $userToImpersonate = User::findOrFail($userId);

        \Illuminate\Support\Facades\Log::debug('Impersonate.start.user_found', [
            'user_to_impersonate' => $userToImpersonate->email,
        ]);

        // Zamezit impersonaci sebe sama
        if ($adminId === $userToImpersonate->id) {
            return redirect()->back()->with('error', 'Nemůžete impersonovat sami sebe.');
        }

        // 1. Nejprve zregenerujeme session a vyčistíme ji pro nového uživatele (bezpečnost)
        // ale ponecháme si ID admina
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        \Illuminate\Support\Facades\Log::debug('Impersonate.start.session_invalidated', [
            'new_session_id' => $request->session()->getId(),
        ]);

        // 2. Přihlásit se jako nový uživatel přes Filament guard (pokud je k dispozici)
        // nebo přes výchozí web guard. Použijeme guard ze session pokud existuje.
        Auth::login($userToImpersonate);

        // 3. Nastavit impersonated_by a další klíče zpět
        $request->session()->put('impersonated_by', $adminId);

        // Explicitně nastavit password hash pro AuthenticateSession middleware
        $guard = Auth::getDefaultDriver();
        $request->session()->put([
            "password_hash_{$guard}" => $userToImpersonate->getAuthPassword(),
            'auth.2fa_confirmed_at' => now()->timestamp,
        ]);

        \Illuminate\Support\Facades\Log::debug('Impersonate.start.after_login_complete', [
            'new_user_id' => Auth::id(),
            'guard' => $guard,
            'impersonated_by_in_session' => $request->session()->get('impersonated_by'),
            'password_hash_present' => $request->session()->has("password_hash_{$guard}"),
        ]);

        // Určit cílovou cestu na základě oprávnění uživatele
        $targetRoute = $userToImpersonate->canAccessAdmin()
            ? route('filament.admin.pages.dashboard')
            : route('member.dashboard');

        \Illuminate\Support\Facades\Log::debug('Impersonate.start.redirecting', [
            'target_route' => $targetRoute,
        ]);

        $request->session()->put('impersonation_started', $userToImpersonate->name);
        return redirect()->to($targetRoute);
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
            // Vyčistit session impersonovaného uživatele
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Auth::login($originalAdmin);

            // Nastavit password hash zpět pro admina
            $guard = Auth::getDefaultDriver();
            $request->session()->put([
                "password_hash_{$guard}" => $originalAdmin->getAuthPassword(),
                'auth.2fa_confirmed_at' => now()->timestamp,
            ]);

            $targetRoute = $originalAdmin->canAccessAdmin()
                ? route('filament.admin.pages.dashboard')
                : route('member.dashboard');

            $request->session()->put('impersonation_stopped', true);
            return redirect()->to($targetRoute);
        }

        return redirect()->route('login')->with('error', 'Nepodařilo se obnovit původní sezení.');
    }
}
