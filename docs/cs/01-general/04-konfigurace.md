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

## Konfigurace na hostingu (Webglobe)

Vzhledem ke specifické struktuře hostingu Webglobe (kde projektový root a veřejná složka mohou být v různých adresářích), Laravel v tomto projektu používá konfiguraci umístěnou v `public/.env`.

- **Kořenový `.env`**: Slouží pro lokální vývoj a uchování "master" konfigurace (zejména `PROD_` proměnných). Je v `.gitignore`.
- **Veřejný `public/.env`**: Je primárním zdrojem konfigurace pro běžící aplikaci (web i Artisan). Je také v `.gitignore`.
- **Automatická synchronizace**: Příkaz `php artisan app:sync` automaticky inicializuje nebo aktualizuje `public/.env` kombinací šablony `.env.example` a hodnot (zejména tajných klíčů a produkčních přístupů) z kořenového `.env`.

### Důležité produkční proměnné (`PROD_`)
Tyto proměnné v kořenovém `.env` definují, kam a jak se aplikace synchronizuje:
- `PROD_HOST`, `PROD_PORT`, `PROD_USER`: SSH přístup.
- `PROD_PATH`: Cesta k projektu na serveru.
- `PROD_DB_*`: Přihlašovací údaje k produkční databázi (používají se pro vzdálené migrace).
- `PROD_GIT_TOKEN`: Token pro přístup k repozitáři z produkčního serveru.

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
