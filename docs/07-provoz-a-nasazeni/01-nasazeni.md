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
- **PHP binárka:** *Vylepšeno:* Systém se nejprve připojí k serveru a automaticky se pokusí najít nejvhodnější verzi PHP (8.4+). Tuto verzi vám pak nabídne jako výchozí hodnotu, kterou stačí potvrdit.
- **Node.js binárka:** *Vylepšeno:* Systém se nejprve připojí k serveru a automaticky se pokusí najít nejvhodnější verzi Node.js (18.0+), která je potřeba pro Vite 6 a Tailwind v4. Na Webglobe typicky najde `node20`, `node18` nebo použije výchozí `node`.
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
5. Spustí migrace databáze (`migrate --force`) a automaticky provede seedování (`app:seed --force`).
6. Nainstaluje NPM balíčky a sestaví assety (`npm run build`).
7. Synchronizuje ikony a optimalizuje cache aplikace.

### 3. Nasazení přes FTP Sync (Alternativa)
Tento režim je ideální, pokud se chcete **vyhnout instalaci Node.js/NPM na serveru** (např. při potížích s verzemi) nebo pokud preferujete nahrávání souborů přes FTP.

1. **Lokálně** ve svém počítači připravte vše potřebné jedním příkazem:
   ```bash
   php artisan app:local:prepare
   ```
   *Tento příkaz automaticky nainstaluje NPM balíčky, sestaví assety (Vite build), synchronizuje ikony a pročistí lokální cache.*
2. Přes **FTP klienta** (např. FileZilla, WinSCP nebo IDE) nahrajte změněné soubory na server do **funkčního adresáře**. Nezapomeňte nahrát i složku `public/build/`.
3. Následně ve svém počítači spusťte příkaz:
   ```bash
   php artisan app:sync
   ```

Tento příkaz na serveru automaticky:
1. Ověří verzi PHP na serveru.
2. Vytvoří nebo aktualizuje `.env` soubor podle vašeho lokálního nastavení.
3. Synchronizuje obsah složky `public` do veřejného adresáře a opraví cesty v `index.php`.
4. Spustí migrace databáze (`migrate --force`) a automaticky provede seedování (`app:seed --force`).
5. Provede synchronizaci ikon a optimalizaci mezipaměti (`optimize`).

Na konci příkazu se zobrazí přehledný souhrn všech provedených kroků s potvrzením úspěšnosti.

Je to **nejjednodušší a nejrobustnější cesta**, pokud nechcete na serveru řešit Git, NPM nebo verze Node.js.

---

### Odolnost proti chybám a kompatibilita databáze (DŮLEŽITÉ)
Vzhledem k tomu, že hosting Webglobe využívá starší verze MySQL/MariaDB (bez podpory nativního typu `JSON` a bez sloupce `generation_expression` v `information_schema.columns`), je v projektu **zakázáno** používat v migracích následující metody Laravelu 12:
- `$table->json('column_name')` – místo toho používejte `$table->longText('column_name')`. Laravel automaticky zvládne přetypování v modelech (casts).
- `Schema::hasColumn` / `Schema::hasColumns`
- `->change()` (např. `$table->string('name')->nullable()->change()`)

Tyto metody vyžadují hloubkovou introspekci schématu, která na tomto hostingu selhává. Místo nich:
1. **Přidávání sloupců:** Provádějte přímo v `Schema::table` bez předchozí kontroly existence sloupce.
2. **Změna typu sloupce:** Pokud je změna nezbytná, upravte původní `create` migraci (pokud ještě neproběhla na produkci) nebo použijte `DB::statement("ALTER TABLE ... MODIFY ...")`.

Všechny stávající migrace byly k 25. 2. 2026 upraveny tak, aby byly s tímto omezením kompatibilní. Pokud v budoucnu narazíte na chybu `SQLSTATE[42000]: Syntax error or access violation: 1064 ... for column ... json`, je to právě kvůli tomuto omezení.

Oba příkazy (`app:production:setup`, `app:deploy` i `app:sync`) jsou vybaveny **automatickou detekcí verzí**.
- Pokud je v konfiguraci nastaveno obecné `node`, systém se při každém běhu pokusí na serveru najít verzi 18+ (např. `node20`, `node18` nebo `/usr/bin/node`).
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

## Předpoklady na serveru (Webglobe)
1. **PHP:** Verze 8.4+ (včetně JIT optimalizací).
2. **SSH Přístup:** Povoleno v administraci Webglobe (nutné pro příkazy `app:deploy` i `app:sync`).
3. **Git:** Musí být nainstalován (pro `app:deploy`).
4. **Composer:** Globálně dostupný (pro `app:deploy`).
5. **Node.js & NPM:** Pro buildování assetů přímo na serveru (pro `app:deploy`). **Při použití metody FTP Sync (bod 3) není na serveru potřeba.**

## Postup nasazení (Manuální přes SSH - příklad pro PHP 8.4)
Pokud chcete nasadit novou verzi ručně, připojte se přes SSH a proveďte (postup je optimalizován pro **Fish shell**, který Webglobe používá):

```bash
cd /cesta/k/projektu
git fetch origin main
git reset --hard origin/main
git clean -df
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
npm install
npm run build
# ------------------------------------------------------------------------

php8.4 artisan app:icons:sync
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
Všechny instalace a buildy probíhají až na produkčním serveru, což zaručuje, že prostředí je konzistentní.

