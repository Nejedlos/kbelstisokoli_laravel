# Deployment (Nasazení na produkci)

Projekt využívá moderní přístup k nasazení, který kombinuje **GitHub** ([https://github.com/Nejedlos/kbelstisokoli_laravel](https://github.com/Nejedlos/kbelstisokoli_laravel)) jako zdrojový repozitář a **SSH konzoli** na hostingu (Webglobe) pro finální kroky.

> Assety pro produkci vždy sestaví workflow z přesného commitu. Při běžném nasazení proto lokálně nespouštějte ani nenahrávejte `public/build`; push do `main` udělá jediný reprodukovatelný build sám.

## Automatizované nasazení z `main` (doporučeno)

Běžné produkční nasazení spouští push do větve `main`. GitHub Actions použije projektový macOS runner označený `kbelstisokoli-deploy`, nejprve paralelně spustí testy, jednou sestaví Vite assety a rychlou kompresí vytvoří hotový produkční archiv včetně `vendor`. Statické soubory v `public/assets` mají vlastní obsahový hash a na server se přenesou jen při změně. Server už neprovádí Git reset, Composer install ani NPM build.

Produkční runner má vlastní registraci a pracovní adresář; nesdílí registraci s jiným repozitářem. Pull requesty zůstávají na GitHub-hosted runneru. Workflow používá již nainstalované PHP, Composer, Node, NPM a vícevláknový `pigz`; před každým během ověří minimální verze, potřebná PHP rozšíření a paměť alespoň 512 MB pro paralelní testy. Mezi běhy zachová vývojový `vendor` a oddělenou produkční Composer cache. Produkční `vendor` má vlastní hash a balí se i přenáší jen po změně Composer závislostí nebo platformy. Release aplikace se vždy skládá z přesného Git commitu do nového dočasného adresáře. Staré nebo nesledované soubory z runneru se proto do produkce nepřenášejí. SSH klíč z GitHub Environment se zapisuje pouze do `$RUNNER_TEMP` konkrétního jobu, takže workflow nemění osobní `~/.ssh` uživatele Macu.

### Produkční uspořádání Webglobe

Doména stále obsluhuje skutečný adresář `/home/html/kbelstisokoli.cz/public_html/www`; nastavení hostingu se nemění. Jednotlivé verze aplikace jsou v `/home/html/kbelstisokoli.cz/public_html/secret/deploy/releases/<git-sha>` a symlink `secret/deploy/current` ukazuje na právě aktivní verzi.

Stabilní `www/index.php` načítá aplikaci přes `current`. Verzované veřejné adresáře a kořenové soubory, například `assets`, `build`, `js`, `css`, `fonts`, `images`, favicony, manifest, `robots.txt` a sitemap, sledují stejný release. `assets` ukazuje na neměnný strom `secret/deploy/managed-assets/<hash>` a Composer závislosti na `secret/deploy/managed-vendor/<hash>`; zapisovaný podadresář partnerských log vede na `secret/deploy/shared/assets-partners`. `www/uploads`, `www/storage`, produkční `.env`, aplikační `storage`, dříve publikované soubory v `www/vendor` a vygenerovaná sada Blade ikon zůstávají na stabilních cestách. `.htaccess` se nemění.

Deployment drží serverový `flock`, ověří SHA-256 archivu, připraví migrace a cache mimo běžící verzi a potom atomicky přepne `current`. Nový release musí na `/up` vrátit hlavičku `X-App-Release` s přesným SHA. Při neúspěchu se pointer vrátí na předchozí verzi. Redis full-page cache používá SHA v klíči, takže přepnutí nevyžaduje globální mazání cache.

Při přípravě nové verze se cache konfigurace, rout, pohledů, Filament komponent a Blade ikon vytvářejí právě jednou. Přenosové soubory pro každý release mají vlastní adresář podle SHA a samotné atomické povýšení se po výpadku SSH neopakuje; opakují se pouze bezpečné čtecí a stagingové kroky.

Migrace se při release s nezměněným stromem `database/migrations` bezpečně přeskočí: aktivní předchozí release už úspěšně dokončil povinnou migraci. Přidání, úprava nebo odstranění migračního souboru vždy vynutí standardní `migrate --force` před atomickým přepnutím.

Pouze první přechod krátce zapne Laravel maintenance režim, počká na skutečné ukončení všech již běžících PHP requestů a přesune původní fyzický adresář partnerských log do sdílené cesty. Pokud se request neuvolní do pěti minut, deployment přesun zruší a aplikaci znovu zapne. Původní i nový release pak používají stejná data i při rollbacku. Další deploymenty tento krok přeskakují.

### Běžné použití

```bash
git push origin main
```

Workflow `Validate and deploy` provede validaci i deployment. Není potřeba přihlašovat se na server ani spouštět `app:deploy`.

Přenos přes SSH opakuje pouze chyby spojení. Před opakovaným přenosem porovná SHA-256 již nahraného archivu, takže po krátkém výpadku neposílá znovu kompletní soubor. Chybu migrace, validace nebo health checku neopakuje a serverový skript provede rollback.

### Nouzové nasazení přímo z pracovního počítače

Pokud validace v GitHub Actions projde, ale spojení mezi GitHubem a Webglobe opakovaně selže, lze tentýž commit nahrát přes místní SSH:

```bash
scripts/deploy-production-from-local.sh --ci-validated
```

Přepínač `--ci-validated` použijte jen tehdy, když krok `Validate code` pro přesný commit úspěšně proběhl. Skript i tak lokálně sestaví produkční frontend. Bez přepínače spustí také Pint a celou paralelní testovací sadu. V obou režimech vyžaduje čistou větev `main`, ověří shodu s `origin/main`, vytvoří stejné neměnné archivy a použije stejný atomický serverový skript, checksumy, sdílená data, rollback a health check jako GitHub Actions.

Výchozí SSH cíl odpovídá tomuto projektu. Lze jej dočasně přepsat proměnnými `PRODUCTION_SSH_HOST`, `PRODUCTION_SSH_PORT`, `PRODUCTION_SSH_USER`, `PRODUCTION_PATH` a `PRODUCTION_PUBLIC_PATH`. Přihlašovací údaje se do repozitáře neukládají; používá se místní SSH klíč a povinná kontrola `known_hosts`.

### Ruční návrat aplikace

Symlink `secret/deploy/previous` ukazuje na verzi aktivní před posledním úspěšným přepnutím. Návrat se provádí pouze při vědomé produkční údržbě atomickou výměnou `current`; před návratem je nutné ověřit kompatibilitu již provedených databázových migrací.

## Starší nástroje pro ruční údržbu

Následující příkazy zůstávají v projektu kvůli diagnostice a mimořádné ruční obnově. Pro běžné produkční nasazení se nepoužívají, protože pracují přímo v původním checkoutu `secret` a prodlužují odstávku. Standardem je workflow z `main` popsané výše.

### Starší interaktivní nastavení (`app:production:setup`)
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
- **PHP binárka:** *Vylepšeno:* Systém se nejprve připojí k serveru a automaticky se pokusí najít nejvhodnější verzi PHP (8.4+). Tuto verzi vám pak nabídne jako výchozí hodnotu, kterou stačí potvrdit.
- **Node.js binárka:** Systém se pokusí najít Node.js 20+ potřebné pro Vite 7; v neinteraktivním SSH načte i `nvm`. Na Webglobe typicky najde `node22`, `node20` nebo použije výchozí `node`.
- **Kontrola spojení a klíčů:** Příkaz automaticky otestuje, zda se lze k serveru připojit bez hesla. Pokud ne, nabídne vám automatické vygenerování a nahrání SSH klíče na server. K tomu budete jednou vyzváni k zadání hesla k serveru.
- **Kontrola požadavků:** Po potvrzení binárek systém provede finální revizi a potvrdí dostupnost Gitu, Composeru a NPM.
- **Adresáře projektu (Interaktivní prohlížeč):** *Novinka:* Příkaz obsahuje vestavěný prohlížeč souborů na serveru.
  - **Funkční adresář:** Kam se nainstaluje jádro aplikace (včetně `.git`, `vendor`, `.env`). Doporučujeme umístit mimo veřejně přístupnou složku pro maximální bezpečnost (např. `/private` nebo `/app`).
  - **Veřejný adresář:** Kam se synchronizuje obsah složky `public` (např. `/www` nebo `/public_html`).
- **Automatické patchování index.php:** Pokud zvolíte rozdílné adresáře, systém v `public/index.php` na serveru automaticky upraví cesty k `autoload.php` a `bootstrap/app.php` tak, aby směřovaly do funkčního adresáře.
- **GitHub Personal Access Token** (PAT). Potřebný pro bezpečné stažení kódu z GitHubu přímo na server bez nutnosti ručního nastavování klíčů.
- **Konfigurace databáze:** Příkaz se dotáže na typ databáze, hostitele, port, název, uživatele a heslo. *Novinka:* Lze zadat i **prefix tabulek** (např. `new_`), což umožní mít v jedné databázi více aplikací nebo oddělit produkční data. Tyto údaje se automaticky zapíší do `.env` na serveru a vygeneruje se nový `APP_KEY`.

**Proč jsou tyto údaje potřeba?**
Bez přístupu k SSH konzoli (Host, Port, Uživatel) by nebylo možné automaticky provádět operace jako `git pull`, `composer install` nebo `npm run build` přímo na serveru. Systém tyto údaje používá k tomu, aby se za vás „přihlásil“ a provedl všechny potřebné kroky jedním příkazem z vašeho počítače.

### Starší ruční nasazení (`app:deploy`)

Tento příkaz používejte jen při vědomé obnově, pokud není dostupné GitHub Actions. Běžné nasazení vždy spouští push do `main`.

```bash
php artisan app:deploy
```

Tento příkaz automaticky:
1. Ověří verzi zvolené PHP binárky na serveru (musí být 8.4.0+).
2. Provede `git fetch` a `git reset --hard` pro sledované soubory; zachová přitom produkční `.env`, uploady i jiné nesledované soubory.
3. Spustí `composer install` (s využitím zvolené PHP binárky, optimalizovaný pro produkci).
4. Provede `npm ci` a sestaví assety (`npm run build`).
5. Spustí pouze idempotentní migrace (`migrate --force`) a znovu vytvoří Laravel cache.

Pravidelné nasazení nikdy nespouští seedery, import statistik, financí nebo uživatelů, synchronizaci ikon, AI reindexaci ani `app:sync`.

### Synchronizace dat (Sync)
Příkaz `app:sync` je samostatná údržbová operace pro agregovanou synchronizaci dat. Není součástí nasazení a spouští se pouze vědomě při plánované správě dat.

```bash
php artisan app:sync [--freshseed] [--stats] [--usersync]
```

**Hlavní přepínače:**
- `--stats`: Synchronizuje soupisky, hráče, zápasy a statistiky z externích zdrojů (cz.basketball). **Automaticky se zapíná při použití `--freshseed`**, aby se po promazání tabulek data znovu načetla.
- `--freshseed`: Smaže data v definovaných tabulkách a nahraje je znovu ze seederů (včetně následné synchronizace statistik).
- `--usersync`: Synchronizuje uživatele a jejich avatary.
- `--force`: Vynutí synchronizaci i bez zjištěných změn.

---

### Starší nasazení přes FTP Sync

FTP postup je nouzová varianta pro obnovu jednotlivých souborů. Není vhodný pro běžné nasazení celé aplikace, protože nevytváří atomický release a může smíchat dvě verze kódu.

#### A) Kompletní synchronizace
1. **Lokálně** ve svém počítači připravte vše potřebné jedním příkazem:
   ```bash
   php artisan app:local:prepare
   ```
   *Tento příkaz provede `npm ci` a sestaví assety (Vite build); nemění ikony, cache ani data.*
2. Přes **FTP klienta** (např. FileZilla, WinSCP nebo IDE) nahrajte změněné soubory na server do **funkčního adresáře**. Nezapomeňte nahrát i složku `public/build/`.
3. Na serveru po nahrání spusťte pouze:
   ```bash
   php8.4 artisan migrate --force --no-interaction
   php8.4 artisan filament:optimize
   php8.4 artisan optimize --no-interaction
   ```

#### B) Pouze sestavení a nahrání assetů (Rychlá volba)

**Přes SSH (SCP):**
Pokud máte nastaven SSH přístup, použijte tento moderní a rychlý příkaz:
```bash
php artisan assets:deploy
```
Tento příkaz:
1. Lokálně spustí `npm run build`; ikony synchronizuje jen s výslovným přepínačem `--with-icons`.
2. Smaže staré assety na produkci, aby nedocházelo ke kolizím verzí.
3. Bezpečně nahraje složku `public/build` (a volitelně `public/assets` s přepínačem `--with-assets`) přes SCP. Příkaz je optimalizován pro velké objemy dat (vysoký timeout).
4. Automaticky vymaže cache pohledů a systémovou cache na produkci přes SSH.

**Přes FTP (Zastaralé):**
Pokud měníte pouze CSS/JS a nemůžete použít SCP, můžete použít:
```bash
php artisan app:deploy:local-assets
```
Tento příkaz:
1. Lokálně spustí `npm run build`.
2. Automaticky nahraje složku `public/build` na produkci přes FTP (vyžaduje `PROD_FTP_*` v `.env`).

Volitelně můžete nahrát i statické obrázky ze složky `public/assets`:
```bash
php artisan app:deploy:local-assets --with-assets
```

---

Tyto varianty jsou určené jen pro ruční obnovu. Automatický release z `main` rovněž nevyžaduje Git, Composer ani Node.js na produkčním serveru a navíc zachovává atomické přepnutí.

---

### Odolnost proti chybám a kompatibilita databáze (DŮLEŽITÉ)
Vzhledem k tomu, že hosting Webglobe využívá starší verze MySQL/MariaDB (bez podpory nativního typu `JSON` a bez sloupce `generation_expression` v `information_schema.columns`), je v projektu **zakázáno** používat v migracích následující metody Laravelu 12:
- `$table->json('column_name')` – místo toho používejte `$table->longText('column_name')`. Laravel automaticky zvládne přetypování v modelech (casts).
- `Schema::hasColumn` / `Schema::hasColumns`
- `->change()` (např. `$table->string('name')->nullable()->change()`)

Tyto metody vyžadují hloubkovou introspekci schématu, která na tomto hostingu selhává. Místo nich:

**Řešení introspekce schématu:**
Pokud se v aplikaci nebo při spouštění Artisan příkazů (např. `model:show`) objeví chyba `Unknown column 'generation_expression'`, je to způsobeno pokusem o moderní introspekci na starší databázi. 
Pro vyřešení tohoto problému je v `config/database.php` přidána podpora pro parametr `version`. V `.env` na produkci musí být nastaveno:
```env
DB_VERSION=5.7.0
```
Tím se Laravelu 12 sdělí, aby nepoužíval moderní sloupce v dotazech na `information_schema`.

1. **Přidávání sloupců:** Provádějte přímo v `Schema::table` bez předchozí kontroly existence sloupce.
2. **Změna typu sloupce:** Pokud je změna nezbytná, upravte původní `create` migraci (pokud ještě neproběhla na produkci) nebo použijte `DB::statement("ALTER TABLE ... MODIFY ...")`.

Všechny stávající migrace byly k 25. 2. 2026 upraveny tak, aby byly s tímto omezením kompatibilní. Pokud v budoucnu narazíte na chybu `SQLSTATE[42000]: Syntax error or access violation: 1064 ... for column ... json`, je to právě kvůli tomuto omezení.

Oba příkazy (`app:production:setup` a `app:deploy`) jsou vybaveny **automatickou detekcí verzí**.
- Pokud je v konfiguraci nastaveno obecné `node`, systém se při každém běhu pokusí na serveru najít verzi 20+ (např. `node22`, `node20` nebo `/usr/bin/node`).
- To řeší specifický problém hostingu Webglobe, kde v různých SSH session může být různé pořadí v `PATH` a výchozí `node` může být zastaralý (v14).
- Pokud systém automaticky najde lepší verzi, vypíše informaci `✅ Použiji: /cesta/k/binarce`.

### Autonomní a AI režim (--ai-test)
Pro účely automatizace (např. při opravách pomocí AI agenta nebo v CI/CD) podporují příkazy `app:deploy` a `app:sync` přepínač `--ai-test`.
- V tomto režimu se systém **nepotáže na hesla ani tokeny**, i kdyby chyběly nebo byly změněny.
- Použije výhradně hodnoty uložené v lokálním `.env`.
- Přeskakuje veškeré interaktivní dotazy (`select`, `password`, `confirm`), což zabraňuje zablokování terminálu v neinteraktivním prostředí.

Oba příkazy jsou také vybaveny **automatickým opakováním**. 
- Pokud selže SSH spojení během setupu, systém vám umožní upravit údaje nebo zkusit znovu nahrát SSH klíč (včetně nového dotazu na heslo).
- Veškeré zadané údaje o serveru se ukládají do `.env` ihned po potvrzení, takže i při přerušení setupu si je systém pro příště pamatuje.
- Pokud dojde k chybě během běhu (např. selže `composer install` nebo `npm run build`), systém se vás zeptá, zda chcete operaci zkusit znovu. To umožňuje opravit příčinu (např. chybějící balíček na serveru) a pokračovat bez nutnosti znovu zadávat všechny konfigurační údaje.

Navíc je bootstrap aplikace v `bootstrap/app.php` zabezpečen tak, aby selhání připojení k databázi (např. při prvotním nasazení před migracemi) nezpůsobilo pád `composer install` nebo jiných CLI příkazů. V `Envoy.blade.php` je také zajištěno, že `.env` soubor s produkčními údaji je vytvořen dříve, než se aplikace začne instalovat, což minimalizuje riziko nekonzistencí.

---

## Historický postup Rsync + SSH reset

Tento postup je archivován pouze pro dohledání starších zásahů. Na produkci se už nesmí používat: `git reset --hard` v původním adresáři může přepsat změny obnovené z FTP zálohy a přímá výměna souborů není atomická.

### 1. Fix ikon (Font Awesome 7 a Tailwind v4)
V tomto projektu byl zaveden soubor `resources/css/icons-fix.css`, který je v layoutu nalinkován samostatně. Slouží k vynucení `font-weight: 300` pro `.fa-light` a k zajištění viditelnosti ikon, protože Tailwind v4 v produkčním buildu tyto definice někdy agresivně optimalizuje.

### 2. Příprava lokálně (Váš počítač)
Ujistěte se, že jste na správné větvi, máte vše commitnuto a pushnuto.

```bash
# Sestavení assetů
npm run build

# Přenos assetů na produkci (včetně promazání starých)
# Synchronizujeme do funkčního adresáře (pro PHP) i do veřejného adresáře (pro Nginx)
# Nahraďte PORT, USER a CESTU svými údaji (viz .env)
rsync -avz --delete -e 'ssh -p 20001' public/build/ ssh-588875@dw191.webglobe.com:/home/html/kbelstisokoli.cz/public_html/secret/public/build/
rsync -avz --delete -e 'ssh -p 20001' public/build/ ssh-588875@dw191.webglobe.com:/home/html/kbelstisokoli.cz/public_html/www/build/
```

### 3. Aktualizace kódu na serveru (SSH)
Tento krok vynutí čistý stav aplikace podle repozitáře.

```bash
ssh -p 20001 ssh-588875@dw191.webglobe.com

# Přejít do funkčního adresáře
cd /home/html/kbelstisokoli.cz/public_html/secret

# Vynucení čistého stavu z Gitu
git fetch origin
git reset --hard origin/main  # Nahraďte vaší větví

# Optimalizace aplikace (používat vždy php8.4)
php8.4 artisan optimize:clear
php8.4 artisan optimize
```

---

## Předpoklady starších ručních postupů na serveru (Webglobe)
1. **PHP:** Verze 8.4+ (včetně JIT optimalizací).
2. **SSH Přístup:** Povoleno v administraci Webglobe (nutné pro příkazy `app:deploy` i `app:sync`).
3. **Git:** Musí být nainstalován (pro `app:deploy`).
4. **Composer:** Globálně dostupný (pro `app:deploy`).
5. **Node.js & NPM:** Pro buildování assetů přímo na serveru (pro `app:deploy`). **Při použití metody FTP Sync (bod 3) není na serveru potřeba.**

## Archivovaný manuální postup přes SSH

> Tento blok je historická reference. Nepoužívejte jej pro produkční nasazení; aktuální workflow přenáší hotový release a přepíná symlink `current`.

> Aktuální informace pro **upgrade na Laravel 13** naleznete v samostatném dokumentu: [02-upgrade-laravel-13.md](02-upgrade-laravel-13.md).

Pokud chcete nasadit novou verzi ručně, připojte se přes SSH a proveďte (postup je optimalizován pro **Fish shell**, který Webglobe používá):

```bash
cd /cesta/k/projektu
git fetch origin main
git reset --hard origin/main
git prune
php8.4 (which composer) install --no-interaction --optimize-autoloader --no-dev
php8.4 artisan migrate --force

# --- KRITICKÝ KROK: Zajištění správné verze Node.js (Vite vyžaduje 18+) ---
# Pokud používáte Fish shell, proveďte přesně tyto kroky k nalezení a použití správné verze:

# 1. Najděte dostupnou verzi Node.js (18 nebo vyšší)
# Zkuste: node -v, node20 -v, node18 -v atd.
# Pokud 'node -v' vypíše 18+, můžete použít přímo 'node'.
# Na Webglobe často existují binárky jako node20, node18, ale mohou být skryté (např. v /opt/alt/node*/usr/bin/).
# POZOR: Někdy je 'node' v /usr/local/bin/ zastaralý (v14), zatímco v /usr/bin/ je v18+.

# Tip pro důkladné vyhledání všech dostupných Node binárek (mimo naši složku .node_bin):
# which -a node20 node18 node | grep -v ".node_bin"

# Tip pro důkladné vyhledání všech dostupných NPM binárek:
# for n in (which -a npm22 npm20 npm18 npm | grep -v ".node_bin"; or ls /opt/alt/node*/usr/bin/npm 2>/dev/null); if test -x $n; echo -n "$n: "; $n -v; end; end

# Příklad pro automatické nalezení a uložení cesty k binárce (preferuje v18+):
set -l NODE_BIN ""
for n in (which -a node20 node18 node | grep -v ".node_bin"; or ls /opt/alt/node*/usr/bin/node)
    if $n -v | string match -rq '^v(18|2[0-9])'
        set NODE_BIN $n
        break
    end
end

if test -z "$NODE_BIN"
    set NODE_BIN (which -a node | grep -v ".node_bin" | head -n1; or which /opt/alt/node*/usr/bin/node | head -n1)
end

# 2. Vytvořte lokální binářky (symlinky) pro konzistenci
mkdir -p .node_bin
# Použijeme realpath, aby symlink mířil na skutečný soubor, ne na jiný symlink
ln -sf (realpath $NODE_BIN) .node_bin/node

# Podobně pro NPM (pokud existuje npm20/npm18, jinak zkusit odvodit od cesty k Node)
set -l NPM_BIN (which -a npm20 npm18 npm | grep -v ".node_bin" | head -n1; or which (string replace "node" "npm" $NODE_BIN))
ln -sf (realpath $NPM_BIN) .node_bin/npm

# 3. Přidejte do PATH (absolutní cestou)
set -gx PATH (realpath .node_bin) $PATH

# 4. OVĚŘENÍ: Musí vypsat verzi v18.x.x+ a cestu k vašemu .node_bin/node
node -v
which node

# Nyní již můžete bezpečně sestavit assety
npm ci
npm run build
# ------------------------------------------------------------------------

php8.4 artisan optimize
```

### Řešení potíží (Troubleshooting)
**Chyba: `Too many levels of symbolic links`**
Tato chyba znamená, že se symlinky v `.node_bin` zacyklily (např. `node` ukazuje na `node`). To se může stát, pokud příkazy pro nastavení PATH spustíte opakovaně v téže session a nepoužijete filtrující `grep`.
- Smažte složku `.node_bin` a začněte znovu: `rm -rf .node_bin`.
- Ujistěte se, že při hledání binárek používáte `grep -v ".node_bin"`.

**Chyba: `SyntaxError: Unexpected token '??='`**
Tato chyba znamená, že se k sestavení assetů (Vite) používá příliš stará verze Node.js. Webglobe má jako výchozí `node` verzi 12 nebo 14, ale moderní nástroje vyžadují 18+.
- Ujistěte se, že jste provedli kroky v sekci „KRITICKÝ KROK“ výše.
- Zkontrolujte výstup `node -v`. Pokud vypíše cokoliv nižšího než 18, PATH není správně nastaven.
- V krajním případě zkuste spustit build přímo pomocí konkrétní verze, např.: `node20 (which npm) run build`.

> Tip: Po buildu udělejte „tvrdý refresh“ v prohlížeči (Cmd/Ctrl + Shift + R). V případě potřeby pročistěte cache pohledů: `php artisan view:clear` a `php artisan filament:clear-cached-components`.


## GitHub Workflow
Na GitHub se nahrává pouze zdrojový kód. Soubory jako `.env`, `vendor/`, `node_modules/` a buildované soubory v `public/build/` jsou ignorovány (dle standardu).

Workflow `.github/workflows/lint.yml` při pull requestu a pushi do `main` spustí Pint a celou PHP testovací sadu. Po úspěšném pushi do `main` následně nasadí přes SSH přesně ověřený commit do GitHub Environment `production`. Soubory `.env` na produkci nemění; její konfigurace zůstává spravovaná mimo CI.

Před prvním automatickým nasazením uložte v Environment `production` tyto Secrets:

- `PRODUCTION_SSH_HOST`
- `PRODUCTION_SSH_PORT`
- `PRODUCTION_SSH_USER`
- `PRODUCTION_PATH`
- `PRODUCTION_PUBLIC_PATH`
- `PRODUCTION_SSH_PRIVATE_KEY`
- `PRODUCTION_SSH_KNOWN_HOSTS` (výstup `ssh-keyscan` pro produkční hostitel)
- `FONTAWESOME_TOKEN`

Pokud některý secret chybí, push workflow skončí chybou ještě před instalací závislostí. Po jejich doplnění provede push do `main` testy, jediný produkční build, přenos hotového release, migrace a atomické přepnutí verze.
