# Upgrade na Laravel 13 (Produkce Webglobe)

Tento dokument popisuje kroky potřebné pro bezpečný upgrade aplikace na Laravel 13 v produkčním prostředí hostingu Webglobe. Postup je optimalizován pro **Fish shell**, který je na tomto hostingu výchozí.

## Předpoklady
- **PHP 8.4+**: Laravel 13 vyžaduje minimálně PHP 8.4.
- **Node.js 18+**: Potřebné pro sestavení assetů pomocí Vite 6.
- **Composer 2.2+**.

## Automatizovaný upgrade (Doporučeno)
Nejjednodušší způsob je použít existující Artisan příkaz pro nasazení, který již interně řeší detekci správných binárek a Laravel 13 podporuje:

```bash
php artisan app:deploy
```

Tento příkaz provede `git pull`, `composer install`, `migrate`, `npm build` a pročištění cache v jednom kroku.

---

## Manuální upgrade přes Fish Shell
Pokud potřebujete provést upgrade ručně přímo na serveru, postupujte podle těchto kroků.

### 1. Stažení kódu
```bash
cd /cesta/k/projektu
git fetch origin main
git reset --hard origin/main
git clean -df
```

### 2. Aktualizace závislostí (Composer)
Na Webglobe je nutné explicitně volat správnou verzi PHP (8.4).

```bash
# Zjištění cesty ke composeru a spuštění pod PHP 8.4
php8.4 (which composer) install --no-interaction --optimize-autoloader --no-dev
```

### 3. Migrace databáze
Laravel 13 může obsahovat změny v systémových tabulkách (např. joby, sessions), proto je migrace kritická.

```bash
php8.4 artisan migrate --force
```

### 4. Sestavení assetů (Vite 6 / Tailwind v4)
Vite vyžaduje Node.js 18+. Ve Fish shellu na Webglobe použijte tento blok pro nalezení a nastavení správné binárky:

```bash
# 1. Najděte dostupnou verzi Node.js (18 nebo vyšší)
set -l NODE_BIN ""
for n in (which -a node22 node20 node18 node | grep -v ".node_bin"; or ls /opt/alt/node*/usr/bin/node)
    if $n -v | string match -rq '^v(18|2[0-9])'
        set NODE_BIN $n
        break
    end
end

# 2. Nastavení lokálního prostředí pro tuto session
if test -n "$NODE_BIN"
    mkdir -p .node_bin
    ln -sf (realpath $NODE_BIN) .node_bin/node
    set -l NPM_BIN (which -a npm22 npm20 npm18 npm | grep -v ".node_bin" | head -n1; or which (string replace "node" "npm" $NODE_BIN))
    ln -sf (realpath $NPM_BIN) .node_bin/npm
    set -gx PATH (realpath .node_bin) $PATH
end

# 3. Instalace a build
npm install
npm run build
```

### 5. Finalizace a pročištění cache
Po upgradu na novou major verzi Laravelu je nezbytné vymazat všechny zkompilované soubory.

```bash
php8.4 artisan optimize:clear
php8.4 artisan filament:optimize
php8.4 artisan icons:sync
php8.4 artisan view:cache
php8.4 artisan optimize
```

## Řešení potíží
- **PHP verze**: Pokud `php8.4` neexistuje, použijte `/usr/bin/php8.4` nebo kontaktujte podporu Webglobe pro aktivaci.
- **Node.js**: Pokud se build zastaví s chybou `SyntaxError: Unexpected token '??='`, znamená to, že se stále používá stará verze Node.js (v14/v16). Ověřte verzi pomocí `node -v`.
- **JSON sloupce**: Na Webglobe stále platí zákaz používání nativních `json` typů v migracích (viz `01-nasazeni.md`). Laravel 13 toto automaticky neřeší, proto zachovejte `longText` v migracích.
