# Veřejný frontend a CMS

Cíl: Vytvořit konzistentní, hypermoderní a sportovní veřejný frontend řízený backendem (branding + page builder), mobile‑first.

## 1. Architektura a technologie
- **Framework:** Blade + Tailwind CSS v4.
- **Routing:** Systémové stránky (controllers) + CMS stránky (dynamické slugy).
- **Branding:** `App\Services\BrandingService` zajišťuje dynamické barvy (CSS proměnné) a identity klubu.

## 2. Page Builder (Blokový systém)
Umožňuje správcům sestavovat stránky z předdefinovaných bloků.
- **Renderer:** Komponenta `<x-page-blocks :blocks="$page->content" />`.
- **Typy bloků:** Hero sekce, Textový blok, Obrázek, CTA, Mřížka karet, Statistiky, Novinky, Zápasy, Galerie, Kontakt, Vlastní HTML.
- **Expert mód:** Možnost definovat vlastní CSS třídy, ID a HTML atributy pro každý blok.

## 3. Branding a Design System
- **Design Tokens:** Barvy, typografie, radiusy a stíny jsou definovány jako CSS proměnné v `:root`.
- **Hashe (Placeholders):** V textech lze používat `###TEAM_NAME###`, `###CLUB_NAME###` atd., které se automaticky nahrazují.
- **Presets:** Podpora barevných témat (např. `club-default`, `dark-arena`).

## 4. Režim přípravy (Under Construction)
Stylové zobrazení informace o přípravě webu s basketbalovou tématikou.
- **Aktivace:** Automaticky (pokud není obsah) nebo manuálně v nastavení.
- **Bypass:** Admini a trenéři vidí web i v režimu přípravy.
- **Vizuál:** Animovaný 3D basketbalový míč, taktické prvky (X a O), atmosférické gradienty.

## 5. SEO a Metadata
- **Dynamické tagy:** Meta tagy (title, description, OG tagy) jsou generovány dynamicky.
- **SEO vztah:** Většina modelů (Page, News) má vazbu na SEO metadata.
- **Fallbacky:** Pokud metadata chybí, použijí se hodnoty z brandingu.

## 6. Stránka Úvod (Homepage)
Úvodní stránka je plně řízena Page Builderem a obsahuje bohatý, moderně stylovaný obsah:
- **Hero sekce:** Velké nadpisy s podporou pro basketbalové grafické prvky a animované CTA.
- **Statistiky:** Animované číselné karty s ikonami Font Awesome Pro.
- **Grid karet:** Přehled hlavních pilířů klubu s moderními hover efekty.
- **Výzva k akci (CTA):** Výrazné sekce pro nábor a kontakt.
- **SEO optimalizace:** Každá sekce je navržena pro maximální indexovatelnost a obsahuje reálný, bilingvní obsah z klubu.

## 8. Administrační Dashboard (Nástěnka)
Dashboard v administraci byl kompletně redesignován jako moderní, bilingvní a funkční rozhraní (Bento Grid):
- **Hero Sekce:** Dynamické uvítání (včetně personalizace pro admina), rychlé akce pro zápasy, uživatele a tréninky.
- **KPI Statistiky:** Přehledné karty pro počet uživatelů, hráčů a týmů s vizuální indikací.
- **Ekonomický Panel:** Reálný stav pohledávek, dluhů a příjmů za aktuální měsíc.
- **Zdraví Systému:** Inteligentní monitoring Cron úloh, rozporů v docházce a chybějících finančních profilů (včetně alertu na inicializaci nové sezóny).
- **Aktivita a Agenda:** Historie změn v systému a přehled nadcházejících zápasů a tréninků v bočním panelu.
- **Bilingvnost:** Veškeré texty a metriky jsou plně lokalizovány (CZ/EN).
