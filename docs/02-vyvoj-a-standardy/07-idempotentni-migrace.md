# Idempotentní migrace

Všechny migrace v projektu byly upraveny tak, aby byly **idempotentní**. To znamená, že je lze bezpečně spouštět opakovaně, i když byly některé změny již v databázi aplikovány (např. při selhání nasazení a jeho opakování).

## Použité postupy

### 1. Vytváření tabulek
Při vytváření tabulek používáme kontrolu existence:

```php
if (!Schema::hasTable('table_name')) {
    Schema::create('table_name', function (Blueprint $table) {
        // ...
    });
}
```

### 2. Přidávání sloupců
Při přidávání sloupců do existujících tabulek kontrolujeme každý sloupec zvlášť:

```php
Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'new_column')) {
        $table->string('new_column')->nullable();
    }
});
```

### 3. Přejmenování tabulek
Při přejmenování kontrolujeme existenci původní i nové tabulky:

```php
if (Schema::hasTable('old_name') && !Schema::hasTable('new_name')) {
    Schema::rename('old_name', 'new_name');
}
```

### 4. Změny typů sloupců (`->change()`)
Při změně typu sloupce (např. na `longText` pro překlady) používáme standardní `change()`, ale celou operaci balíme do kontroly existence tabulky, aby nedocházelo k chybám, pokud tabulka ještě neexistuje (např. při paralelním vývoji).

### 5. Specifika pro produkční prostředí (Webglobe)
Na produkčním serveru (Webglobe) platí kritické omezení: **databáze nepodporuje datový typ `json`**.

Při vytváření nebo úpravě migrací:
- Místo `$table->json('field')` **vždy** používejte `$table->longText('field')`.
- Toto platí pro všechna translatable pole i metadata.
- Laravel (a balíček `spatie/laravel-translatable`) se postará o serializaci/deserializaci JSON dat do tohoto textového pole.

Dále může standardní schéma Laravelu (`Schema::hasColumn`, `Schema::table`) selhat s chybou `Unknown column 'generation_expression' in 'field list'` kvůli specifické konfiguraci MariaDB/MySQL a ovladačů.

V takových případech používáme **vlastní kontrolu pomocí raw SQL**:

```php
$prefix = DB::getTablePrefix();
$table = $prefix . 'ai_documents';

try {
    $columnExists = DB::select("SHOW COLUMNS FROM {$table} LIKE 'column_name'");
    if (empty($columnExists)) {
        DB::statement("ALTER TABLE {$table} ADD COLUMN column_name TEXT NULL");
    }
} catch (\Throwable $e) {
    // Log or ignore
}
```

Tento přístup obchází introspekci schématu a přímo komunikuje s databází, čímž zajišťuje funkčnost i v problematických prostředích.

## Proč je to důležité
Na produkčním prostředí (Webglobe) může při nasazení dojít k neočekávaným stavům. Idempotentní migrace zajišťují, že `php artisan migrate` v rámci `Envoy` nasazení vždy doběhne úspěšně a nezanechá databázi v nekonzistentním stavu.
