# Eloquent Modely a Vztahy - Nový Help Systém

Tento dokument popisuje implementovanou doménovou vrstvu pro nový help systém. Všechny modely jsou umístěny v namespace `App\Models` a využívají standardy projektu (Laravel 12, Spatie Translatable).

## 1. Přehled Modelů

Vznikly následující modely:

1.  **`HelpCategory`**: Reprezentuje hierarchickou strukturu (sekce a podsekce).
2.  **`HelpArticle`**: Hlavní entita nápovědy obsahující text, metadata a vazby.
3.  **`HelpQuickAction`**: Rychlé odkazy (tlačítka) přiřazené k článku.
4.  **`HelpFaq`**: Často kladené otázky specifické pro daný článek.

---

## 2. Detailní popis a vazby

### HelpCategory
- **Hierarchie**: Podporuje nekonečné zanoření pomocí `parent_id`.
- **Lokalizace**: Pole `name` a `description`.
- **Vztahy**:
    - `parent()`: Nadřazená kategorie (`BelongsTo`).
    - `children()`: Podřazené kategorie (`HasMany`).
    - `articles()`: Články v této kategorii (`HasMany`).
- **Scopy**:
    - `scopeActive()`: Pouze aktivní kategorie.
    - `scopeRoot()`: Pouze hlavní kategorie (bez rodiče).
    - `scopeFeatured()`: Doporučené kategorie.

### HelpArticle
- **Lokalizace**: `title`, `content`, `excerpt`, `search_keywords`.
- **Role (Audience)**: Uloženo v JSON poli `audience_roles`.
- **Vztahy**:
    - `category()`: Příslušnost ke kategorii (`BelongsTo`).
    - `quickActions()`: Seznam rychlých akcí (`HasMany`).
    - `faqs()`: Seznam FAQ k článku (`HasMany`).
    - `relatedArticles()`: Související články (`BelongsToMany` přes pivot `help_article_related`).
- **Scopy**:
    - `scopePublished()`: Pouze publikované články (bere v úvahu i `published_at`).
    - `scopeForAudience($roles)`: Filtruje články podle rolí uživatele.
- **Helpery**:
    - `hasAudienceRole($role)`: Ověří, zda má role přístup k článku.

### HelpQuickAction & HelpFaq
- Jednoduché modely pro doplňkový obsah.
- Oba mají lokalizovaná pole a vazbu `article()` (`BelongsTo`).

---

## 3. Lokalizace a Castování

Všechny modely používají trait `HasTranslations`. To znamená, že k polím se přistupuje standardně:
```php
$article->getTranslation('title', 'cs');
// nebo při nastaveném locale:
$article->title;
```

Důležité casty:
- `is_published`, `is_active`, `is_featured`, `is_customized` jsou castovány na `boolean`.
- `audience_roles` a `metadata` jsou castovány na `json` (array).
- `published_at` je castován na `datetime`.

---

## 4. Použití v kódu

### Získání nápovědy pro trenéra:
```php
$articles = HelpArticle::published()
    ->forAudience('coach')
    ->with('category')
    ->get();
```

### Získání struktury pro landing page:
```php
$categories = HelpCategory::root()
    ->active()
    ->with(['children', 'articles' => fn($q) => $q->published()])
    ->orderBy('sort_order')
    ->get();
```
