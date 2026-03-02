# Dashboard Nudges (Doporučení na nástěnce)

Tento modul slouží k zobrazování personalizovaných doporučení a upozornění pro členy na jejich dashboardu v členské sekci. Cílem je motivovat uživatele k dokončení profilu nebo k provedení důležitých akcí.

## Technický popis

Systém funguje na principu "nudges" (postrčení), která jsou generována na straně serveru a zobrazována ve formě atraktivních, zavíratelných bannerů.

### Generování nudges
Nudges jsou definovány v `DashboardController` (metoda `index`). Aktuálně systém kontroluje:
- **Chybějící avatar:** Pokud uživatel nemá nahranou žádnou vlastní fotografii v Media Library (kolekce `avatar`), zobrazí se mu výzva k nahrání.

### UI a rotace
- Pokud je k dispozici více nudges, systém vybere při každém načtení stránky jeden náhodně. Tím se předejde zahlcení uživatele, ale zároveň se rotují důležité úkoly.
- UI je postaveno na Tailwind CSS s využitím brandingových barev.
- Obsahuje titulek, zprávu, volitelný návod (instruction), ikonu a tlačítko s akcí (CTA).

### Skrývání (Persistence)
- Uživatel může nudge zavřít pomocí křížku.
- Stav skrytí je uložen v `localStorage` prohlížeče pod klíčem `nudge_hidden_{id}_{user_id}`.
- To znamená, že po zavření se dané konkrétní doporučení uživateli znovu nezobrazí, dokud nevymaže cache prohlížeče nebo se nepřihlásí z jiného zařízení/prohlížeče.

## Přidání nového nudge

Pro přidání nového typu doporučení je třeba:
1. Definovat lokalizační řetězce v `lang/cs.json` a `lang/en.json`.
2. Přidat logiku do `App\Http\Controllers\Member\DashboardController`.

Příklad struktury nudge:
```php
$nudges[] = [
    'id' => 'profile_complete',
    'title' => __('dashboard.nudges.profile.title'),
    'message' => __('dashboard.nudges.profile.message'),
    'cta' => __('dashboard.nudges.profile.cta'),
    'url' => route('member.profile.edit'),
    'icon' => 'user-pen',
    'color' => 'primary', // nebo secondary, success, warning atd.
    'instruction' => __('dashboard.nudges.profile.instruction'),
];
```

## Jazyková podpora
Všechny texty jsou plně lokalizovány (CZ/EN) prostřednictvím standardních JSON souborů v `lang/`.
