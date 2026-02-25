# Oprava: Class "Laravel\Pail\PailServiceProvider" not found

Tato chyba se vyskytovala na produkci, protože v mezipaměti (`bootstrap/cache/services.php`) byl zaregistrován provider balíčku `laravel/pail`, který je však v `require-dev` a na produkci nebyl (nebo neměl být) nainstalován.

## Problém
1.  **Stale Cache:** Soubory v `bootstrap/cache/` nebyly při nasazení promazávány a mohly obsahovat zastaralé informace o balíčcích z vývojového prostředí.
2.  **Dev Dependencies:** Deployment skript `Envoy.blade.php` nepoužíval `--no-dev` příznak, takže se pokoušel instalovat i vývojové nástroje na produkci, což mohlo vést k nekonzistentnímu stavu.

## Řešení
1.  **Úprava Envoy.blade.php**:
    - Před spuštěním `composer install` byly přidány příkazy pro smazání všech souborů v `bootstrap/cache/*.php`. Tím se zajistí, že si Laravel znovu vygeneruje seznam providerů a balíčků na základě aktuálně nainstalovaných `vendor` balíčků.
    - Příkaz `composer install` nyní používá příznak `--no-dev`, aby se na produkci neinstalovaly zbytečné (a potenciálně chybující) vývojové balíčky.
2.  **Úprava .gitignore**:
    - Odstraněna redundantní výjimka `!bootstrap/cache/` z hlavního `.gitignore`, protože složka `bootstrap/cache/` má vlastní `.gitignore`, který správně ignoruje vše kromě sebe sama.

## Jak ověřit opravu
Po nasazení nové verze by se měl automaticky spustit:
```bash
rm -f bootstrap/cache/*.php
composer install --no-dev ...
php artisan optimize
```
Pokud aplikace stále padá, je možné ručně smazat soubory v `bootstrap/cache/` přímo na serveru:
```bash
rm /home/html/kbelstisokoli.cz/public_html/secret/bootstrap/cache/*.php
```

## Rollback postup
V případě problémů se skriptem `Envoy.blade.php` lze změny vrátit v Gitu. Manuální promazání cache však nemá žádný negativní vliv, protože se při příštím požadavku nebo spuštění `artisan` znovu vygeneruje.
