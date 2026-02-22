# Vyhledávání a navigace

Tento modul zajišťuje orientaci uživatelů na webu prostřednictvím fulltextového vyhledávání, chytrou navigaci a hierarchické breadcrumbs (drobečkovou navigaci).

## 1. Vyhledávání (Public Search)

### Technické řešení
- **Služba:** `App\Services\SearchService`
- **Controller:** `App\Http\Controllers\Public\SearchController`
- **Vlastnosti:**
    - Prohledává modely `Page` a `Post`.
    - Respektuje stav publikace (`is_visible`, `status = published`).
    - Výsledky jsou sjednoceny přes `App\DataTransferObjects\SearchResult`.
    - SEO: Stránka s výsledky vyhledávání má nastaven `noindex, follow`.

### Rozsah vyhledávání
Aktuálně se prohledávají pole:
- **Page:** `title`, `content` (v aktuálním jazyce).
- **Post:** `title`, `excerpt`, `content` (v aktuálním jazyce).

## 2. Navigace a zóny

Aplikace je rozdělena do tří hlavních zón, které jsou navigačně propojeny:

### Public Zóna
- Hlavní menu v `x-header`.
- Integrované vyhledávání (overlay v headeru).
- Uživatelské menu (vpravo nahoře) pro přihlášené uživatele umožňující rychlý přepon do Member/Admin sekce.

### Member Zóna
- Přístupná po přihlášení přes `/member`.
- Obsahuje dashboard, profil, docházku a ekonomiku.
- Zpětný odkaz na veřejný web v navigaci.

### Admin Zóna (Filament)
- Přístupná oprávněným uživatelům přes `/admin`.
- Uživatelské menu ve Filamentu obsahuje odkazy:
    - **Členská sekce** (`member.dashboard`)
    - **Veřejný web** (`public.home`)

## 3. Breadcrumbs (Drobečková navigace)

### Implementace
- **Služba:** `App\Services\BreadcrumbService`
- **Komponenta:** `<x-breadcrumbs :breadcrumbs="$breadcrumbs" />`
- **Vlastnosti:**
    - Automaticky generuje hierarchii pro stránky a aktuality.
    - **SEO:** Komponenta automaticky generuje strukturovaná data typu `BreadcrumbList` (JSON-LD).

### Použití v controlleru
```php
public function show(Page $page, BreadcrumbService $breadcrumbService)
{
    return view('page', [
        'breadcrumbs' => $breadcrumbService->generateForPage($page)->get()
    ]);
}
```

## 4. UX a Mobile-first
- Vyhledávání je dostupné na mobilu přes ikonu lupy.
- Breadcrumbs jsou horizontálně scrollovatelné na malých zařízeních, aby nezabíraly příliš místa vertikálně.
- Uživatelské menu je optimalizováno pro dotykové ovládání.

## 5. Výkon
- Vyhledávání používá jednoduché `LIKE` dotazy s limitem výsledků, což je dostatečné pro MVP.
- Navigace a breadcrumbs jsou generovány efektivně bez zbytečných DB dotazů (pokud není vyžadována hluboká hierarchie).
