# Debugování a testování e-mailů

Tato stránka v administraci slouží k technické verifikaci e-mailového nastavení (SMTP) a sledování logů spojených s odesíláním.

## Funkce panelu

### 1. Konfigurace serveru
Zobrazuje aktuální nastavení e-mailového odesílatele načtené z `.env` souboru (nebo produkční konfigurace).
- **Maskování:** Hesla jsou pro bezpečnost částečně maskována.
- **Git Info:** Panel zobrazuje aktuální commit a datum poslední změny kódu pro snadnější identifikaci verze.

### 2. Odeslání testovacího e-mailu
Nástroj pro okamžité odeslání zkušební zprávy pro ověření funkčnosti SMTP.
- **Výběr uživatele:** Nad polem příjemce se nachází vyhledávací pole pro výběr uživatele ze systému. Po výběru se jeho e-mail automaticky doplní do pole příjemce.
- **Ruční úprava:** E-mail příjemce lze po automatickém doplnění libovolně změnit.
- **Zpráva:** Lze definovat vlastní text zkušebního e-mailu.

### 3. Simulace Error Reportu
Speciální akce, která nasimuluje pád aplikace (Exception) a odeslání technického reportu na adresu definovanou v `TECHNICAL_CONTACT_EMAIL`. Slouží k ověření, že systém v případě chyby na produkci dokáže informovat vývojáře.

### 4. Sledování logů
V dolní části stránky se zobrazuje posledních 20 řádků ze souboru `laravel.log`, které obsahují klíčová slova "mail" nebo "error". To umožňuje rychlou diagnostiku selhání bez nutnosti přístupu k serveru přes SSH.

## Související soubory
- **Třída:** `App\Filament\Pages\EmailDebug`
- **Šablona:** `resources/views/filament/pages/email-debug.blade.php`
- **Logy:** `storage/logs/laravel.log`
