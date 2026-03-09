# Standardy e-mailových předmětů

Tento dokument definuje pravidla pro formátování předmětů e-mailů odesílaných ze systému Kbelští sokoli. Cílem je, aby předměty působily lidsky, profesionálně a neobsahovaly strojové prvky (jako jsou hranaté závorky `[]`).

## 1. Základní pravidla

- **Zákaz hranatých závorek:** Nikdy nepoužívejte `[]` pro označení systému nebo kategorie (např. `[KS FEEDBACK]` je špatně).
- **Oddělovač:** Jako hlavní oddělovač mezi kategorií/brandem a konkrétním tématem používáme svislou čáru s mezerami ` | `.
- **Branding:** Pokud je v předmětu název klubu, píšeme jej přirozeně (např. `Kbelští sokoli | ...` nebo `Kbely Falcons | ...`).
- **Lidský jazyk:** Předměty by měly znít jako od člověka, nikoliv jako systémový log.

## 2. Příklady formátování

### Systémová a transakční oznámení
- **Staré:** `[Kbelští sokoli] Přihrávka pro nové heslo`
- **Nové:** `Kbelští sokoli | Přihrávka pro nové heslo`

### Zpětná vazba a hlášení
- **Staré:** `[KS FEEDBACK #123] Nefunguje nahrávání fotek`
- **Nové:** `Zpětná vazba č. 123 | Nefunguje nahrávání fotek`

### Chybová hlášení (pro administrátory)
- **Staré:** `[APP][PRODUCTION] ExceptionName (file:line)`
- **Nové:** `Chyba aplikace | PRODUCTION | ExceptionName (file:line)`

### Pre-boot chyby (chyby při spuštění)
- **Staré:** `[PreBoot][PRODUCTION] ExceptionName`
- **Nové:** `Chyba spuštění | PRODUCTION | ExceptionName`
- **Staré:** `[PreBoot][FATAL] Error message`
- **Nové:** `Kritická chyba spuštění | Error message`

## 3. Implementace v kódu

Při vytváření nových e-mailů (Mailable nebo Notifications) se vyhněte statickým řetězcům se závorkami.

**Špatně:**
```php
return new Envelope(
    subject: "[KS NOTIFICATION] Nová zpráva",
);
```

**Správně:**
```php
return new Envelope(
    subject: "Kbelští sokoli | Nová zpráva",
);
```

V případě dynamických předmětů používejte `sprintf` nebo interpolaci řetězců s oddělovačem ` | `.
