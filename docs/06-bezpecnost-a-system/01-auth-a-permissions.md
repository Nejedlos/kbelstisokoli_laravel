# Systém přihlašování a validací

Tento dokument popisuje vylepšení systému přihlašování, inline validací a notifikací v projektu Kbelští sokoli.

## Účel
Cílem bylo zpříjemnit uživatelské rozhraní při přihlašování a obnově hesla, zajistit srozumitelné chybové hlášky v češtině a poskytnout okamžitou zpětnou vazbu přímo u jednotlivých polí formuláře.

## Technický popis

### 1. Lokalizace a "lidské" hlášky
Veškeré systémové hlášky byly lokalizovány do češtiny s důrazem na přátelský a srozumitelný tón (např. místo "Pole email je povinné" používáme "Zapomněli jste vyplnit e-mail, bez něj to nepůjde").

- **Umístění:** `lang/cs/` (soubory `auth.php`, `passwords.php`, `validation.php`) a `lang/cs.json`.
- **Fortify integrace:** Specifické hlášky z Fortify akcí jsou překládány pomocí `cs.json`.

### 2. Inline validace v Blade šablonách (Frontend)
Všechny formuláře v adresáři `resources/views/auth/` byly upraveny tak, aby zobrazovaly chyby přímo pod příslušným vstupním polem. Pro zajištění moderního vzhledu a uživatelského zážitku byly vypnuty nativní prohlížečové validace.

- **Nativní validace:** Všechny formy mají nastaven atribut `novalidate` a u vstupních polí byly odstraněny atributy `required`. Tím se předešlo neestetickým systémovým bublinám prohlížeče.
- **Vizuální indikace:** Při chybě se změní barva ohraničení (border) vstupního pole na jemně červenou (`border-rose-500/40`) a přidá se jemný stín pro zvýraznění pole.
- **Chybové zprávy:** Zobrazují se pod polem s ikonou (`fa-circle-exclamation`) a dynamickou animací `animate-shake`, která vizuálně upozorní na chybu při odeslání.
- **Hromadný výpis:** Byl odstraněn hromadný výpis chyb v horní části stránky pro čistší vzhled.

### 3. Inline validace v administraci (Filament)
Pro sjednocení uživatelského zážitku byly stejné principy aplikovány i na administraci postavenou na Filament PHP 5.

- **Okamžitá zpětná vazba (Klientská validace):** V `AdminPanelProvider` je implementován "Validation Helper" (JavaScript), který zajišťuje okamžité zobrazení chybových hlášek při opuštění pole (blur) nebo odeslání prázdného formuláře. Toto řešení simuluje chování moderních validačních knihoven a poskytuje uživateli zpětnou vazbu bez nutnosti čekat na server.
- **Robustní detekce chybových stavů:** JavaScript nyní dynamicky přidává třídy `.fi-invalid-field` (na celé pole) a `.fi-is-invalid` (na input wrapper), kdykoliv je detekována klientská nebo serverová chyba. To zajišťuje, že vizuální styl zůstane stabilní i při Livewire updatech a automatickém focusu.
- **Reaktivní serverová validace:** Login stránka (`app/Filament/Pages/Auth/Login.php`) byla upravena tak, aby pole pro e-mail a heslo používala reaktivní validaci (`.live()`), což urychluje zobrazení serverových chyb (např. při špatném formátu e-mailu).
- **Rozlišení focusu a chyby:** Pro lepší uživatelský zážitek byl standardní focus změněn na modrou barvu (`#2563EB`). Pokud však pole obsahuje chybu, focus zůstává výrazně červený (`#E11D48`). Tím se předešlo situaci, kdy běžný focus vypadal jako chybový stav.
- **Globální sjednocení:** Pomocí `resources/css/filament-auth.css` byly přepsány výchozí styly chybových hlášek Filamentu. Nyní používají výraznou červenou ikonu (`fa-circle-exclamation`), mají lehké růžové pozadí, levý ohraničující pruh a stejnou animaci `animate-shake` jako frontendové šablony.
- **Výrazná vizuální změna:** Při chybě se u pole aktivuje nejen červené ohraničení a výrazný "glow" efekt, ale celá sekce pole (label i input) se podbarví, přidá se čárkovaný border a pole dostane jemné růžové pozadí i ve stavu bez focusu.
- **Oprava labelů a struktury:** Byla minimalizována mezera mezi labelem a vstupním polem (`.fi-fo-field-label-col`). Labely jsou nyní těsněji u vstupních polí a jsou vertikálně vycentrované.
- **Globální vypnutí nativních validací:** V `AdminPanelProvider` je implementován robustní JavaScriptový hook využívající `MutationObserver` a Livewire lifecycle hooky. Ten automaticky přidává atribut `novalidate` ke všem formulářům, čímž se eliminují systémové bubliny prohlížeče.

### 4. Vylepšené notifikace (Flash messages)
Notifikace o úspěšných akcích (např. odeslání e-mailu pro obnovu hesla) mají nový, vizuálně atraktivnější design.

- **Vzhled:** "Glass-morphism" styl s ikonou a nadpisem (např. "Skvělá zpráva", "E-mail odeslán").
- **Barvy:** Použita smaragdová barva (`emerald`) pro pozitivní zprávy a červená pro chyby.
- **Struktura:** Notifikace ve Filamentu byly přestylovány tak, aby ladily s designem administrace (zaoblené rohy, výrazný levý barevný pruh).

### 5. Kontrola aktivního účtu
V `FortifyServiceProvider` byla upravena logika autentizace. Pokud uživatel zadá správné údaje, ale jeho účet není aktivní (`is_active = false`), zobrazí se srozumitelná chybová hláška přímo u e-mailu.

### 6. Sjednocený vizuální styl (Glassmorphism)
Od února 2026 byl zaveden nový vizuální standard pro všechny autentizační stránky (Login, 2FA, Obnova hesla). 

- **Layout:** Společný `layouts.auth` s tmavým gradientním pozadím a plovoucími dekoracemi.
- **Karty:** Používá se třída `.glass-card` (bílá s 85% opacitou, silný blur, jemný stín).
- **Vstupní pole:** Pro maximální čitelnost používáme bílé pozadí (`bg-white`) a tmavý text (`text-slate-900`) s výrazným zaoblením (`rounded-2xl`).
- **Unifikace:** Filament i Fortify šablony využívají stejné Blade komponenty `<x-auth-header />` a `<x-auth-footer />` pro vizuální identitu.

## Způsob použití

### Pro vývojáře
Při přidávání nových polí do auth formulářů doporučujeme dodržovat následující vzor pro inline chybu (bez použití `required` atributu):

```blade
<div class="space-y-3">
    <label for="field" ...>Název pole</label>
    <div class="relative group/input">
        <input name="field" 
               class="... border {{ $errors->has('field') ? 'border-rose-500/40 shadow-[0_0_15px_rgba(244,63,94,0.1)]' : 'border-white/10' }} ...">
    </div>
    @error('field') 
        <div class="flex items-center gap-2 text-rose-400 mt-2 ml-1 animate-shake">
            <i class="fa-light fa-circle-exclamation text-[10px]"></i>
            <p class="text-[10px] font-bold tracking-wide">{{ $message }}</p>
        </div>
    @enderror
</div>
```

### Pro administrátory
Změny nevyžadují žádné speciální nastavení. Všechny hlášky se automaticky přizpůsobují stavu aplikace. Pokud je potřeba změnit textaci, lze tak učinit v souborech v adresáři `lang/cs/`.

# Systém přihlašování a validací

Tento dokument popisuje vylepšení systému přihlašování, inline validací a notifikací v projektu Kbelští sokoli.

## Účel
Cílem bylo zpříjemnit uživatelské rozhraní při přihlašování a obnově hesla, zajistit srozumitelné chybové hlášky v češtině a poskytnout okamžitou zpětnou vazbu přímo u jednotlivých polí formuláře.

## Technický popis

### 1. Lokalizace a "lidské" hlášky
Veškeré systémové hlášky byly lokalizovány do češtiny s důrazem na přátelský a srozumitelný tón (např. místo "Pole email je povinné" používáme "Zapomněli jste vyplnit e-mail, bez něj to nepůjde").

- Umístění: `lang/cs/` (soubory `auth.php`, `passwords.php`, `validation.php`) a `lang/cs.json`.
- Fortify integrace: Specifické hlášky z Fortify akcí jsou překládány pomocí `cs.json`.

### 2. Inline validace v Blade šablonách (Frontend)
Všechny formuláře v adresáři `resources/views/auth/` byly upraveny tak, aby zobrazovaly chyby přímo pod příslušným vstupním polem. Pro zajištění moderního vzhledu a uživatelského zážitku byly vypnuty nativní prohlížečové validace.

- Nativní validace: Všechny formy mají nastaven atribut `novalidate` a u vstupních polí byly odstraněny atributy `required`. Tím se předešlo neestetickým systémovým bublinám prohlížeče.
- Vizuální indikace: Při chybě se změní barva ohraničení (border) vstupního pole na jemně červenou (`border-rose-500/40`) a přidá se jemný stín pro zvýraznění pole.
- Chybové zprávy: Zobrazují se pod polem s ikonou (`fa-circle-exclamation`) a dynamickou animací `animate-shake`, která vizuálně upozorní na chybu při odeslání.
- Hromadný výpis: Byl odstraněn hromadný výpis chyb v horní části stránky pro čistší vzhled.

### 3. Inline validace v administraci (Filament)
Pro sjednocení uživatelského zážitku byly stejné principy aplikovány i na administraci postavenou na Filament PHP 5.

- Okamžitá zpětná vazba (Klientská validace): V `AdminPanelProvider` je implementován "Validation Helper" (JavaScript), který zajišťuje okamžité zobrazení chybových hlášek při opuštění pole (blur) nebo odeslání prázdného formuláře. Toto řešení simuluje chování moderních validačních knihoven a poskytuje uživateli zpětnou vazbu bez nutnosti čekat na server.
- Robustní detekce chybových stavů: JavaScript nyní dynamicky přidává třídy `.fi-invalid-field` (na celé pole) a `.fi-is-invalid` (na input wrapper), kdykoliv je detekována klientská nebo serverová chyba. To zajišťuje, že vizuální styl zůstane stabilní i při Livewire updatech a automatickém focusu.
- Reaktivní serverová validace: Login stránka (`app/Filament/Pages/Auth/Login.php`) byla upravena tak, aby pole pro e-mail a heslo používala reaktivní validaci (`.live()`), což urychluje zobrazení serverových chyb (např. při špatném formátu e-mailu).
- Rozlišení focusu a chyby: Pro lepší uživatelský zážitek byl standardní focus změněn na modrou barvu (`#2563EB`). Pokud však pole obsahuje chybu, focus zůstává výrazně červený (`#E11D48`). Tím se předešlo situaci, kdy běžný focus vypadal jako chybový stav.
- Globální sjednocení: Pomocí `resources/css/filament-auth.css` byly přepsány výchozí styly chybových hlášek Filamentu. Nyní používají výraznou červenou ikonu (`fa-circle-exclamation`), mají lehké růžové pozadí, levý ohraničující pruh a stejnou animaci `animate-shake` jako frontendové šablony.
- Výrazná vizuální změna: Při chybě se u pole aktivuje nejen červené ohraničení a výrazný "glow" efekt, ale celá sekce pole (label i input) se podbarví, přidá se čárkovaný border a pole dostane jemné růžové pozadí i ve stavu bez focusu.
- Oprava labelů a struktury: Byla minimalizována mezera mezi labelem a vstupním polem (`.fi-fo-field-label-col`). Labely jsou nyní těsněji u vstupních polí a jsou vertikálně vycentrované.
- Globální vypnutí nativních validací: V `AdminPanelProvider` je implementován robustní JavaScriptový hook využívající `MutationObserver` a Livewire lifecycle hooky. Ten automaticky přidává atribut `novalidate` ke všem formulářům, čímž se eliminují systémové bubliny prohlížeče.

### 4. Vylepšené notifikace (Flash messages)
Notifikace o úspěšných akcích (např. odeslání e-mailu pro obnovu hesla) mají nový, vizuálně atraktivnější design.

- Vzhled: "Glass-morphism" styl s ikonou a nadpisem (např. "Skvělá zpráva", "E-mail odeslán").
- Barvy: Použita smaragdová barva (`emerald`) pro pozitivní zprávy a červená pro chyby.
- Struktura: Notifikace ve Filamentu byly přestylovány tak, aby ladily s designem administrace (zaoblené rohy, výrazný levý barevný pruh).

### 5. Kontrola aktivního účtu
V `FortifyServiceProvider` byla upravena logika autentizace. Pokud uživatel zadá správné údaje, ale jeho účet není aktivní (`is_active = false`), zobrazí se srozumitelná chybová hláška přímo u e-mailu.

---

## Aktualizace: Admin = Admin + Member, oddělené vynucení 2FA (2026-02-21)

Tato aktualizace sjednocuje přístupová pravidla tak, aby administrátor byl superset běžného člena (má k dispozici administraci i členskou sekci) a zároveň zůstalo vynuceno 2FA pouze pro vstup do administrace.

### Pravidla přístupu
- Member zóna (členská sekce) je dostupná každému přihlášenému uživateli s oprávněním `view_member_section`.
- Admin zóna (Filament) je dostupná pouze uživatelům s oprávněním `access_admin` a aktivním/potvrzeným 2FA.
- Admin uživatel má i oprávnění `view_member_section`, a tedy přístup do členské sekce (admin ⊇ member).

### 2FA enforcement (pouze pro admin sekci)
- Middleware `EnsureTwoFactorEnabled` nyní vynucuje 2FA výhradně při vstupu na `admin*` trasy (vč. Filament routingu).
- Admin bez dokončeného 2FA:
  - do adminu nesmí a je přesměrován na `auth.two-factor-setup`;
  - členskou sekci využívat může (nižší riziko), aby nebyl blokován v běžné agendě.

### Redirect po přihlášení
Redirect je rozdělen na pravidla přístupu a cílové umístění po loginu:
1. Pokud existuje bezpečný `intended` cíl, respektujeme jej.
2. Pokud má uživatel oprávnění `access_admin`, směřujeme jej do admin panelu (po případném dokončení 2FA flow).
3. Ostatní uživatelé směřují do členské sekce (`member.dashboard`).

### Navigace a UX
- V členském layoutu (`resources/views/layouts/member.blade.php`) se uživatelům s oprávněním `access_admin` zobrazuje v uživatelském menu jasný odkaz „Administrace“.
- V admin panelu (Filament) byl do uživatelského menu přidán odkaz „Členská sekce“ (`member.dashboard`) pro rychlý návrat.
- Texty jsou srozumitelné a nezobrazují se odkazy, na které uživatel nemá oprávnění.

### Opravy kolizí a centralizace logiky
- Vyhnuli jsme se podmínkám typu „je přesně role X“. Všude se vyhodnocují schopnosti/permission: `view_member_section` a `access_admin`.
- `EnsureTwoFactorEnabled` kontroluje pouze přístupy do admin sekce, aby 2FA neblokovalo běžné používání členské části.

### Změněné soubory
- `app/Http/Middleware/EnsureTwoFactorEnabled.php` – 2FA se aplikuje jen na `admin*`.
- `app/Http/Responses/LoginResponse.php` – priorita redirectu pro adminy s `access_admin`.
- `resources/views/layouts/member.blade.php` – odkaz do administrace v uživatelském menu.
- `app/Providers/Filament/AdminPanelProvider.php` – odkaz „Členská sekce“ v user menu Filamentu.
- `resources/views/auth/two-factor-setup.blade.php` – přidán odkaz pro návrat do členské sekce.

### Ověřené scénáře
1. Běžný member: login → member dashboard; admin URL → 403/redirect.
2. Admin (s validní 2FA): login → admin panel; přístup do admin i member sekce OK.
3. Admin bez 2FA: admin panel blokován (redirect na setup); členská sekce dostupná.
4. Logout: korektní odhlášení; session invalidována.
5. Guest: chráněné member/admin trasy → login flow.

### Použité příkazy (non‑interactive)
- Spuštění feature testů: `php artisan test --testsuite=Feature --no-interaction`

### Doporučená commit zpráva
```
feat(auth): allow admin to access member area; enforce 2FA only for admin panel; fix redirects and UX links

- admin = admin + member capabilities
- 2FA middleware enforced only on admin routes
- admin default redirect to Filament panel, members to member dashboard
- added cross-links between admin panel and member area
```

### Další krok
- Přidat integrační testy pro scénáře admin bez 2FA → member sekce povolena, admin sekce blokována.
- Ověřit lokalizační texty a ikony v obou menu (Font Awesome Light varianta) napříč prostředími.

---

## Aktualizace: Rozšířená správa 2FA v administraci (2026-02-21)

Tato aktualizace přináší kompletní nástroje pro správu dvoufázového ověření (2FA) přímo v administraci uživatelů.

### 1. Detailní stav 2FA ve formuláři uživatele
V `UserResource` (formulář uživatele) byla přidána dedikovaná sekce „Dvoufázové ověření (2FA)“, která přehledně zobrazuje:
- **Aktuální stav:** Neaktivní / Čeká na potvrzení (vygenerován QR) / Aktivní a ověřeno.
- **Datum aktivace:** Kdy bylo 2FA plně potvrzeno.

### 2. Správcovské akce (pro administrátory)
Administrátoři s oprávněním `manage_users` nyní mohou:
- **Resetovat 2FA:** Akce „Vypnout / Resetovat 2FA“ smaže veškeré 2FA údaje uživatele (secret, kódy). To je nezbytné, pokud uživatel ztratí přístup k telefonu. Po resetu bude uživatel (pokud je admin) při příštím přihlášení znovu vyzván k nastavení.
- **Hromadný reset:** V tabulce uživatelů lze označit více záznamů a provést hromadný reset 2FA.

### 3. Sebe-správa (pro přihlášeného uživatele)
Uživatel při editaci svého vlastního profilu v admin panelu vidí rozšířené možnosti:
- **Zobrazit záchranné kódy:** Zobrazení kódů je chráněno opětovným zadáním hesla uživatele. Kódy se zobrazí v persistentní notifikaci.
- **Regenerovat kódy:** Umožňuje zneplatnit staré a vygenerovat nové záchranné kódy.
- **Vypnout 2FA:** Uživatel si může sám vypnout 2FA, přičemž při příštím přístupu do adminu jej systém opět vyzve k nastavení (pokud je pro něj 2FA povinné).

### 4. Filtrování v tabulce
V tabulce uživatelů byl vylepšen filtr pro 2FA:
- Lze filtrovat uživatele, kteří mají 2FA **plně potvrzené** (zabezpečené), oproti těm, kteří jej mají rozpracované nebo neaktivní.

### Změněné soubory
- `app/Filament/Resources/Users/Schemas/UserForm.php` – přidána sekce 2FA a interaktivní akce.
- `app/Filament/Resources/Users/Tables/UsersTable.php` – vylepšen filtr a přidána hromadná akce resetu.

### Ověřené scénáře
1. Admin resetuje 2FA jinému uživateli → 2FA data v DB smazána (null).
2. Admin si zobrazí své kódy → vyžadováno heslo → kódy zobrazeny v notifikaci.
3. Filtrování „Zabezpečeno“ v tabulce → zobrazí pouze uživatele s `two_factor_confirmed_at`.
4. Hromadný reset 2FA → úspěšně aplikováno na vybrané uživatele.

### Doporučená commit zpráva
```
feat(auth): enhance 2FA management in admin panel

- added 2FA management section to UserForm with actions (reset, view codes, regenerate)
- self-viewing of recovery codes protected by password confirmation
- improved 2FA status filtering in UsersTable
- added bulk reset action for 2FA
```

---

## Aktualizace: Oprava kompletního odhlášení (2026-02-21)

Tato aktualizace zajišťuje, že uživatel se může vždy „kompletně odhlásit“, i když se nachází v rozpracovaném stavu přihlašování (2FA challenge).

### 1. Přístupnost logoutu bez autentizace
Původně byla logout routa `/logout` chráněna middlewarem `auth`, což znemožňovalo odhlášení uživatelům, kteří zadali správné heslo, ale ještě nepotvrdili druhý faktor (v tomto stavu nejsou pro systém plně „autentizováni“).
- **Změna:** V `routes/web.php` byla logout routa explicitně definována s použitím pouze `web` middleware.
- **Důsledek:** Tlačítko „Zrušit a odhlásit se“ na stránce 2FA challenge nyní spolehlivě funguje.

### 2. Kompletní invalidace session
Logout proces ve Fortify standardně volá `session()->invalidate()` a `session()->regenerateToken()`.
- **Důsledek:** Při odhlášení jsou ze session odstraněna veškerá data, včetně dočasného ID uživatele (`login.id`) a příznaku „remember“ pro 2FA. Uživatel je tak odhlášen z 1FA i probíhajícího 2FA pokusu.

### Změněné soubory
- `routes/web.php` – uvolnění logout routy z `auth` middleware.

### Ověřené scénáře
1. Uživatel v 2FA challenge klikne na odhlášení → je přesměrován na úvodní stránku, session je vyčištěna, při pokusu o přístup do adminu musí znovu zadat heslo.
2. Plně přihlášený uživatel se odhlásí → standardní odhlášení funguje beze změn.

### Doporučená commit zpráva
```
fix(auth): allow logout during 2FA challenge for complete session clearance

- removed auth middleware from /logout route
- ensured session is fully invalidated even for partially authenticated users
```
