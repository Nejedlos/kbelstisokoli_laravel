# Práce s JSON poli a kompatibilita s DB

Tento dokument definuje pravidla pro práci s JSON daty v databázi. **POZOR: Produkční prostředí na hostingu Webglobe (původní DB server 62.109.154.152) má omezenou podporu pro JSON funkce v SQL.**

## 1. Hlavní JSON pole v projektu
Většina modelů v projektu využívá JSON pole pro metadata, konfiguraci nebo lokalizované překlady.
- **metadata:** Obsahuje doplňkové informace (např. externí ID, synchronizační údaje).
- **audience_roles:** Pole obsahující seznam rolí (např. `["admin", "member"]`).
- **Lokalizace:** Pole s překlady (`name`, `description` atd.) spravovaná balíčkem `spatie/laravel-translatable`.

## 2. Kritické omezení: Nedostupnost JSON funkcí v SQL
Produkční databáze na Webglobe **nepodporuje** nativní JSON funkce v SQL dotazech. To znamená:
- **ZÁKAZ** používání `whereJsonContains` v Eloquent query builderu.
- **ZÁKAZ** používání operátoru `->` (např. `where('metadata->external_id', ...)`).
- **ZÁKAZ** používání `JSON_EXTRACT`, `JSON_CONTAINS` a dalších JSON funkcí v `whereRaw`.

Při použití těchto funkcí vyhodí databáze chybu: `FUNCTION ...json_contains does not exist`.

## 3. Doporučené postupy (Safe alternatives)

### 3.1 Filtrování v paměti (PHP) - DOPORUČENO
Pokud tabulka obsahuje maximálně stovky záznamů, načtěte data a profiltrujte je pomocí Laravel Collections.

**Příklad (Správně):**
```php
// Načteme všechny a profiltrujeme v PHP
$opponent = Opponent::all()->first(function($op) use ($extId) {
    return ($op->metadata['external_id'] ?? null) == $extId;
});
```

**Příklad (Špatně - způsobí SQL Error na produkci):**
```php
$opponent = Opponent::where('metadata->external_id', $extId)->first();
```

### 3.2 Použití operátoru LIKE s opatrností
Pro jednoduchá JSON pole (např. seznamy rolí) lze použít operátor `LIKE`, pokud nehrozí záměna podřetězců.

**Příklad (Správně pro role):**
```php
$query->where('audience_roles', 'LIKE', '%"admin"%');
```

### 3.3 Filtrování v rámci sezóny/týmu
Pro zápasy a statistiky vždy nejprve omezte dotaz na konkrétní sezónu a tým, a teprve poté filtrujte v PHP.

```php
$match = BasketballMatch::where('season_id', $seasonId)
    ->where('team_id', $teamId)
    ->get()
    ->first(function($m) use ($extId) {
        return ($m->metadata['external_id'] ?? null) == $extId;
    });
```

## 4. Castování v modelech
Vždy se ujistěte, že pole je v modelu definováno v poli `$casts` jako `json` nebo `array`. To umožňuje transparentní práci s daty v PHP i bez SQL funkcí.

```php
protected $casts = [
    'metadata' => 'json',
    'audience_roles' => 'array',
];
```

## 5. Změny provedené v březnu 2026
V rámci opravy synchronizace statistik byly upraveny následující třídy, aby nepoužívaly JSON SQL funkce:
- `OpponentSyncService`
- `MatchSyncService`
- `PlayerSyncService`
- `StatisticSyncService`
- `HelpQueryService`
- `HelpArticle` a `HelpCategory` (scopy)
