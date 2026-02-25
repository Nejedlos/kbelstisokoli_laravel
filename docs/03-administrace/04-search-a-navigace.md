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

## 6. AI Vyhledávání (Backend Search)

Pro administraci a členskou sekci je k dispozici sémantické vyhledávání ("AI-powered"), které umožňuje uživatelům popsat, co chtějí v systému udělat.

### Technické řešení
- **Služba:** `App\Services\BackendSearchService`
- **AI Model:** OpenAI `gpt-4o-mini` (vyžaduje nastavení `SERVICES_OPENAI_KEY` v `.env`).
- **Fallback:** Pokud není AI klíč k dispozici, vyhledává se v popisech a klíčových slovech cílů.
- **Kontexty:**
    - **Admin:** Vyhledává v dostupných Filament Resources a administrátorských akcích.
    - **Member:** Vyhledává v sekcích členské zóny (docházka, platby, profil, týmy).

### Integrace
- **Administrace:** Integrováno do globálního vyhledávání Filamentu (přes `Dashboard` stránku). V horní liště je navíc umístěno výrazné, moderní AI vyhledávací pole s animovanými prvky, nápovědou a interaktivními rychlými tipy.
- **Členská sekce:** Výrazné, vizuálně sladěné vyhledávací pole v horní liště (na desktopu) s nápovědou a "Superhuman-style" overlayem obsahujícím ikony a rychlé tipy. Na mobilu dostupný přes ikonu lupy.

### Oprávnění
BackendSearchService automaticky filtruje výsledky podle oprávnění aktuálně přihlášeného uživatele (`$user->can()`), takže uživatel nikdy neuvidí cíle, ke kterým nemá přístup.
