# Přihlašování a nastavení 2FA (2026-09-02)

## Pravidla přístupu

- Uživatel bez přístupu do administrace se standardně přihlašuje heslem. Dobrovolné 2FA začne platit až po potvrzení kódem z aplikace.
- Povinnost 2FA vychází ze stejné metody `User::canAccessAdmin()` jako přístup do Filamentu. Zahrnuje trenéra, administrátora, editora i vlastní roli ekonoma nebo jiné role s oprávněním `access_admin`. Samotný hráčský profil nebo soupiska povinnost nezakládají.
- Po ověření hesla musí uživatel s přístupem do administrace dokončit nastavení 2FA, než smí používat administraci, členskou sekci nebo jiné aplikační endpointy. Dostupné zůstává nastavení, potvrzení hesla, změna jazyka a odhlášení.
- Již aktivní 2FA vyžaduje platné ověření relace nebo dříve ověřené zapamatované zařízení. Bez něj se uživatel odpojí od přihlášené relace a pokračuje standardní Fortify výzvou; nemůže zobrazit klíč či záložní kódy, změnit data ani vypnout 2FA pouhým potvrzením hesla.
- Stejné pravidlo platí pro Fortify i přihlašovací formulář Filamentu a pro uživatele s kombinací členských a administrátorských rolí.

## Nastavení z členského profilu

Profil i doporučení na dashboardu odkazují na společný průvodce `/auth/two-factor-setup`. Průvodce před zobrazením citlivých údajů vyžaduje nedávno ověřené heslo. Čerstvé přihlášení heslem tuto podmínku splňuje.

1. Potvrzení hesla, pokud od posledního ověření uplynul nastavený limit.
2. Aktivace a vygenerování QR kódu.
3. Naskenování QR nebo zadání klíče ručně, následně potvrzení kódem z aplikace. Chyba kódu se zobrazí přímo v průvodci.
4. Zobrazení záložních kódů s kopírováním a stažením. Ověření kódu při aktivaci současně ověří aktuální relaci, takže není nutná druhá okamžitá výzva.
5. Pokračování na bezpečně ověřenou původní adresu, jinak na členský dashboard (nebo administraci, pokud vlastní role nemá oprávnění k členské sekci). Změna hesla a návrat do průvodce nepoškodí uložený cíl.

Člen smí rozpracované nastavení opustit, pokračovat později nebo dobrovolné 2FA vypnout. U účtu s přístupem do administrace nelze vlastní povinné 2FA vypnout. Správcovský reset jiným oprávněným uživatelem zůstává dostupný; po resetu je opět nutné nastavení.

## Technické body

- `TwoFactorService` sjednocuje potvrzení relace, výzvu a ověření zapamatovaného zařízení.
- `EnsureTwoFactorEnabled` a `CheckTwoFactorTimeout` jsou součástí skupiny `web` i trvalých auth middleware Filamentu, aby kontrola platila také pro Livewire akce a pomocné admin endpointy (včetně zahájení impersonace).
- Nepotvrzený secret není aktivní 2FA. Člen se kvůli nedokončenému nastavení nedostane do zacyklené přihlašovací výzvy.
- Potvrzení relace a cookie zařízení jsou svázány s uživatelem, heslem a aktuálním 2FA secretem pomocí HMAC. Cookie má také serverem ověřované datum expirace. Staré cookies bez těchto údajů vyžadují nové ověření. Změna hesla nebo secretu zneplatní starý důkaz.
- Screenshot parametr ani běžný header neopravňuje k obejití 2FA. Interní autorizovaný render používá atribut platný pouze pro daný požadavek; nepřidává výjimku do session pro další požadavky.
- Před ověřením challenge se znovu kontroluje aktivita účtu a existence potvrzeného 2FA. Deaktivace či reset během přihlašování nevede k přihlášení ani k chybě dešifrování.
- Obnova hesla zachovává požadavek na aktivní 2FA i u obyčejného člena. Odhlášení ruší také rozpracovanou challenge.
- Ukládání přihlášení, hesla a 2FA nesynchronizuje členské role. Synchronizace je vyvolána vytvořením účtu nebo změnou členství, takže se neodebírají role starším účtům během přihlašování.
- Není potřeba migrace databáze ani změna rolí existujících uživatelů.

## Ověření

Regresní scénáře jsou v `tests/Feature/TwoFactorPipelineTest.php`, navazující přístupové kontroly v `AuthAccessTest` a `Auth2faChallengeTest`. Testy používají SQLite v paměti. Nové průchody ověřují skutečné TOTP i záložní kódy a obě přihlašovací cesty.

Testovací základ používá jednotně `RefreshDatabase`; původní kombinace s `DatabaseTransactions` otevírala v části sady dvě transakce. Před migracemi se kontroluje skutečně načtená konfigurace prostředí `testing` a databáze SQLite `:memory:`, včetně ochrany před omylem ponechanou produkční konfigurační cache.

V prohlížeči ověřeno povinné nastavení trenéra i dobrovolné nastavení hráče: přihlášení, QR, skutečný TOTP kód, zobrazení záložních kódů, pokračování a návrat do profilu. Použity samostatná testovací databáze a smyšlené účty.
