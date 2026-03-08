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
