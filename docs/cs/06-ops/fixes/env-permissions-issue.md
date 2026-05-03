# Problém s načítáním .env na produkci (Oprávnění)

## Popis problému
Na produkčním serveru (Webglobe) se v uživatelském rozhraní (např. v Email Debugu) zobrazovaly výchozí hodnoty Laravelu (např. `127.0.0.1` pro SMTP host), přestože v souboru `.env` byly nastaveny správné produkční údaje.

## Příčina
1. **Rozdílní uživatelé:** SSH uživatel (`ssh-588875`) je odlišný od uživatele, pod kterým běží webový server (PHP-FPM/Apache, např. `kbelstisokoli.cz`).
2. **Restriktivní oprávnění:** Soubor `.env` byl nastaven na `600` (`-rw-------`) a jeho vlastníkem byl SSH uživatel.
3. **Chybějící Cache:** Aplikace neměla vygenerovanou konfigurační cache (`bootstrap/cache/config.php`).

Kvůli těmto bodům webový server nemohl přečíst soubor `.env` a Laravel se tiše přepnul na výchozí hodnoty definované v souborech v adresáři `config/`.

## Řešení
Pro vyřešení bez nutnosti měnit oprávnění souboru `.env` (které je z bezpečnostního hlediska pro SSH uživatele správné) byla využita konfigurační cache:

1. Spuštěn příkaz pod SSH uživatelem:
   ```bash
   php8.4 artisan config:cache
   ```
2. Tento příkaz vygeneroval soubor `bootstrap/cache/config.php` s oprávněním `644`, který již webový server může přečíst.
3. Všechny hodnoty z `.env` jsou nyní "zapečené" v této cache a aplikace funguje správně.

## Doporučení
- Při každé změně v `.env` na produkci je nutné znovu spustit `php artisan config:cache`, aby se změny projevily i ve webovém rozhraní.
- Pokud je vyžadováno, aby aplikace četla `.env` dynamicky bez cache, musí být oprávnění souboru `.env` změněno na `644` (riziko: soubor bude čitelný pro všechny uživatele na sdíleném hostingu v rámci stejné skupiny/webu).
