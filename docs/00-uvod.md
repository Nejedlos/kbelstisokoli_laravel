# Dokumentace projektu Kbelští sokoli

Vítejte v technické dokumentaci projektu Kbelští sokoli. Tato složka obsahuje detailní popis modulů, funkcionalit a technických specifikací celého systému.

## Zdrojový kód (GitHub)
Repozitář: [https://github.com/Nejedlos/kbelstisokoli_laravel](https://github.com/Nejedlos/kbelstisokoli_laravel)

## Obsah dokumentace
00. [Úvod a přehled](00-uvod.md)
01. [Architektura projektu](01-architektura.md)
02. [Struktura projektu (Souborový systém)](02-struktura-projektu.md)
03. [Konfigurace prostředí](03-konfigurace.md)
04. [Autentizace a autorizace (Auth System)](04-autentizace-a-autorizace.md)
05. [Lokalizace a překlady](05-lokalizace.md)
06. [Správa assetů (Vite, CSS, JS)](06-sprava-assetu.md)
07. [Font Awesome Pro (Ikony)](07-font-awesome.md)
08. [Administrační rozhraní (Filament)](08-administrace-filament.md)
09. [Správa uživatelů a členů](09-sprava-uzivatelu.md)
10. [Ekonomický modul a fakturace](10-ekonomicky-modul.md)
11. [Veřejný frontend (Web)](11-verejny-frontend.md)
12. [Nasazení na produkční server (Deployment)](12-nasazeni-na-produkci.md)

## Rychlý start
Projekt je postaven na Laravelu 12. Pro lokální vývoj se doporučuje používat **Laravel Sail**.

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```
