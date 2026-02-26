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
    - `User::teams()` → `belongsToMany(Team::class, 'coach_team')->withPivot(['email'])`.
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


#### Dropzona (nahrávání příloh)
- Pro nahrávání příloh je použita jednoduchá projektová dropzona jako Blade komponenta `x-member.dropzone` (postavena na Alpine.js, bez externí JS knihovny).
- Vlastnosti:
  - Single upload (1 soubor), velikost do 10 MB.
  - Povolené typy: `pdf, jpg, jpeg, png, doc, docx, xls, xlsx`.
  - Náhled obrázků, možnost odebrání souboru před odesláním, validace velikosti a typu na klientu.
- Použití v šabloně:
```
<x-member.dropzone name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" :max-size-mb="10" />
```
- Nasazení:
  - Bez nových Vite entrypointů (používá se existující Alpine v app.js); není potřeba `npm run build` pouze kvůli této změně.
  - Po nasazení vyčistěte cache a zkompilované pohledy, pokud by se komponenta neprojevila okamžitě.
- Pozn.: Pokud v systému existuje již jiná interní „dropzona“ pod odlišným názvem, lze komponentu snadno přepnout.


#### Karta kontaktu (Admin/Trenér)
- Na stránkách formulářů je v pravém sloupci zobrazena karta kontaktu cílové osoby:
  - Kontakt administrátora bere údaje z nastavení v administraci (Branding → Kontakty):
    - `admin_contact_email` (s fallbackem na `.env:ERROR_REPORT_EMAIL`)
    - `admin_contact_name` (volitelné; výchozí „Administrátor“)
    - `admin_contact_phone` (volitelné; fallback na `contact_phone`)
    - `admin_contact_photo_path` (volitelné; doporučeno bílé pozadí)
  - Kontakt trenéra se zobrazuje, pokud je uživatel v jednom týmu – vypíše všechny trenéry daného týmu (jméno, e‑mail z pivotu nebo uživatele, telefon, avatar pokud existuje v kolekci `avatar`).
  - Pokud uživatel není v žádném týmu nebo má více týmů, zobrazí se informatívní hint a použije se admin fallback.
- UI je plně dvojjazyčné (cs/en) – viz `lang/*/member.php` → `feedback.contact_card.*`.
