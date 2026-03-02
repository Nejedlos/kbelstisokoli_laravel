# Throttling a deduplikace chybových e-mailů

Tento dokument popisuje mechanismus pro omezení (throttling) a deduplikaci chybových e-mailů odesílaných z produkčního prostředí.

## Účel
Zabránit zahlcení e-mailové schránky v případě, že se na produkci vyskytne opakující se chyba v krátkém časovém intervalu (např. chyba v cyklu nebo při vysoké návštěvnosti).

## Jak to funguje
1. **Fingerprint:** Pro každou nahlášenou chybu se vygeneruje unikátní "otisk" (fingerprint) založený na:
   - Třídě výjimky (`Exception class`)
   - Normalizované zprávě (očištěné o dynamické prvky jako ID, UUID)
   - Souboru a řádku, kde chyba vznikla
   - URL adrese požadavku
2. **Cache:** Fingerprint se uloží do cache s nastavenou dobou platnosti (TTL).
3. **Potlačení:** Pokud je stejný fingerprint v cache nalezen, další e-mail se pro tuto chybu již neodesílá.
4. **Logování:** Potlačení e-mailu je zaznamenáno v systémovém logu jako `Error mail suppressed (deduped)`. Toto logování je samo o sobě limitováno na jednou za 60 sekund pro daný fingerprint, aby se předešlo zahlcení logů.

## Konfigurace (.env)

Následující proměnné v `.env` souboru umožňují jemné nastavení chování:

| Proměnná | Výchozí hodnota | Popis |
| :--- | :--- | :--- |
| `ERROR_REPORT_EMAIL` | `null` | Adresa, na kterou se posílají reporty. Pokud je prázdná, e-maily se neposílají. |
| `ERROR_REPORT_ENVIRONMENTS` | `production` | Čárkou oddělený seznam prostředí, kde je hlášení chyb aktivní. |
| `ERROR_MAIL_DEDUP_ENABLED` | `true` | Zapíná/vypíná mechanismus deduplikace. |
| `ERROR_MAIL_DEDUP_TTL_SECONDS` | `900` | Doba (v sekundách), po kterou je stejná chyba potlačena (výchozí 15 min). |
| `ERROR_MAIL_DEDUP_ENVIRONMENTS` | `production,staging` | Prostředí, ve kterých se má deduplikace aplikovat. |
| `ERROR_MAIL_ALWAYS_SEND` | `false` | Pokud je `true`, v `local` prostředí se e-maily posílají vždy (ignoruje TTL). |

### Příklad pro Produkci
```env
ERROR_REPORT_EMAIL=admin@kbelstisokoli.cz
ERROR_REPORT_ENVIRONMENTS=production
ERROR_MAIL_DEDUP_ENABLED=true
ERROR_MAIL_DEDUP_TTL_SECONDS=900
```

### Příklad pro Vývoj (Debugování produkční chyby)
Pokud potřebujete na produkci dočasně vidět každý výskyt chyby:
```env
ERROR_MAIL_DEDUP_ENABLED=false
```

## Technické detaily
- **Implementace:** `App\Support\ErrorMailThrottle`
- **Integrace:** `bootstrap/app.php` v sekci `$exceptions->report()`
- **Úložiště:** Používá výchozí Laravel `Cache` (na produkci doporučen Redis nebo DB, nikoliv `file` pokud je více workerů).

## Omezení a rizika
- **Změna fingerprintu:** Pokud se v chybové zprávě vyskytují unikátní prvky (např. časové razítko), které nejsou v normalizaci podchyceny, může dojít k selhání deduplikace.
- **Dostupnost Cache:** Pokud selže Cache, deduplikace nebude fungovat a e-maily se budou posílat při každém výskytu. Samotné odesílání chyb v `bootstrap/app.php` je však obaleno v `try-catch`, takže by to nemělo shodit celou aplikaci.
