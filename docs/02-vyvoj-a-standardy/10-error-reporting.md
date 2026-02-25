# Chybové reporty a 500 stránka (produkce)

Tento dokument popisuje, jak v projektu funguje odesílání chybových reportů e‑mailem a jak vypadá produkční 500 stránka s kopírovatelnými debug informacemi.

## Co bylo zavedeno / změněno

- Oprava SMTP konfigurace pro odesílání e‑mailů (viz `config/mail.php`):
  - Namísto klíče `scheme` se nyní používá standardní `encryption` s vazbou na `MAIL_ENCRYPTION` v `.env`.
- Robustnější reportování chyb v `bootstrap/app.php`:
  - Při chybě 500+ na produkci se generuje sanitizovaný JSON report (skryje hesla, tokeny), který se odesílá e‑mailem na `ERROR_REPORT_EMAIL`.
  - Pokud odeslání e‑mailu selže, incident se zaloguje do Laravel logu (`log` channel).
- Vlastní produkční 500 stránka `resources/views/errors/500.blade.php`:
  - Používá branding barvy a basketbalovou terminologii ("Míč skončil v autu").
  - Technické detaily jsou pro běžného uživatele skryty za tlačítkem "Technická zpráva pro admina".
  - Obsahuje kompletní JSON report a volitelný stack trace.
  - Tlačítko „Zkopírovat kompletní zprávu“ uloží sanitizovaný JSON do schránky pro případnou ruční diagnostiku.

## Diagnostika a testování (Admin)

V administraci byla přidána stránka **Debugování e-mailů** (v sekci Admin nástroje), která umožňuje:
- Kontrolu aktuálně načtené SMTP konfigurace (hostitel, port, šifrování).
- Odeslání zkušebního e-mailu na libovolnou adresu.
- Ruční vyvolání simulovaného Error Reportu (500) pro ověření automatické notifikace.
- Zobrazení posledních záznamů z `laravel.log` týkajících se e-mailů.

## Požadavky na prostředí (.env)

Ujistěte se, že na produkci máte nastaveno (příklad pro Webglobe):

```
MAIL_MAILER=smtp
MAIL_HOST=mail.webglobe.cz
MAIL_PORT=465            # SSL (doporučeno) nebo 587 (TLS)
MAIL_USERNAME=...        # celá e-mailová adresa
MAIL_PASSWORD=...
MAIL_ENCRYPTION=ssl      # pro 465 použijte ssl, pro 587 tls
MAIL_FROM_ADDRESS=mailer@kbelstisokoli.cz
MAIL_FROM_NAME="Kbelští sokoli"

ERROR_REPORT_EMAIL=vas@e-mail.cz
# OPTIONAL: ERROR_REPORT_SENDER=mailer@kbelstisokoli.cz
```

Po nasazení změn nezapomeňte:

```
php artisan config:clear
php artisan cache:clear
```

(na Webglobe dle Envoy/SSH postupu)

## Jak otestovat

1. Na produkci vyvolejte řízenou chybu (např. dočasnou trasu, nebo navštivte URL, které 500 způsobuje).
2. Zkontrolujte, že dorazil e‑mail s předmětem `"[APP][env] ExceptionClass (file:line)"`.
3. Na chybové stránce klikněte na „Zkopírovat technickou zprávu“ a vložte do chatu pro rychlou diagnostiku.

## Poznámky k bezpečnosti

- Zobrazené debug informace jsou sanitizované – běžná citlivá pole (hesla, tokeny) jsou nahrazena hodnotou `[hidden]`.
- Stack trace je schovaný za tlačítkem a neměl by obsahovat tajné hodnoty, přesto jej sdílejte pouze s administrátory/vývojáři.
