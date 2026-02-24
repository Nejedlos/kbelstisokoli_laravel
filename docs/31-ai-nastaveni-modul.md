# AI Nastavení Modul (Technická dokumentace)

Tento modul poskytuje centrální správu AI funkcí v projektu Kbelští sokoli. Umožňuje konfiguraci poskytovatelů (OpenAI), modelů, chování inference a monitorování aktivity.

## Architektura

Modul se skládá z následujících komponent:

- **Databáze:**
    - `ai_settings`: Singleton tabulka uchovávající globální konfiguraci. Citlivé údaje (API klíče) jsou ukládány šifrovaně pomocí Laravel `encrypted` castu.
    - `ai_request_logs`: Tabulka pro audit a debugování AI požadavků.
- **Modely:**
    - `App\Models\AiSetting`: Eloquent model pro nastavení.
    - `App\Models\AiRequestLog`: Eloquent model pro logování.
- **Služby:**
    - `App\Services\AiSettingsService`: Hlavní servisní vrstva. Zajišťuje načítání nastavení s využitím cache a fallbacku na `.env` proměnné. Poskytuje metodu pro logování.
- **UI (Filament):**
    - `App\Filament\Pages\AiSettings`: Administrační stránka s moderním dashboardem pro správu nastavení.
    - `resources/views/filament/pages/ai-settings.blade.php`: Custom Blade view pro sexy vzhled.

## Fallback Mechanismus

Systém je navržen tak, aby byl robustní i bez záznamu v databázi:

1. Pokud je `AI_USE_DATABASE_SETTINGS=false` (v `.env`) nebo v DB vypnuto, použijí se hodnoty z `config/ai.php` (které načítají `OPENAI_*` z `.env`).
2. Pokud je v DB zapnuto používání databáze, mají hodnoty z DB přednost.
3. Pokud v DB chybí konkrétní hodnota, systém se snaží použít rozumný výchozí fallback.

## Bezpečnost

- **API Klíče:** Jsou v databázi uloženy šifrovaně (`encrypted`). V administraci jsou maskovány a uživatel je může bezpečně přepsat.
- **Oprávnění:** Přístup k modulu je chráněn oprávněním `manage_ai_settings`.
- **Redakce logů:** Doporučuje se logovat pouze nezbytné informace. Logování promptů a odpovědí lze v nastavení vypnout.

## Cache

Nastavení jsou cachována (výchozí 1 hodina) pod klíčem `ai_global_settings`. Při uložení nastavení přes Filament se cache automaticky invaliduje.
