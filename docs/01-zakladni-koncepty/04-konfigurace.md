# Konfigurace prostředí (.env)

Projekt využívá standardní Laravel `.env` soubor s několika specifickými rozšířeními pro potřeby hostingu Webglobe a integrace s AI.

## Základní nastavení
- `APP_NAME`: Název aplikace ("Kbelští sokoli").
- `APP_ENV`: Prostředí (`local`, `production`).
- `APP_DEBUG`: Ladící režim (`true` pro lokál).
- `APP_LOCALE`: Výchozí jazyk (`cs`).

## Databáze
Pro lokální vývoj je přednastaven MySQL na `127.0.0.1`.
- `DB_DATABASE`: `kbelstisokoli`

## Vlastní cesty a disky
Tyto proměnné jsou důležité pro správné fungování nahrávání souborů a přístupových práv na serveru:
- `PUBLIC_FOLDER`: Relativní nebo absolutní cesta k veřejné složce (obvykle `public`).
- `STORAGE_PATH`: Cesta k úložišti (obvykle `storage`).
- `UPLOADS_DISK`: Disk pro nahrávání (např. `public`).
- `UPLOADS_DIR`: Složka pro nahrávané soubory (např. `uploads`).

## Integrace AI (OpenAI)
Projekt je připraven na integraci s OpenAI pomocí následujících klíčů:
- `OPENAI_API_KEY`: API klíč pro OpenAI.
- `OPENAI_DEFAULT_MODEL`: Výchozí model (např. `gpt-4o-mini`).
- `OPENAI_ANALYZE_MODEL`: Model pro analýzy (např. `gpt-4o`).

## Odesílání chyb
- `ERROR_REPORT_EMAIL`: Emailová adresa, na kterou se odesílají kritické chyby z produkce.
