# Laravel 13 Upgrade Validation Checklist - Kbelští sokoli

Tento checklist slouží k finální verifikaci systému po upgradu na Laravel 13 a migraci AI vrstvy. Každý bod musí být splněn před označením upgradu za hotový.

## ⚙️ Core & Infrastructure
- [ ] **Aplikace bootuje:** Web i administrace se načítají bez chyb.
- [ ] **Artisan příkazy:** `php artisan about` a `php artisan list` fungují bez warningů.
- [ ] **Migrace:** `php artisan migrate --status` ukazuje vše jako "Ran".
- [ ] **Environment:** `.env` obsahuje všechny nové klíče (např. pro AI SDK).
- [ ] **Optimalizace:** `php artisan optimize` proběhne v pořádku.

## 🕒 Background Tasks & Services
- [ ] **Queue / Workers:** `php artisan queue:work` zpracovává joby bez selhání.
- [ ] **Scheduler:** `php artisan schedule:list` ukazuje správné časy (zejména dynamické úkoly z DB).
- [ ] **Logs:** `storage/logs/laravel.log` neobsahuje nové `Fatal error` ani neočekávané `Deprecated`.

## 🔐 Auth & Security
- [ ] **Login / Logout:** Funguje na frontendu i v administraci.
- [ ] **2FA (Fortify):** Dvoufaktorové ověření v administraci je funkční.
- [ ] **Impersonation:** Možnost přihlásit se jako jiný uživatel (pokud je povoleno).
- [ ] **Permissions:** Spatie Permission správně blokuje/povoluje přístup k modulům.

## 🛠️ Filament Administrace
- [ ] **Dashboard:** Widgety se správně vykreslují a data souhlasí.
- [ ] **Resources:** Listování, vytváření a editace klíčových entit (Členové, Týmy) funguje.
- [ ] **Relation Managers:** Vazby mezi entitami se správně zobrazují.
- [ ] **Language Switch:** Přepínání jazyků (CS/EN) v adminu funguje.
- [ ] **MediaLibrary:** Nahrávání a zobrazování obrázků (Photo Pool) je funkční.

## ⚛️ Frontend & Livewire
- [ ] **Vite Build:** `npm run build` projde a assety se správně linkují (manifest hash).
- [ ] **Volt Komponenty:** Interaktivní prvky na webu (např. přihláška) reagují.
- [ ] **Member Dashboard:** Členská sekce zobrazuje data hráče/trenéra.
- [ ] **Error Pages:** 404 a 500 stránky mají správný klubový branding.

## 🤖 AI Vrstva (SDK Native)
- [ ] **AI Search:** Asistent v adminu odpovídá na dotazy s využitím kontextu (RAG).
- [ ] **Semantic Links:** Odkazy v AI odpovědích vedou na správné URL v systému.
- [ ] **Structured Output:** Import statistik správně parsuje HTML tabulky.
- [ ] **Metadata Suggestions:** AI navrhuje bilingvní popisy k fotkám.
- [ ] **Logging:** Volání AI jsou zaznamenána v `ai_request_logs` (včetně tokenů a modelu).

## 🧪 Automatizované Testy
- [ ] **Feature Tests:** Všechny testy v `tests/Feature` procházejí (Green).
- [ ] **Unit Tests:** Všechny testy v `tests/Unit` procházejí.
- [ ] **Browser Tests (Dusk):** Kritické UI flow (login, platba) jsou verifikovány.
