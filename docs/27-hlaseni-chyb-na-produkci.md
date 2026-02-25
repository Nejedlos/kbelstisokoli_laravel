# Hlášení chyb na produkci (Application + Pre-boot)

Tento dokument popisuje, jak je v projektu nastaveno automatické hlášení selhání e‑mailem jak v rámci Laravel aplikace (během requestu), tak **před načtením Laravelu** (pre‑boot), aby byly zachyceny i fatální chyby při startu.

## 1. Příjemce a SMTP (Webglobe)

E‑maily se odesílají přes SMTP server Webglobe s identitou `mailer@kbelstisokoli.cz`.

Konfigurace v `.env` (viz i `/.env.example`):

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.kbelstisokoli.cz
MAIL_PORT=465
MAIL_USERNAME=mailer@kbelstisokoli.cz
MAIL_PASSWORD=waT6C9a6
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=mailer@kbelstisokoli.cz
MAIL_FROM_NAME="Kbelští sokoli"

# Kam posílat reporty chyb
ERROR_REPORT_EMAIL=mailer@kbelstisokoli.cz
# Volitelné: adresa odesílatele pro reporty (pokud se liší od MAIL_FROM_ADDRESS)
ERROR_REPORT_SENDER=mailer@kbelstisokoli.cz
```

## 2. Hlášení chyb v aplikaci (Laravel Exception Handler)

Implementace se nachází v `bootstrap/app.php` v sekci `withExceptions()` – registruje se report callback, který na **produkci** odešle e‑mail pro chyby 5xx a ostatní neošetřené výjimky. Zpráva obsahuje:

- Základní info o aplikaci (name, env, url)
- Výjimku (class, message, code, file, line, zkrácený trace)
- Kontext requestu (URL, metoda, IP, query, input – citlivé klíče zamaskovány)
- Hlavičky requestu
- Info o serveru (PHP verze, SAPI, paměť)
- Přihlášený uživatel (id, email, name)

Šablona e‑mailu je `resources/views/emails/error-report.blade.php` a zpracovává data předaná do `App\Mail\ErrorMail`.

## 3. Hlášení chyb před bootem Laravelu (pre‑boot)

V `public/index.php` je zavedeno:
- Načtení `.env` přes `Dotenv::safeLoad()` už před bootem.
- Vlastní `set_exception_handler()` pro zachycení výjimek v pre‑boot fázi.
- `register_shutdown_function()` pro zachycení fatálních chyb (E_ERROR, E_PARSE, …).
- Odeslání e‑mailu přes `symfony/mailer` na základě SMTP údajů z `.env`.

Pre‑boot report neodesílá kompletní superglobály, ale pouze bezpečné minimum ze serverových proměnných (metoda, URI, host, IP, UA), aby nedošlo k úniku citlivých dat.

## 4. Ověření funkčnosti

- Lokálně můžete vyvolat chybu v jakékoliv kontrolerové akci (např. `throw new \RuntimeException('Test exception');`) a zkontrolovat doručení e‑mailu do schránky uvedené v `ERROR_REPORT_EMAIL`.
- Pro pre‑boot test dočasně vložte do `public/index.php` před `require bootstrap/app.php` řádek `throw new \RuntimeException('Pre-boot test');` a ověřte doručení reportu. Poté řádek ihned odstraňte.

Pozn.: Na produkci musí být povolené odchozí SMTP spojení a správně vyplněné hodnoty v `.env`.

## 5. Bezpečnostní poznámky

- Pole `password`, `password_confirmation`, `current_password`, `_token`, `token` jsou v reportu **maskována**.
- Pre‑boot handler odesílá pouze omezené serverové údaje.
- Při selhání odesílání reportu se chyba **nepropadá** do uživatele – jen se zapíše do `error_log`.
