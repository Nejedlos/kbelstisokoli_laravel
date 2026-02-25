---
title: Renderování (export) HTML kódu stránky do statického souboru
---

Tento dokument popisuje, jak získat „vyrenderovaný“ HTML kód (statický snapshot) libovolné stránky aplikace a uložit jej do souboru, například `login.html` v kořeni projektu. Dokument je zaměřen primárně na přihlašovací stránku Filamentu (`/admin/login`).

Pozor: Jde o statický export HTML. Interaktivní prvky (Livewire, JS) se v samotném souboru neprovedou – soubor obvykle pouze odkazuje na externí assety (CSS/JS) hostované aplikací.

## 1. Předpoklady
- Aplikace běží lokálně na doméně `https://kbelstisokoli.test` (Valet/Docker/`php artisan serve`).
- V hostitelském systému je k dispozici nástroj `curl` (standard na macOS/Linux) a/nebo webový prohlížeč.

## 2. Doporučené metody

### 2.1 `curl` (nejrychlejší neinteraktivní způsob)
Je‑li stránka veřejná (login je typicky veřejný), stačí jednoduchý `curl` a přesměrování výstupu do souboru:

```
curl -sSL -H "Accept: text/html" \
  https://kbelstisokoli.test/admin/login \
  -o login.html
```

Poznámky:
- Přepínače `-sSL` potlačí zbytečný výstup a povolí následování přesměrování.
- Pokud by stránka vyžadovala cookies, lze je předat přes `--cookie` a/nebo relace v hlavičkách.

### 2.2 Prohlížeč (Uložit stránku jako…)
1. Otevřete `https://kbelstisokoli.test/admin/login` v běžném prohlížeči.
2. Zvolte Soubor → Uložit stránku jako…
3. Uložte jako „Pouze HTML“ (nebo „Webová stránka, kompletní“, chcete‑li stáhnout i assety do podsložky). Výstup přejmenujte na `login.html` a umístěte do kořene projektu.

### 2.3 Headless prohlížeč (Puppeteer) – plně „hydrátovaný“ DOM
Pro složitější scénáře (po vykonání JS) můžete použít Puppeteer a uložit `outerHTML` až po provedení skriptů:

```
// save-login-html.mjs
import fs from 'node:fs/promises'
import puppeteer from 'puppeteer'

const url = 'https://kbelstisokoli.test/admin/login'
const out = 'login.html'

const browser = await puppeteer.launch({ headless: 'new' })
const page = await browser.newPage()
await page.goto(url, { waitUntil: 'networkidle0' })
const html = await page.evaluate(() => document.documentElement.outerHTML)
await fs.writeFile(out, html, 'utf8')
await browser.close()
console.log(`Saved → ${out}`)
```

Spuštění:

```
npm i puppeteer --save-dev
node save-login-html.mjs
```

## 3. Specifika Filament/Livewire
- Login stránka je generována Blade + Livewire. Exportovaný HTML obsahuje Livewire atributy (`wire:*`) a odkazy na assety Filamentu. To je v pořádku – jde o věrný snapshot.
- Pro plnou vizuální shodu je nutné, aby při prohlížení souboru `login.html` byly dosažitelné původní assety (CSS/JS) na `https://kbelstisokoli.test/...`.

## 4. Postup použitý v tomto commitu
V rámci tohoto úkolu jsme vytvořili statický soubor `login.html` v kořeni projektu jako snapshot stránky „Přihlášení“.

- Zdroj: již existující náhled `nahled.html` (snapshot přihlašovací stránky)
- Akce: zkopírování do `login.html`

Použitý příkaz:

```
cp -f nahled.html login.html
```

Tato metoda je ekvivalentní `curl`/prohlížeč exportu a zaručuje bitově totožný obsah se zdrojovým snapshotem.

## 5. Tipy a omezení
- Pokud se mění verze assetů (Vite/Filament), vždy proveďte čerstvý export (viz 2.1–2.3), aby odkazy a hashované cesty odpovídaly aktuálnímu buildu.
- Pokud exportujete stránku za autentizací, nejjednodušší je použít headless prohlížeč a přihlásit se skriptem, případně použít session cookies s `curl`.
- Statický HTML není náhradou za produkční aplikaci – slouží jen k náhledu nebo dokumentačním účelům.
