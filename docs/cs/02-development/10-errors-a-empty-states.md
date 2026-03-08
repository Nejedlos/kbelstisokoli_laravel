# Error Pages a Empty States

Tento dokument popisuje systém pro řešení prázdných stavů (Empty States) a chybových stránek (Error Pages) na veřejném webu Kbelští sokoli.

## 1. Empty States (Prázdné stavy)

Pro sjednocení vzhledu stránek, které zatím neobsahují data (novinky, zápasy, výsledky vyhledávání), používáme komponentu `<x-empty-state>`.

### Komponenta `<x-empty-state>`
Umístění: `resources/views/components/empty-state.blade.php`

**Parametry:**
- `title` (string): Hlavní nadpis (volitelný, bere se z překladu).
- `subtitle` (string): Podnadpis/popis (volitelný).
- `icon` (string): Název Font Awesome ikony (např. `fa-newspaper`). Výchozí je `fa-basketball`.
- `primaryCta` (array): Pole s `url` a `label` pro hlavní tlačítko.
- `secondaryCta` (array): Pole s `url` a `label` pro sekundární tlačítko.

**Příklad použití:**
```blade
<x-empty-state
    :title="__('news.empty_title')"
    :subtitle="__('news.empty_subtitle')"
    icon="fa-newspaper"
    :primaryCta="['url' => route('public.matches.index'), 'label' => __('news.empty_cta_matches')]"
    :secondaryCta="['url' => route('public.contact.index'), 'label' => __('news.empty_cta_contact')]"
/>
```

## 2. Error Pages (Chybové stránky)

Chybové stránky (404, 403, 419, 500, 503) jsou stylizovány do moderního sportovního designu s basketbalovou tématikou (mikrocopy).

### Architektura
Všechny chybové stránky dědí ze společného layoutu `resources/views/errors/layout.blade.php`. Tento layout je navržen jako "odolný" (resilient) – nevyžaduje kompletní public layout, aby fungoval i v případě kritických chyb (např. chyba v SEO datech nebo navigaci).

**Klíčové vlastnosti layoutu:**
- Využívá `BrandingService` pro barvy a logo.
- Obsahuje basketbalové dekorace (gradienty).
- Plně lokalizovaný (CZ/EN).
- Responzivní design.

### Lokalizace
Texty pro chybové stránky jsou definovány v `lang/{locale}/errors.php`.

**Dostupné stránky:**
- **404 (Not Found):** "Tahle přihrávka skončila v autu"
- **403 (Forbidden):** "Vstup zakázán" (do této části hřiště mají přístup jen vybraní hráči)
- **419 (Page Expired):** "Časový limit vypršel"
- **500 (Server Error):** "Technická chyba v týmu"
- **503 (Service Unavailable):** "Probíhá údržba hřiště"

### Úprava chybových stránek
Jednotlivé stránky v `resources/views/errors/*.blade.php` definují pouze obsahové sekce:
```blade
@extends('errors.layout')
@section('title', __('errors.404.title'))
@section('code', '404')
@section('headline', __('errors.404.title'))
@section('message', __('errors.404.message'))
@section('tagline', __('errors.404.tagline'))
@section('actions')
    <!-- Tlačítka -->
@endsection
```

## 3. Best Practices
- **Mikrocopy:** Při psaní nových textů pro chyby držte sportovní, přátelský, ale profesionální tón.
- **CTA:** Vždy nabídněte uživateli cestu ven (zpět na home, novinky, zápasy).
- **Lokalizace:** Nikdy nehardcodujte texty přímo do šablon, vždy používejte `errors.php`.
