### Zpětná vazba v členské sekci (Kontakt trenéra / Kontakt admina)

Tento modul umožňuje členům rychle kontaktovat své trenéry (pro týmová témata – zápasy, tréninky, platby v týmu) a administrátory (pro témata systému – nastavení stránek, nefunkčnost apod.).

#### Účel
- Umožnit členům poslat zprávu přes jednoduchý formulář v členské sekci.
- Automaticky zvolit správného adresáta:
  - Trenéry podle vybraného/uživatelova týmu.
  - Administrátora podle nastavení v administraci, s fallbackem na `ERROR_REPORT_EMAIL` z `.env`.
- Odeslat potvrzovací e-mail uživateli a zobrazit notifikaci o úspěchu.

#### Použití (pro uživatele)
- V členské sekci (Dashboard) jsou nová tlačítka:
  - „Kontaktovat trenéra“ → formulář s výběrem týmu (pokud je uživatel v více týmech), předmět, zpráva, příloha (volitelná).
  - „Kontaktovat administrátora“ → formulář: předmět, zpráva, příloha (volitelná).
- Po odeslání:
  - Zpráva je doručena trenérům daného týmu (nebo adminovi při absenci trenérů/e-mailů).
  - Uživatel obdrží potvrzovací e-mail (podle aktuálně zvoleného jazyka).

#### Technický popis
- Datový model:
  - Nová pivot tabulka `coach_team` (`team_id`, `user_id`, `email`, timestamps) – umožňuje přiřadit trenéry (uživatele) k týmům a případně pro tým přepsat kontaktní e-mail.
  - Relace:
    - `Team::coaches()` → `belongsToMany(User::class, 'coach_team')->withPivot(['email'])`.
    - `User::teamsCoached()` → `belongsToMany(Team::class, 'coach_team')->withPivot(['email'])`.
- Nastavení admin e-mailu:
  - Filament stránka `BrandingSettings` rozšířena o pole `admin_contact_email`.
  - Fallback: pokud není nastaveno, použije se `.env` klíč `ERROR_REPORT_EMAIL`.
- Routy (prefix `/clenska-sekce`):
  - `GET /kontakt-trenera` → formulář (route name: `member.contact.coach.form`).
  - `POST /kontakt-trenera` → odeslání (`member.contact.coach.send`).
  - `GET /kontakt-admina` → formulář (`member.contact.admin.form`).
  - `POST /kontakt-admina` → odeslání (`member.contact.admin.send`).
- Kontroler: `App\Http\Controllers\Member\ContactController`.
  - Validace: `subject` (min 5), `message` (min 10), `attachment` (max 10 MB; `pdf,jpg,jpeg,png,doc,docx,xls,xlsx`).
  - Ukládání příloh: na disk dle `filesystems.default` / `UPLOADS_DISK` do podsložky `<uploads>/feedback` (resp. `UPLOADS_DIR/feedback`).
  - Odeslání e-mailů: `App\Mail\FeedbackMessage`, potvrzení `App\Mail\FeedbackConfirmation`.
  - Příjemci:
    - Trenéři týmu (přes relaci/pivot e-mail → jinak `users.email`).
    - Pokud chybí, admin e-mail z `settings.admin_contact_email` → jinak `ERROR_REPORT_EMAIL`.
- Lokalizace:
  - UI v `lang/cs/member.php` a `lang/en/member.php` → sekce `feedback`.
  - E-maily v `lang/cs/mail.php` a `lang/en/mail.php`.
- Šablony:
  - Formuláře: `resources/views/member/contact/{coach,admin}.blade.php`.
  - E-maily: `resources/views/emails/feedback/{message,confirmation}.blade.php`.

#### Nasazení
1) Spusťte migrace: `php artisan migrate --no-interaction`.
2) V administraci (Branding → Kontaktní údaje) vyplňte `E-mail pro administrátorský kontakt`.
3) Otestujte odeslání zpráv (cz/en) a zkontrolujte doručení i potvrzovací e-maily.

#### Poznámky
- UI i e-maily jsou bilingvní – používá se aktuální `locale` v čase odeslání.
- Pokud má člen více týmů, je k dispozici výběr týmu. Pokud nemá žádný, upozorníme a pošleme na admina.
