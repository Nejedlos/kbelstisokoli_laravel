# Systém přihlašování a validací

Tento dokument popisuje vylepšení systému přihlašování, inline validací a notifikací v projektu Kbelští sokoli.

## Účel
Cílem bylo zpříjemnit uživatelské rozhraní při přihlašování a obnově hesla, zajistit srozumitelné chybové hlášky v češtině a poskytnout okamžitou zpětnou vazbu přímo u jednotlivých polí formuláře.

## Technický popis

### 1. Lokalizace a "lidské" hlášky
Veškeré systémové hlášky byly lokalizovány do češtiny s důrazem na přátelský a srozumitelný tón (např. místo "Pole email je povinné" používáme "Zapomněli jste vyplnit e-mail, bez něj to nepůjde").

- **Umístění:** `lang/cs/` (soubory `auth.php`, `passwords.php`, `validation.php`) a `lang/cs.json`.
- **Fortify integrace:** Specifické hlášky z Fortify akcí jsou překládány pomocí `cs.json`.

### 2. Inline validace v Blade šablonách
Všechny formuláře v adresáři `resources/views/auth/` byly upraveny tak, aby zobrazovaly chyby přímo pod příslušným vstupním polem.

- **Vizuální indikace:** Při chybě se změní barva ohraničení (border) vstupního pole na jemně červenou (`border-rose-500/50`).
- **Chybové zprávy:** Zobrazují se pod polem s animací `animate-fade-in-down`.
- **Hromadný výpis:** Byl odstraněn hromadný výpis chyb v horní části stránky, aby se předešlo duplicitě a nepřehlednosti.

### 3. Vylepšené notifikace (Flash messages)
Notifikace o úspěšných akcích (např. odeslání e-mailu pro obnovu hesla) mají nový, vizuálně atraktivnější design.

- **Vzhled:** "Glass-morphism" styl s ikonou a nadpisem (např. "Skvělá zpráva", "E-mail odeslán").
- **Barvy:** Použita smaragdová barva (`emerald`) pro pozitivní zprávy.

### 4. Kontrola aktivního účtu
V `FortifyServiceProvider` byla upravena logika autentizace. Pokud uživatel zadá správné údaje, ale jeho účet není aktivní (`is_active = false`), zobrazí se srozumitelná chybová hláška přímo u e-mailu.

## Způsob použití

### Pro vývojáře
Při přidávání nových polí do auth formulářů doporučujeme dodržovat následující vzor pro inline chybu:

```blade
<input name="field" class="border {{ $errors->has('field') ? 'border-rose-500/50' : 'border-white/10' }} ...">
@error('field') 
    <p class="text-[10px] text-rose-400 font-bold mt-2 ml-1 animate-fade-in-down">{{ $message }}</p> 
@enderror
```

### Pro administrátory
Změny nevyžadují žádné speciální nastavení. Všechny hlášky se automaticky přizpůsobují stavu aplikace. Pokud je potřeba změnit textaci, lze tak učinit v souborech v adresáři `lang/cs/`.
