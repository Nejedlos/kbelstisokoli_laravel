# Dokumentace projektu Kbelští sokoli

Vítejte v technické dokumentaci projektu Kbelští sokoli. Tato složka obsahuje detailní popis modulů, funkcionalit a technických specifikací celého systému.

> DŮLEŽITÉ UPOZORNĚNÍ K ASSETŮM
>
> Po KAŽDÉ úpravě vzhledu (změna CSS/JS) je nutné spustit `npm run build`, jinak se změny neprojeví. Podrobnosti viz kapitola „06. Správa assetů (Vite, CSS, JS)“.

## Zdrojový kód (GitHub)
Repozitář: [https://github.com/Nejedlos/kbelstisokoli_laravel](https://github.com/Nejedlos/kbelstisokoli_laravel)

## Obsah dokumentace
00. [Úvod a přehled](00-uvod.md)
01. [Architektura projektu](01-architektura.md)
02. [Struktura projektu (Souborový systém)](02-struktura-projektu.md)
03. [Konfigurace prostředí](03-konfigurace.md)
04. [Autentizace a autorizace](04-autentizace-a-autorizace.md)
05. [Lokalizace a překlady](05-lokalizace.md)
06. [Správa assetů (Vite, CSS, JS)](06-sprava-assetu.md)
07. [Font Awesome Pro (Ikony)](07-font-awesome.md)
08. [Administrace (Filament PHP)](08-administrace-filament.md)
09. [Správa uživatelů a profilů](09-sprava-uzivatelu-a-profilu.md)
10. [Sportovní modul a RSVP](10-sportovni-modul.md)
11. [Statistiky a soutěže](11-statistiky-a-souteze.md)
12. [Ekonomický modul a fakturace](12-ekonomicky-modul.md)
13. [Média a Galerie](13-media-a-galerie.md)
14. [Komunikace a notifikace](14-komunikace-a-notifikace.md)
15. [Veřejný frontend a CMS](15-verejny-frontend-a-cms.md)
16. [Systémová automatizace a Redirecty](16-systemova-automatizace-a-redirecty.md)
17. [Nasazení na produkční server (Deployment)](17-nasazeni-na-produkci.md)

## Rychlý start
Projekt je postaven na Laravelu 12. Pro lokální vývoj se doporučuje používat **Laravel Sail**.

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```
