# Service Layer nového help systému

Tento dokument popisuje architekturu a implementaci servisní vrstvy pro nový help systém založený na databázi. Cílem je oddělit business logiku a načítání dat od uživatelského rozhraní (Filament pages, Livewire komponenty).

## 1. Architektura

Systém je rozdělen do několika specializovaných služeb v namespace `App\Services\Help`:

- **`HelpQueryService`**: Zodpovídá za primární načítání dat z databáze (články, kategorie, stromová struktura).
- **`HelpSearchService`**: Zajišťuje fulltextové vyhledávání v lokalizovaných polích a klíčových slovech.
- **`HelpNavigationService`**: Generuje breadcrumbs a navigační struktury.
- **`HelpService`**: Hlavní agregátor (Facade-like), který sjednocuje API pro použití v kontrolerech a UI.

## 2. Přehled služeb a metod

### HelpQueryService
- `forAudience(array|string $roles)`: Nastaví role pro následné query (filtrování podle `audience_roles`).
- `getHomeCategories()`: Vrací kořenové kategorie pro landing page.
- `getFeaturedArticles(int $limit)`: Vrací doporučené články napříč kategoriemi.
- `getCategoryBySlug(string $slug)`: Načte kategorii se subkategoriemi a články.
- `getArticleBySlug(string $slug)`: Načte detail článku včetně FAQ, Quick Actions a souvisejících článků.
- `getCategoryTree()`: Vrací kompletní hierarchii pro postranní navigaci.

### HelpSearchService
- `forAudience(array|string $roles)`: Filtruje výsledky vyhledávání podle oprávnění uživatele.
- `search(string $query)`: Vyhledává v `title`, `content`, `excerpt` a `search_keywords` pro aktuální locale.

### HelpNavigationService
- `getBreadcrumbs($item)`: Vrací kolekci pro drobečkovou navigaci pro kategorii nebo článek.

## 3. Klíčové vlastnosti

### Role-aware Visibility
Služby automaticky filtrují obsah na základě rolí uživatele. Pokud článek obsahuje definované `audience_roles`, uvidí jej pouze uživatelé s danou rolí (nebo administrátoři, pokud je tak nastavena logika v UI).
V query se to řeší přes Eloquent scope `forAudience` definovaný v modelu `HelpArticle`.

### Lokalizace (Translatable)
Všechny služby využívají balíček `spatie/laravel-translatable`. Vyhledávání v `HelpSearchService` je optimalizováno pro JSON strukturu v databázi (např. `where("title->{$locale}", 'like', ...)`).

### Čisté UI (Filament Integration)
Díky této vrstvě bude `Help` page ve Filamentu pouze volat:
```php
$data = $helpService->forAudience($userRoles)->getArticleData($slug);
```
Žádné komplexní Eloquent dotazy nebudou v souborech nápovědy.

## 4. Doporučení pro další vývoj

- **Cachování**: Pro produkční nasazení se doporučuje obalit výsledky `getCategoryTree()` a `getHomeData()` do `Cache::tags(['help'])`.
- **Vážené vyhledávání**: V budoucnu lze `HelpSearchService` rozšířit o váhy (např. shoda v titulku má větší váhu než v obsahu).
- **Logování vyhledávání**: Lze přidat sledování "úspěšnosti" vyhledávání pro identifikaci chybějícího obsahu.
