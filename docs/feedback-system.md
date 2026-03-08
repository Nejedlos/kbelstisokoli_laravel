# Moderní Bug Report / Feedback Systém

Tento modul implementuje robustní systém pro sběr zpětné vazby od přihlášených uživatelů (členů i administrátorů). Umožňuje snadné hlášení chyb, nápadů a obecné zpětné vazby přímo z rozhraní aplikace bez nutnosti přesměrování.

## Funkce
- **Plovoucí widget:** Přístupný vlevo dole pro všechny přihlášené uživatele.
- **Inline modal:** Formulář se otevírá přímo na stránce jako overlay.
- **Automatická diagnostika:** Sběr URL, titulu stránky, informací o prohlížeči, viewportu a verzi aplikace.
- **Screenshoty:** Automatické pořízení screenshotu aktuální obrazovky při odeslání (přes `dom-to-image-more` s fallbackem na `html2canvas`).
- **Výkonnostní data:** Sběr TTFB, DOMContentLoaded, Load eventu a TOP 20 nejpomalejších resources přes Performance API.
- **Breadcrumbs:** Historie navigace, scrollování a interakcí (kliknutí na tlačítka, odeslání formulářů).
- **Logy konzole:** Sběr posledních 300 logů z prohlížeče (console.log, warn, error) a 100 runtime chyb (onerror, unhandledrejection).
- **Network failure tracking:** Automatický sběr neúspěšných síťových požadavků (status >= 400).
- **DOM Snapshot Light:** Volitelný sběr HTML struktury hlavní části stránky (max 100KB, maskované inputy).
- **Click tracking:** Volitelný sběr posledních 200 kliknutí s přesnými souřadnicemi.
- **Privacy First:** Automatické maskování prvků s třídou `.bugmask` nebo atributem `data-bugmask="true"` na screenshotu a rekurzivní redakce citlivých dat (hesla, tokeny, cookie) ve všech datech.
- **Lehký provoz:** Sběr dat využívá Ring Buffery a nezpomaluje běžný chod aplikace. Těžké operace (screenshot, DOM snapshot, performance dump) se provádějí až při odeslání hlášení.

## Backend a Administrace
- **Ukládání:** Reporty jsou uloženy v databázi `feedback_reports`, soubory (screenshoty, logy) v `storage/app/feedback/{id}/`.
- **Notifikace:** Po odeslání je zaslán e-mail s přehledem a přílohami na konfigurované adresáře (Klub IT).
- **Admin UI:** Správa hlášení ve Filamentu (`Zpětná vazba`), s náhledy screenshotů a detailním prohlížečem logů.

## Konfigurace
Konfigurace se nachází v `config/feedback.php`.
- `enabled`: Globální zapnutí/vypnutí.
- `environments`: V jakých prostředích se má widget zobrazovat.
- `recipients`: Seznam e-mailů pro notifikace.
- `limits`: Limity pro logy, velikost payloadu a rate limiting.
- `redaction`: Seznam klíčů a vzorů pro maskování citlivých dat.

## Příkazy
- `php artisan feedback:smoke`: Spustí automatický test funkčnosti celého systému (simulace odeslání).
- `php artisan test tests/Feature/FeedbackSystemTest.php`: Spustí feature testy.

## Troubleshooting
- **413 Payload Too Large:** Způsobeno příliš velkým screenshotem nebo logy. Uživatel je vyzván k vypnutí příloh.
- **CORS u screenshotu:** Pokud se na stránce nachází obrázky z cizích domén bez správné CORS hlavičky, nemusí se na screenshotu zobrazit.
- **429 Too Many Requests:** Uživatel překročil rate limit (10/min) nebo duplicate guard (stejný title a popis v posledních 5 minutách).

## Implementované soubory
- `app/Http/Controllers/FeedbackController.php`: Zpracování požadavků.
- `app/Http/Middleware/InjectFeedbackWidget.php`: Injektáž widgetu do HTML.
- `app/Models/FeedbackReport.php`: Databázový model.
- `app/Filament/Resources/FeedbackReports/FeedbackReportResource.php`: Admin rozhraní.
- `resources/views/partials/feedback-widget.blade.php`: Frontend komponenta.
- `resources/views/emails/feedback-report.blade.php`: Šablona notifikačního e-mailu.
- `config/feedback.php`: Hlavní konfigurace.
