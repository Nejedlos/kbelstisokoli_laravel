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

V tomto projektu jsou plánované úlohy spravovány dvěma způsoby:

### 3.1 Dynamické úlohy (Administrace / Batch příkazy)

Většina úloh je definována v databázi v tabulce `cron_tasks`. Tyto úlohy lze spravovat přímo v administraci v sekci **Systém -> Cron Úlohy**.

U každé úlohy lze nastavit:
- **Aktivitu**: Zda se má úloha spouštět.
- **Cron výraz**: Kdy se má úloha spouštět (např. `0 3 * * *`).
- **Příkaz**: Artisan příkaz, který se má provést.

Mezi tyto úlohy patří:
- **Synchronizace statistik**: Globální i pro konkrétní týmy.
- **Docházka Upomínky**: Rozesílání notifikací členům.
- **Finance**: Kontrola splatnosti předpisů.
- **Systémový úklid**: Promazávání starých logů a cache.

### 3.2 Statické úlohy (Kód)

Některé kritické systémové úlohy jsou definovány přímo v kódu v `bootstrap/app.php` nebo `routes/console.php`:
- **Scheduler Heartbeat**: Každou minutu (pro monitoring běhu plánovače a debug panelu).

## 4. Monitoring a Logování

Každý běh úlohy z administrace (Dynamické úlohy) je logován do tabulky `cron_logs`. V administraci je u každé úlohy vidět:
- **Poslední běh**: Čas a výsledek (Success/Failed).
- **Výstup (Output)**: Kompletní výstup z konzole pro diagnostiku.
- **Doba trvání**: Jak dlouho úloha běžela.
