# Deployment (Nasazení na produkci)

Projekt využívá moderní přístup k nasazení, který kombinuje **GitHub** ([https://github.com/Nejedlos/kbelstisokoli_laravel](https://github.com/Nejedlos/kbelstisokoli_laravel)) jako zdrojový repozitář a **SSH konzoli** na hostingu (Webglobe) pro finální kroky.

## Předpoklady na serveru (Webglobe)
1. **PHP:** Verze 8.4+ (včetně JIT optimalizací).
2. **SSH Přístup:** Povoleno v administraci Webglobe.
3. **Git:** Musí být nainstalován.
4. **Composer:** Globálně dostupný nebo jako `composer.phar` v rootu.
5. **Node.js & NPM:** Pro buildování assetů (Vite).
6. **SSH klíč:** Na serveru musí být vygenerován SSH klíč a přidán do GitHubu (Deploy keys), aby bylo možné provádět `git pull`.

## Postup nasazení (Manuální přes SSH)
Pokud chcete nasadit novou verzi ručně, připojte se přes SSH a proveďte:

```bash
cd /cesta/k/projektu
git pull origin main
composer install --no-interaction --optimize-autoloader --no-dev
php artisan migrate --force
npm install
npm run build
php artisan optimize
```

## Automatizace pomocí Laravel Envoy
V projektu je připraven soubor `Envoy.blade.php`. Po nastavení IP adresy a cesty v tomto souboru můžete nasazovat z lokálního stroje jediným příkazem:

```bash
envoy run deploy
```

Tento příkaz provede všechny výše zmíněné kroky automaticky na vzdáleném serveru.

## GitHub Workflow
Na GitHub se nahrává pouze zdrojový kód. Soubory jako `.env`, `vendor/`, `node_modules/` a buildované soubory v `public/build/` jsou ignorovány (dle standardu). 
Všechny instalace a buildy probíhají až na produkčním serveru, což zaručuje, že prostředí je konzistentní.
