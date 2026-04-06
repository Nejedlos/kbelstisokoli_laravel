# CzBasketball Fetcher

Tento dokument popisuje implementaci `StatFetcherInterface` pro web `cz.basketball`.

## 1. Technická specifikace

- **Třída:** `App\Services\Stats\Fetchers\CzBasketballFetcher`
- **Rozhraní:** `App\Services\Stats\Contracts\StatFetcherInterface`
- **Závislosti:** Laravel `Http` klient (Guzzle), `Storage`, `Log`.

## 2. Robustnost a HTTP konfigurace

Fetcher je navržen tak, aby spolehlivě stahoval data i při občasných výpadcích nebo pokusech o blokování botů:

- **User-Agent:** Nastaven na moderní prohlížeč (Chrome/Mac), aby se předešlo blokování na základě hlaviček.
- **Timeout:** 90 sekund (celkový) a 30 sekund (připojení) pro zvládnutí pomalejších odpovědí ze serveru asociace nebo problémů se SSL handshakem.
- **Retries:** 3 pokusy o stažení při selhání (např. timeout nebo 5xx error).
- **Backoff:** Využívá exponenciální backoff (3s, 6s, 12s) mezi pokusy, aby nedošlo k přetížení cílového serveru.
- **SSL Robustnost:** Využívá `CURLOPT_SSL_SESSIONID_CACHE => false` pro prevenci SSL timeoutů na některých hostinzích (např. Webglobe).
- **Redirects:** Automaticky následuje přesměrování (včetně subdomén jako `smo.cz.basketball`).

## 3. Snapshoty a Audit

Pro účely ladění a historické evidence se každý úspěšný (i neúspěšný) pokus o stažení ukládá jako snapshot:

- **Cesta:** `storage/app/external/czbasketball/{season}/{run_type}/{id}-{timestamp}.html`
- **Metadata:** Cesta k souboru, HTTP status a finální URL (po redirectech) se ukládají do tabulky `external_import_runs`.
- **Kontext:** Pro aktivaci snapshotů je nutné fetcheru předat aktuální běh pomocí `$fetcher->setCurrentRun($run)`.

## 4. Kódování (Encoding)

Fetcher automaticky detekuje kódování z hlavičky `Content-Type`. Pokud narazí na `windows-1250` (časté u starších českých systémů), automaticky obsah převede do `UTF-8`.

## 5. Lokální testování

Fetcher lze testovat pomocí jednoduchého skriptu nebo v Tinkeru:

```php
$fetcher = app(App\Services\Stats\Contracts\StatFetcherInterface::class);
$html = $fetcher->fetch('https://cz.basketball/tym/7738?y=2025');
echo strlen($html); // Ověření, že data byla stažena
```

Při testování v rámci importní pipeline:

```php
$run = App\Models\ExternalImportRun::create([...]);
$fetcher->setCurrentRun($run);
$html = $fetcher->fetch($url);
// Následně zkontrolujte storage/app/external/czbasketball/...
```

## 6. Bezpečnost

- Fetcher provádí pouze server-side download HTML obsahu.
- **Nepoužívá** headless prohlížeč (Puppeteer/Playwright), což snižuje nároky na zdroje a minimalizuje riziko spouštění škodlivého JS.
- URL jsou validovány standardním Laravel `Http` klientem.
