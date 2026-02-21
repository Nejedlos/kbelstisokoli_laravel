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
