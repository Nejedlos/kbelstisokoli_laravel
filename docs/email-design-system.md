# Email Design System - Kbelští sokoli

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
