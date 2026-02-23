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

Nebo můžete rovnou zadat SSH příkaz:

```bash
php artisan app:production:setup "ssh -p 20001 ssh-588875@dw191.webglobe.com"
```

Příkaz se vás interaktivně zeptá na následující údaje (které se pokusí předvyplnit, pokud jste zadali SSH příkaz):
- **Hostitele** (IP adresa nebo doména, např. `dw191.webglobe.com`). Slouží k nalezení serveru v síti.
- **SSH port** (výchozí `22`, u Webglobe často `20001`). Specifická „brána“, přes kterou se SSH připojuje.
- **SSH uživatele** (např. `ssh-588875`). Jméno, pod kterým se budou na serveru spouštět všechny instalační a aktualizační příkazy.
- **PHP binárka:** Možnost specifikovat cestu k PHP (např. `php8.4`). Důležité pro hostingy s více verzemi PHP.
- **Kontrola spojení a klíčů:** *Novinka:* Příkaz automaticky otestuje, zda se lze k serveru připojit bez hesla. Pokud ne, nabídne vám automatické vygenerování a nahrání SSH klíče na server. K tomu budete jednou vyzváni k zadání hesla k serveru. Tím se zajistí, že následné deploje budou probíhat zcela automaticky bez ptaní na heslo.
- **Kontrola požadavků:** Příkaz automaticky prověří verze PHP (vyžadováno 8.4+), Gitu, Composeru, Node.js (vyžadováno 18.0+) a NPM na serveru přes zadaný port a s využitím zvolené PHP binárky.
- **Adresáře projektu (Interaktivní prohlížeč):** *Novinka:* Příkaz obsahuje vestavěný prohlížeč souborů na serveru.
  - **Funkční adresář:** Kam se nainstaluje jádro aplikace (včetně `.git`, `vendor`, `.env`). Doporučujeme umístit mimo veřejně přístupnou složku pro maximální bezpečnost (např. `/private` nebo `/app`).
  - **Veřejný adresář:** Kam se synchronizuje obsah složky `public` (např. `/www` nebo `/public_html`).
- **Automatické patchování index.php:** Pokud zvolíte rozdílné adresáře, systém v `public/index.php` na serveru automaticky upraví cesty k `autoload.php` a `bootstrap/app.php` tak, aby směřovaly do funkčního adresáře.
- **GitHub Personal Access Token** (PAT). Potřebný pro bezpečné stažení kódu z GitHubu přímo na server bez nutnosti ručního nastavování klíčů.
- **Konfigurace databáze:** Příkaz se dotáže na typ databáze, hostitele, port, název, uživatele a heslo. *Novinka:* Lze zadat i **prefix tabulek** (např. `new_`), což umožní mít v jedné databázi více aplikací nebo oddělit produkční data. Tyto údaje se automaticky zapíší do `.env` na serveru a vygeneruje se nový `APP_KEY`.

**Proč jsou tyto údaje potřeba?**
Bez přístupu k SSH konzoli (Host, Port, Uživatel) by nebylo možné automaticky provádět operace jako `git pull`, `composer install` nebo `npm run build` přímo na serveru. Systém tyto údaje používá k tomu, aby se za vás „přihlásil“ a provedl všechny potřebné kroky jedním příkazem z vašeho počítače.

### 2. Pravidelné nasazení (Deploy)
Jakmile máte setup hotový, stačí pro každé další nasazení spustit:

```bash
php artisan app:deploy
```

Tento příkaz automaticky:
1. Ověří verzi zvolené PHP binárky na serveru (musí být 8.4.0+).
2. Provede `git fetch` a `git reset --hard` (vynutí stav odpovídající repozitáři, případné lokální změny na serveru budou zahozeny).
3. Spustí `composer install` (s využitím zvolené PHP binárky, optimalizovaný pro produkci).
4. Synchronizuje veřejné soubory do zvoleného veřejného adresáře a zajistí správné cesty v `index.php`.
5. Spustí migrace databáze (`migrate --force`).
6. Nainstaluje NPM balíčky a sestaví assety (`npm run build`).
7. Synchronizuje ikony a optimalizuje cache aplikace.

### 3. Rychlá synchronizace po FTP (Sync)
Pokud soubory na server nahráváte ručně (např. přes FTP klienta nebo automatickou synchronizaci v IDE), můžete následně spustit pouze konfigurační a databázové kroky:

```bash
php artisan app:sync
```

Tento příkaz:
1. Ověří verzi PHP na serveru.
2. Vytvoří nebo aktualizuje `.env` soubor podle vašeho lokálního nastavení.
3. Synchronizuje obsah složky `public` do veřejného adresáře a opraví cesty v `index.php`.
4. Spustí migrace databáze (`migrate --force`).
5. Provede synchronizaci ikon a optimalizaci mezipaměti (`optimize`).

Je to ideální volba pro rychlé promítnutí změn v kódu, které jste právě nahráli, aniž byste museli spouštět celý proces nasazení s Gitem a NPM.

---

### Odolnost proti chybám
Oba příkazy (`app:production:setup` i `app:deploy`) jsou vybaveny **automatickým opakováním**. 
- Pokud selže SSH spojení během setupu, systém vám umožní upravit údaje nebo zkusit znovu nahrát SSH klíč (včetně nového dotazu na heslo).
- Veškeré zadané údaje o serveru se ukládají do `.env` ihned po potvrzení, takže i při přerušení setupu si je systém pro příště pamatuje.
- Pokud dojde k chybě během běhu (např. selže `composer install` nebo `npm run build`), systém se vás zeptá, zda chcete operaci zkusit znovu. To umožňuje opravit příčinu (např. chybějící balíček na serveru) a pokračovat bez nutnosti znovu zadávat všechny konfigurační údaje.

Navíc je bootstrap aplikace v `bootstrap/app.php` zabezpečen tak, aby selhání připojení k databázi (např. při prvotním nasazení před migracemi) nezpůsobilo pád `composer install` nebo jiných CLI příkazů. V `Envoy.blade.php` je také zajištěno, že `.env` soubor s produkčními údaji je vytvořen dříve, než se aplikace začne instalovat, což minimalizuje riziko nekonzistencí.

---

## Předpoklady na serveru (Webglobe)
1. **PHP:** Verze 8.4+ (včetně JIT optimalizací).
2. **SSH Přístup:** Povoleno v administraci Webglobe.
3. **Git:** Musí být nainstalován.
4. **Composer:** Globálně dostupný nebo jako `composer.phar` v rootu.
5. **Node.js & NPM:** Pro buildování assetů (Vite 6 vyžaduje Node 18.0+).

## Postup nasazení (Manuální přes SSH - příklad pro php8.4)
Pokud chcete nasadit novou verzi ručně, připojte se přes SSH a proveďte:

```bash
cd /cesta/k/projektu
git fetch origin main
git reset --hard origin/main
git clean -df
git prune
php8.4 $(which composer) install --no-interaction --optimize-autoloader --no-dev
php8.4 artisan migrate --force
npm install
npm run build
php8.4 artisan app:icons:sync
php8.4 artisan optimize
```

> Tip: Po buildu udělejte „tvrdý refresh“ v prohlížeči (Cmd/Ctrl + Shift + R). V případě potřeby pročistěte cache pohledů: `php artisan view:clear` a `php artisan filament:clear-cached-components`.


## GitHub Workflow
Na GitHub se nahrává pouze zdrojový kód. Soubory jako `.env`, `vendor/`, `node_modules/` a buildované soubory v `public/build/` jsou ignorovány (dle standardu). 
Všechny instalace a buildy probíhají až na produkčním serveru, což zaručuje, že prostředí je konzistentní.

