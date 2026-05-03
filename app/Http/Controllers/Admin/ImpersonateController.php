<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonateController extends Controller
{
    /**
     * Search for users (AJAX).
     */
    public function search(Request $request)
    {
        /** @var User $admin */
        $admin = Auth::user();

        if (! $admin || ! $admin->can('impersonate_users')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = $request->get('q');

        $usersQuery = User::query()
            ->where('is_active', true)
            ->where('id', '!=', $admin->id);

        if (! empty($query)) {
            $usersQuery->where(function ($q) use ($query) {
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
            'results' => $users->map(fn ($user) => [
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

        if (! $admin) {
            return redirect()->route('login');
        }

        // 1. Zapamatovat si ID admina pro návrat
        $adminId = $admin->id;

        \Illuminate\Support\Facades\Log::info('Impersonate.start.attempt', [
            'admin_id' => $adminId,
            'user_to_impersonate_id' => $userId,
            'session_id' => $request->session()->getId(),
        ]);

        // Kontrola oprávnění
        if (! $admin->can('impersonate_users')) {
            \Illuminate\Support\Facades\Log::warning('Impersonate.start.unauthorized', ['admin_id' => $admin->id]);
            return redirect()->back()->with('error', 'Nemáte oprávnění k impersonaci.');
        }

        // Najít cílového uživatele
        $userToImpersonate = User::findOrFail($userId);

        // Zamezit impersonaci sebe sama
        if ($adminId === (int) $userId) {
            return redirect()->back()->with('error', 'Nemůžete impersonovat sami sebe.');
        }

        // 2. Bezpečný switch uživatele
        // Nejdříve odhlásíme admina (včetně smazání remember cookie)
        Auth::guard('web')->logout();

        // Vyčistíme a zregenerujeme sezení
        $request->session()->flush();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        // 3. Přihlásíme nového uživatele do web guardu
        Auth::guard('web')->login($userToImpersonate);

        // Synchronizujeme Auth state i v rámci aktuálního requestu
        auth()->setUser($userToImpersonate);

        // 4. Nastavit klíče do NOVÉ session
        $request->session()->put('impersonated_by', $adminId);
        $request->session()->put('impersonation_started', $userToImpersonate->name);

        // Nastavení password hashe pro AuthenticateSession middleware
        // Používáme oba formáty klíčů pro maximální kompatibilitu s Laravel 11/12 a Filamentem
        $guard = Auth::getDefaultDriver() ?: 'web';
        $passwordHash = $userToImpersonate->getAuthPassword();

        $request->session()->put([
            "password_hash_{$guard}" => $passwordHash,
            'auth.password_hash_web' => $passwordHash,
            'auth.2fa_confirmed_at' => now()->timestamp, // Obejít 2FA pro impersonaci
        ]);

        // Vynutíme uložení session do storage před redirectem
        $request->session()->save();

        \Illuminate\Support\Facades\Log::info('Impersonate.start.success', [
            'new_user_id' => Auth::id(),
            'new_session_id' => $request->session()->getId(),
            'impersonated_by' => $request->session()->get('impersonated_by'),
        ]);

        // Určit cílovou cestu na základě oprávnění
        $targetRoute = $userToImpersonate->canAccessAdmin()
            ? route('filament.admin.pages.dashboard')
            : route('member.dashboard');

        // Vynutíme čistý redirect bez cache
        return redirect()->to($targetRoute)
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Stop impersonating and return to original admin.
     */
    public function stop(Request $request)
    {
        if (! $request->session()->has('impersonated_by')) {
            return redirect()->route('filament.admin.pages.dashboard');
        }

        $originalAdminId = $request->session()->get('impersonated_by');
        $originalAdmin = User::find($originalAdminId);

        if ($originalAdmin) {
            // Bezpečný návrat: odhlásit se z web guardu
            Auth::guard('web')->logout();

            // Vyčistit a zregenerovat sezení
            $request->session()->flush();
            $request->session()->regenerate();
            $request->session()->regenerateToken();

            // Přihlásit původního admina
            Auth::guard('web')->login($originalAdmin);
            auth()->setUser($originalAdmin);

            // Nastavit password hash zpět
            $guard = Auth::getDefaultDriver() ?: 'web';
            $passwordHash = $originalAdmin->getAuthPassword();

            $request->session()->put([
                "password_hash_{$guard}" => $passwordHash,
                'auth.password_hash_web' => $passwordHash,
                'auth.2fa_confirmed_at' => now()->timestamp,
                'impersonation_stopped' => true,
            ]);

            $request->session()->save();

            $targetRoute = $originalAdmin->canAccessAdmin()
                ? route('filament.admin.pages.dashboard')
                : route('member.dashboard');

            return redirect()->to($targetRoute)
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }

        return redirect()->route('login')->with('error', 'Nepodařilo se obnovit původní sezení.');
    }
}
