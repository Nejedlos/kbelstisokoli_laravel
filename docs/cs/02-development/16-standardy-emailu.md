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


---

# Email Design System - Technický popis

Tento dokument popisuje systém pro odchozí e-maily v projektu Kbelští sokoli. Cílem je moderní vzhled, jednoduchost a maximální kompatibilita s e-mailovými klienty.

## 1. Architektura
E-maily jsou postaveny na Blade šablonách s využitím "table-based" layoutu a inline stylů.

- **Základní layout:** `resources/views/emails/layouts/base.blade.php`
- **Konfigurace:** `config/email_branding.php`
- **Assety:** Loga jsou umístěna v `public/assets/img/brand/`.

## 2. Kompatibilita
Pro zajištění funkčnosti v Outlooku, Gmailu a Apple Mailu dodržujeme tato pravidla:
- Šířka kontejneru: **600px** (fixní s mobilním fallbackem).
- Fonty: **Arial, Helvetica, sans-serif**.
- Layout: Výhradně přes HTML tabulky (`<table>`).
- Stylování: Pouze **inline CSS** (atribut `style`).
- **Zákaz:** Flexbox, Grid, `position: fixed`, externí CSS.

## 3. Komponenty (Partials)
Pro běžné prvky e-mailu používejte připravené Blade partials:
- `@include('emails.partials.button', ['url' => $url, 'text' => 'Tlačítko'])`
- `@include('emails.partials.panel', ['text' => 'Obsah info boxu'])`
- `@include('emails.partials.divider')`
- `@include('emails.partials.spacer', ['height' => 20])`
- `@include('emails.partials.key-value-table', ['items' => ['Klíč' => 'Hodnota']])`
- `@include('emails.partials.list-table', ['header' => ['H1', 'H2'], 'rows' => [['C1', 'C2']]])`

## 4. Náhledy (Preview)
Pro vývoj a kontrolu e-mailů je k dispozici preview systém (pouze pro local nebo adminy):
- URL: `/dev/mail-preview`
- Controller: `App\Http\Controllers\Dev\MailPreviewController`

## 5. Jak přidat nový e-mail
1. Vytvořte Mailable třídu (`php artisan make:mail`).
2. V metodě `content()` použijte `view: 'emails.vase-sablona'`.
3. Šablona by měla dědit z layoutu:
```blade
@extends('emails.layouts.base')

@section('title', 'Předmět e-mailu')

@section('content')
    <h1 style="margin-top: 0; color: {{ config('email_branding.colors.secondary') }}; font-size: 22px; font-weight: bold;">Nadpis</h1>
    <p>Obsah zprávy...</p>
    @include('emails.partials.button', ['url' => $url, 'text' => 'Akce'])
@endsection
```

## 6. Branding
Konfigurace v `config/email_branding.php` čerpá z hlavního nastavení brandingu klubu.
- **Logo:** `email-logo.png` (šířka 140px).
- **Barvy:** Primární barva `#E11D48` (červená) pro tlačítka a akcenty.
