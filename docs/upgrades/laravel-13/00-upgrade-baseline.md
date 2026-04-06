# Laravel 13 Upgrade Baseline - 06.04.2026

Tento dokument slouží jako výchozí bod pro upgrade projektu **Kbelští sokoli** z Laravel 12 na Laravel 13.

## 🕒 Základní informace
- **Datum a čas:** 2026-04-06 14:01:00
- **Výchozí branch:** `main`
- **Nová branch:** `chore/upgrade-laravel-13-ai-native`
- **Poslední commit hash:** `dd83272c97da6f085fff652131ebb67c57dd6451`
- **Necommitnuté změny:** Žádné (working tree clean)

## 🎯 Cíl upgradu
Bezpečný upgrade projektu na Laravel 13 se zachováním plné funkčnosti všech klíčových komponent:
- **Filament PHP 5** (administrace a správa entit)
- **Livewire 4** (frontend interaktivita)
- **AI Vrstva** (OpenAI integrace, sémantické vyhledávání, normalizace dat)

## ⚙️ Systémové prostředí
- **PHP:** 8.4.19 (Built by Laravel Herd)
- **Composer:** 2.9.5 (2026-01-29 11:40:53)

## 📦 Verze hlavních komponent
- **Laravel Framework:** 12.52.0
- **Livewire:** v4.1.4
- **Filament PHP:** v5.2.2 (včetně modulů actions, forms, schemas, tables, widgets)

## 🤖 AI Integrace a balíčky
AI vrstva je v projektu implementována robustně jako vlastní service vrstva (`App\Services\Ai*`), která využívá standardní Laravel nástroje (`Http` fasáda) pro komunikaci s OpenAI API.

### Související balíčky (composer.json):
- `laravel/scout` (^10.24) - Využíváno pro sémantické a indexované vyhledávání.

### Klíčové AI komponenty:
- `App\Services\AiSearchService` (Sémantické vyhledávání přes OpenAI)
- `App\Services\AiTextEnhancer` (Vylepšování textů pomocí AI)
- `App\Services\AiIndexService` (Indexace a obohacení dat pro vyhledávání)
- `App\Services\Stats\Normalizers\OpenAiNormalizer` (Extrakce a normalizace sportovních dat z HTML)
- `App\Services\AiSettingsService` (Centrální správa OpenAI konfigurace)

---
*Dokument byl vygenerován jako součást přípravy na upgrade.*
