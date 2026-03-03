# Plánované úlohy (Cron)

Tento dokument popisuje konfiguraci a spouštění plánovaných úloh v projektu Kbelští sokoli.

## 1. Konfigurace

Vzhledem k omezením některých hostingových prostředí, kde není možné nastavit standardní Laravel Cron (`* * * * * php /cesta/k/projektu/artisan schedule:run >> /dev/null 2>&1`), je v projektu implementována možnost spouštění přes HTTP endpoint.

### 1.1 Nastavení tokenu

Pro zabezpečení endpointu je nutné v souboru `.env` nastavit unikátní token:

```env
SCHEDULE_TOKEN=vas_tajny_hash_zde
```

Doporučujeme vygenerovat náhodný řetězec, například pomocí:
`openssl rand -hex 16`

## 2. Způsoby spouštění

### 2.1 HTTP Endpoint (Doporučeno pro Webglobe)

Pokud hosting umožňuje nastavit Cron jako volání URL (HTTP GET), nastavte jej na následující adresu:

`https://new.kbelstisokoli.cz/system/schedule/{SCHEDULE_TOKEN}`

Interval spouštění nastavte na **každou minutu** (`* * * * *`).

### 2.2 Standardní CLI Cron

Pokud máte přístup k SSH a standardnímu Cronu, použijte klasický příkaz:

```bash
* * * * * php /home/html/kbelstisokoli.cz/public_html/secret/artisan schedule:run >> /dev/null 2>&1
```

## 3. Definice úloh

Plánované úlohy jsou definovány v souboru `routes/console.php`.

Aktuálně nastavené úlohy:
- `seo:generate-sitemap` – Spouští se denně v 03:00.

## 4. Monitoring a Logování

Výstup z HTTP endpointu zobrazuje výsledek posledního běhu plánovače. Pokud nebyla naplánována žádná úloha pro daný čas, výstup bude prázdný (nebo s potvrzením o spuštění).

V případě chyb hledejte záznamy v `storage/logs/laravel.log`.
