# Fix: Scheduler Exit Code 126 na produkci (Webglobe)

## Popis problému
Dne 7. 8. 2026 v 03:30 došlo na produkci k pádu plánovače úloh s chybou:
`Scheduled command ['/usr/bin/php8.4' 'artisan' queue:prune-failed --hours=24] failed with exit code [126].`

Následně po prvním pokusu o opravu došlo k chybě:
`BadMethodCallException: Method Illuminate\Console\Scheduling\Schedule::usePhpBinary does not exist.`

### Příčina
Chyba `exit code 126` znamená, že proces byl vytvořen, ale nešel spustit (obvykle "Permission denied" pro danou binárku). Na sdíleném hostingu Webglobe webový uživatel (pod kterým běží PHP-FPM a tedy i HTTP endpoint pro cron) nemá práva spouštět CLI binárku PHP na absolutní cestě `/usr/bin/php8.4`.

Laravel při použití `Schedule::command()` automaticky vytváří subproces přes `Symfony\Process`, který se snaží detekovat cestu k PHP. Tato detekce v kombinaci s omezeními hostingu vedla k selhání.

## Provedené změny

### 1. Úprava `routes/console.php`
Změnili jsme způsob spouštění kritických úloh z `Schedule::command()` na `Schedule::call()`. 
- **Původní:** `Schedule::command('queue:prune-failed --hours=24')`
- **Nové:** `Schedule::call(fn() => Artisan::call('queue:prune-failed', ['--hours' => 24]))`
Tato změna zajišťuje, že příkaz běží v rámci aktuálního PHP procesu a nevyžaduje spouštění externí binárky.

### 2. Úprava `bootstrap/app.php`
Původně zamýšlené volání `$schedule->usePhpBinary()` bylo odstraněno, protože v této verzi Laravelu neexistuje. Místo toho se spoléháme na:
1. Konverzi příkazů na `Schedule::call()`, které běží v aktuálním procesu.
2. Nastavení environmentální proměnné `PHP_BINARY` v bloku `booting` souboru `bootstrap/app.php`:
```php
if ($app->environment('production') && $php = config('app.prod_php_binary')) {
    putenv("PHP_BINARY=$php");
}
```
Toto nastavení pomáhá Symfony Process komponentě najít správnou binárku, pokud je subproces přesto vyžadován.

### 3. Úprava `routes/public.php` (Diagnostika a Fallback)
Vylepšili jsme start queue workera:
- Přidána diagnostika funkčnosti PHP binárky před jejím použitím.
- Přidán automatický fallback na `php8.4` (z PATH), pokud absolutní cesta nebo konfigurovaná cesta selže.
- Přidáno logování chyb do Laravel logu pro snadnější diagnostiku startu workera na pozadí.

## Verifikace
- Kontrola syntaxe souborů.
- Ověření, že `Schedule::call` je v Laravelu 12 správně použito s `.name()`.
- Na produkci se změna projeví při dalším běhu cronu.

## Poznámka pro budoucí úpravy
Na Webglobe se vyhýbejte používání `Schedule::command()` pro systémové příkazy volané přes HTTP cron. Vždy preferujte `Schedule::call(fn() => Artisan::call(...))`.
