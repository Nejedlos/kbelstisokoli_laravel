# Validace hesel (Profil a Auth)

Tento modul zajišťuje jednotnou a robustní validaci hesel napříč celou aplikací (registrace, obnova hesla, úprava profilu).

## 1. Pravidla pro hesla
V celém systému jsou nastavena jednotná pravidla pro hesla (přes `Password::defaults()`):
- **Minimální délka:** 8 znaků.
- **Velká a malá písmena:** Vyžadováno alespoň jedno od každého.
- **Čísla:** Vyžadováno alespoň jedno číslo.

## 2. Klientská validace (JavaScript)
Pro okamžitou zpětnou vazbu bez nutnosti odesílat formulář na server používáme vlastní validátor v `resources/js/filament-error-handler.js`.

### Klíčové funkce:
- **Indikátor síly:** Při psaní nového hesla se zobrazují splněná/nesplněná pravidla (ikony a barvy).
- **Kontrola shody:** Real-time kontrola, zda se potvrzení hesla shoduje s hlavním heslem.
- **Závislost polí:** V profilu je `Stávající heslo` vyžadováno pouze tehdy, pokud uživatel vyplňuje `Nové heslo`.
- **Prevence odeslání:** Pokud formulář obsahuje chyby zjištěné klientem, odeslání je zastaveno (`e.preventDefault()`).
- **Uživatelská přívětivost:** Při pokusu o odeslání neplatného formuláře stránka automaticky odroluje k první chybě a zaostří na dané pole.

## 3. Backendová validace (Laravel)
Klientská validace doplňuje (nenahrazuje) backendovou validaci. 
- V `ProfileController` jsou názvy polí lokalizovány (např. "Stávající heslo" místo "current_password"), aby chybové hlášky byly srozumitelné.
- Pokud klientská validace selže (nebo je vypnutý JS), server vrátí `ValidationException`, kterou odchytí Blade šablona a zobrazí standardní chybové hlášky pod poli.

## 4. Přepínání viditelnosti
Všechna pole pro hesla obsahují ikonu "oka" pro přepínání viditelnosti (plain text vs maskované), což zlepšuje UX zejména na mobilních zařízeních.
