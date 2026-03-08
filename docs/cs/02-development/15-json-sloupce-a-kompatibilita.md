# JSON sloupce a kompatibilita databáze

Tento dokument popisuje omezení a pravidla pro práci s JSON sloupci v projektu Kbelští sokoli s ohledem na produkční prostředí Webglobe.

## Problém
Produkční databáze na hostingu Webglobe (aktuálně MariaDB starší verze) nepodporuje nativní JSON funkce jako `json_unquote` nebo `json_extract`. 
Při použití standardní Laravel syntaxe pro dotazování do JSONu (`->where('metadata->key', 'value')`) dochází k chybě:
`SQLSTATE[42000]: Syntax error or access violation: 1305 FUNCTION json_unquote does not exist`.

## Pravidla pro vývoj
1. **Zákaz syntaxe `->` v dotazech:** V metodách `where()`, `orWhere()` atd. nikdy nepoužívejte operátor `->` pro přístup k JSON polím.
2. **Vyhledávání přes `LIKE`:** Místo nativních funkcí používejte pro vyhledávání v JSON sloupcích operátor `LIKE`.
3. **Konvence pro `LIKE`:** 
    - Pro řetězce: `->where('metadata', 'LIKE', '%"key":"value"%')`
    - Buďte opatrní při vyhledávání čísel, která mohou být uložena bez uvozovek. Pokud je to možné, vynucujte v PHP ukládání jako string.
4. **Filament a translatable pole:** 
    - Standardní `searchable()` na translatable polích (Spatie Translatable) generuje SQL s `json_unquote`.
    - **Povinnost:** Vždy definujte vlastní query pro vyhledávání v translatable polích:
      ```php
      TextColumn::make('title')
          ->searchable(query: function ($query, string $search): Builder {
              return $query->where('title', 'LIKE', "%{$search}%");
          })
      ```
    - **Řazení:** Translatable pole nesmí být `sortable()`, protože MariaDB na hostingu neumí řadit podle JSON hodnot bez nativních funkcí.
    - **Relace:** Pokud vyhledáváte v relaci přes translatable pole (např. `team.name`), použijte `whereHas` s `LIKE`.
5. **Globální vyhledávání (Filament):** 
    - Filament Resource třídy by měly mít vypnuté globální vyhledávání pro translatable atributy, protože rovněž generuje `json_unquote`.
    - V Resource třídě definujte prázdné pole:
      ```php
      public static function getGloballySearchableAttributes(): array {
          return [];
      }
      ```

## Omezení UPDATE a DELETE se subquery (Error 1093)
MySQL a MariaDB nepodporují aktualizaci nebo mazání z tabulky, která je zároveň použita v subquery v klauzuli `WHERE`. 
Chyba: `SQLSTATE[HY000]: General error: 1093 You can't specify target table 'table_name' for update in FROM clause`.

Tento problém se typicky vyskytuje při sloučení záznamů (merge), kdy se snažíme převést vazby a zároveň zabránit duplicitám.

### Řešení
Místo subquery v SQL načtěte potřebná ID do PHP (např. pomocí `pluck()`) a následně je předejte do `whereIn()`. Tím se operace rozdělí do dvou nezávislých SQL dotazů a MariaDB nebude hlásit chybu.

**Špatně (způsobí pád v MariaDB):**
```php
DB::table('user_relationships')
    ->where('parent_id', $sourceId)
    ->whereNotExists(function ($query) use ($targetId) {
        $query->select(DB::raw(1))
            ->from('user_relationships as ur2')
            ->where('ur2.parent_id', $targetId)
            ->whereColumn('ur2.child_id', 'user_relationships.child_id');
    })
    ->update(['parent_id' => $targetId]);
```

**Správně (bezpečné a kompatibilní):**
```php
// 1. Načtení ID do PHP kolekce
$targetChildrenIds = DB::table('user_relationships')
    ->where('parent_id', $targetId)
    ->pluck('child_id');

// 2. Identifikace duplicit, které by po updatu kolidovaly
$duplicateIds = DB::table('user_relationships')
    ->where('parent_id', $sourceId)
    ->whereIn('child_id', $targetChildrenIds)
    ->pluck('id');

// 3. Smazání duplicit (volitelně)
if ($duplicateIds->isNotEmpty()) {
    DB::table('user_relationships')->whereIn('id', $duplicateIds)->delete();
}

// 4. Samotný update bez subquery
DB::table('user_relationships')
    ->where('parent_id', $sourceId)
    ->update(['parent_id' => $targetId]);
```

## Ghost uživatelé
Speciálním případem jsou tzv. "Ghost" uživatelé. Ti jsou v systému označeni v `metadata->is_ghost`.

**Pravidlo:** K detekci Ghost uživatelů nepoužívejte dotazy do `metadata`, ale kontrolu emailu. Ghost uživatelé mají vždy email začínající prefixem `ghost_`.

```php
// Špatně:
User::where('metadata->is_ghost', true)->get();

// Správně:
User::where('email', 'LIKE', 'ghost_%')->get();
```

## Příklad bezpečné úpravy
**Špatně (způsobí pád):**
```php
$match = BasketballMatch::where('metadata->external_id', $externalId)->first();
```

**Správně (kompatibilní):**
```php
$match = BasketballMatch::where('metadata', 'LIKE', '%"external_id":"' . $externalId . '"%')->first();
```

## Dopad na výkon
Vyhledávání přes `LIKE` s divokou kartou na začátku (`%...`) znemožňuje použití indexů a vede k Full Table Scan. Vzhledem k rozsahu dat projektu (tisíce řádků) je to v tuto chvíli přijatelné. Pokud by počet dat výrazně vzrostl, bude nutné přejít na dedikované sloupce pro nejčastěji vyhledávané atributy.
