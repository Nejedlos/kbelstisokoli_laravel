# Deployment (Nasazení na produkci)

Projekt využívá moderní přístup k nasazení, který kombinuje **GitHub** ([https://github.com/Nejedlos/kbelstisokoli_laravel](https://github.com/Nejedlos/kbelstisokoli_laravel)) jako zdrojový repozitář a **SSH konzoli** na hostingu (Webglobe) pro finální kroky.

> KRITICKÉ UPOZORNĚNÍ K ASSETŮM (Vite)
>
> Po jakékoliv změně vzhledu (úprava CSS/JS) je NUTNÉ spustit `npm run build`, jinak se změny na produkci/pro náhled NEPROJEVÍ. Týká se to i Filament auth UI (`resources/css/filament-auth.css`, `resources/js/filament-error-handler.js`).

## Automatizované nasazení (Doporučeno)

Pro maximální zjednodušení byly vytvořeny Artisan příkazy, které obstarají vše od prvotního nastavení (včetně Gitu s tokenem) až po pravidelné nasazování.

### 1. Prvotní nastavení (Setup)
Pokud nasazujete na nový server nebo chcete přenastavit parametry, spusťte:

```bash
php artisan app:production:setup
```

Příkaz se vás interaktivně zeptá na:
- **IP adresu** serveru.
- **SSH uživatele** (např. `michal`).
- **Cestu** k projektu na serveru.
  - *Novinka:* Příkaz se pokusí automaticky proskenovat server přes SSH a nabídne vám nalezené adresáře (např. `www`, `public_html`) k výběru.
- **GitHub Personal Access Token** (PAT).
- **Veřejný adresář:** Možnost nastavit symlink, pokud hosting vyžaduje umístění webu v konkrétní složce (např. `/www`), ale zbytek aplikace chcete mít jinde.
- **Konfigurace databáze:** Můžete přímo zadat údaje k DB, které se automaticky zapíší do `.env` na serveru.

**Proč GitHub Token?**
Díky tokenu se Git na serveru autentizuje automaticky bez nutnosti ručně generovat a přidávat SSH klíče do GitHubu. Token je bezpečně uložen v `.env` na vašem localhostu a použit pro `git clone` / `pull` na serveru.

### 2. Pravidelné nasazení (Deploy)
Jakmile máte setup hotový, stačí pro každé další nasazení spustit:

```bash
php artisan app:deploy
```

Tento příkaz automaticky:
1. Provede `git pull` s využitím tokenu.
2. Spustí `composer install` (optimalizovaný pro produkci).
3. Spustí migrace databáze (`migrate --force`).
4. Nainstaluje NPM balíčky a sestaví assety (`npm run build`).
5. Synchronizuje ikony a optimalizuje cache aplikace.

---

## Předpoklady na serveru (Webglobe)
1. **PHP:** Verze 8.4+ (včetně JIT optimalizací).
2. **SSH Přístup:** Povoleno v administraci Webglobe.
3. **Git:** Musí být nainstalován.
4. **Composer:** Globálně dostupný nebo jako `composer.phar` v rootu.
5. **Node.js & NPM:** Pro buildování assetů (Vite).

## Postup nasazení (Manuální přes SSH)
Pokud chcete nasadit novou verzi ručně, připojte se přes SSH a proveďte:

```bash
cd /cesta/k/projektu
git pull origin main
composer install --no-interaction --optimize-autoloader --no-dev
php artisan migrate --force
npm install
npm run build
php artisan app:icons:sync
php artisan optimize
```

> Tip: Po buildu udělejte „tvrdý refresh“ v prohlížeči (Cmd/Ctrl + Shift + R). V případě potřeby pročistěte cache pohledů: `php artisan view:clear` a `php artisan filament:clear-cached-components`.


## GitHub Workflow
Na GitHub se nahrává pouze zdrojový kód. Soubory jako `.env`, `vendor/`, `node_modules/` a buildované soubory v `public/build/` jsou ignorovány (dle standardu). 
Všechny instalace a buildy probíhají až na produkčním serveru, což zaručuje, že prostředí je konzistentní.
