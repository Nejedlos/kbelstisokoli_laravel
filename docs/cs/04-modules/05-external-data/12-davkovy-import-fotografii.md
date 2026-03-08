# Dávkový import fotografií (PhotoPool)

Tento modul řeší problém s timeouty při nahrávání velkého množství fotografií (až 200 ks najednou) v administraci na hostingu Webglobe.

## Problém
Při synchronním zpracování (vytváření `MediaAsset`, pivot vazeb a generování konverzí pomocí Spatie Media Library) docházelo u velkých setů k překročení PHP timeoutu (60s) nebo paměťových limitů.

## Řešení: Dávkování a Polling
Import byl rozdělen do dvou fází:

1.  **Příprava (Save/Create):** 
    - Nahrané soubory z dočasné složky Livewire jsou pouze přesunuty do trvalé složky (`photo_pools/{id}/originals`).
    - Cesty k těmto souborům jsou uloženy do sloupce `pending_import_queue` (typ `longText`, v Laravelu castováno na `array`) v modelu `PhotoPool`.
    - Tato operace je velmi rychlá (pouze přesun souborů na disku).

2.  **Zpracování (Polling):**
    - Ve Filament formuláři se při neprázdné frontě zobrazí indikátor stavu.
    - Pomocí Livewire pollingu (`wire:poll`) se každé 3 sekundy na serveru spustí metoda `processImportQueue`.
    - Ta zpracuje dávku **5 fotografií** (vytvoří assety a konverze).
    - Po úspěšném zpracování jsou původní soubory z `originals` složky smazány, aby se šetřilo místo (Spatie si je již zkopírovala do své struktury).

## Výhody
- Žádné timeouty (každý request trvá jen pár sekund pro malou dávku).
- Uživatel vidí reálný progress (kolik fotek zbývá).
- Robustnost: Pokud se jeden request přeruší, příště se naváže tam, kde se skončilo.

## Uživatelské rozhraní a Zpětná vazba
Modul poskytuje uživateli jasnou informaci o průběhu:

1.  **Helper Text v Uploaderu:** Informuje o hromadném nahrávání a varuje před zavřením okna.
2.  **Stavový panel (Processing Progress):** Zobrazuje se v editaci poolu, pokud je fronta neprázdná. Obsahuje animovaný indikátor, počet zbývajících souborů a výrazné varování před zavřením okna.
3.  **Global Loader (Basketball):** Při každém běhu dávkového zpracování (`processImportQueue`) se přes celou obrazovku zobrazí animovaný loader s textem, aby uživatel viděl, že server aktivně pracuje.

---

## Technické detaily
- **Služba:** `App\Services\PhotoPoolImporter`
- **Trait:** `App\Traits\HasPhotoPoolImport` (použito v `EditPhotoPool` a `CreatePhotoPool`)
- **Model:** Přidány sloupce `pending_import_queue` (`longText`) a `is_processing_import` (`bool`).
- **Konfigurace:** Dávka je nastavena na 5 kusů pro maximální kompatibilitu s Webglobe limity.
