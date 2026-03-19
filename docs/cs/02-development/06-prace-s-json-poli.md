# Práce s JSON poli

V rámci projektu Kbelští sokoli jsme přešli z ukládání serializovaných dat v polích `longtext` na nativní typ `json` (v MySQL 8). Tento dokument popisuje, jak s těmito poli pracovat, jak v nich vyhledávat a na co si dát pozor.

## 1. Hlavní JSON pole v projektu
Většina modelů v projektu využívá JSON pole pro metadata, konfiguraci nebo lokalizované překlady.

- **metadata:** Obsahuje doplňkové informace (např. externí ID, synchronizační údaje, nastavení uživatele).
- **title, content, keywords, summary:** U modelu `AiDocument` a translatable modelů jsou tato pole typu JSON.
- **audience_roles:** Pole obsahující seznam rolí (např. `["admin", "player"]`).

## 2. Vyhledávání v JSON polích (Laravel Eloquent)

Díky migraci na typ `json` v MySQL 8 můžeme využívat nativní JSON operátory přímo v Eloquentu.

### 2.1 Základní vyhledávání podle klíče
Místo zastaralého `LIKE '%"key":"value"%'` používejte šipkovou notaci:

```php
// Správně
$match = BasketballMatch::where('metadata->external_id', $externalId)->first();

// Špatně (zastaralé)
$match = BasketballMatch::where('metadata', 'LIKE', '%"external_id":"' . $externalId . '"%')->first();
```

### 2.2 Kontrola existence klíče
Pro zjištění, zda JSON obsahuje konkrétní klíč:

```php
// Vyhledá všechny profily, které už byly synchronizovány
$synced = PlayerProfile::whereNotNull('metadata->last_sync_at')->get();
```

### 2.3 Vyhledávání v polích (JSON_CONTAINS)
Pro pole, která obsahují seznam hodnot (např. role):

```php
// Vyhledá články určené pro roli 'admin'
$articles = HelpArticle::whereJsonContains('audience_roles', 'admin')->get();
```

### 2.4 Vyhledávání v translatable polích
U modelů používajících `spatie/laravel-translatable` vyhledáváme v konkrétním jazyce:

```php
$locale = app()->getLocale();
$posts = Post::where("title->{$locale}", 'LIKE', "%{$search}%")->get();
```

## 3. Práce v Query Builderu (Raw SQL)
Pokud pracujete přímo s `DB::table()`, můžete v MySQL 8 využít operátor `->>` pro extrakci a unquoting hodnoty.

```php
$results = DB::table('help_articles')
    ->selectRaw("title->>'$." . $locale . "' as title_str")
    ->whereRaw("metadata->>'$.section' = ?", ['admin'])
    ->get();
```

## 4. Castování v modelech
Vždy se ujistěte, že pole je v modelu definováno v poli `$casts` jako `json` nebo `array`.

```php
protected $casts = [
    'metadata' => 'json',
    'audience_roles' => 'array',
];
```

## 5. Důležité upozornění
- **Case sensitivity:** Vyhledávání v JSON polích přes `where('field->key', 'value')` je v MySQL standardně case-sensitive pro přesné shody, ale `LIKE` zůstává case-insensitive (pokud je tak nastavená kolace databáze).
- **Indexy:** Pro často vyhledávaná JSON pole lze v MySQL 8 vytvořit funkční indexy (Functional Indexes) pro zvýšení výkonu.
