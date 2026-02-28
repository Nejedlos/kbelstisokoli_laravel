# Dávkový import fotografií (PhotoPool)

Tento modul řeší problém s timeouty při nahrávání velkého množství fotografií (až 200 ks najednou) v administraci na hostingu Webglobe.

## Problém
Při synchronním zpracování (vytváření `MediaAsset`, pivot vazeb a generování konverzí pomocí Spatie Media Library) docházelo u velkých setů k překročení PHP timeoutu (60s) nebo paměťových limitů.

## Řešení: Dávkování a Polling
Import byl rozdělen do dvou fází:

1.  **Příprava (Save/Create):** 
    - Nahrané soubory z dočasné složky Livewire jsou pouze přesunuty do trvalé složky (`photo_pools/{id}/originals`).
    - Cesty k těmto souborům jsou uloženy do JSON sloupce `pending_import_queue` v modelu `PhotoPool`.
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

## Technické detaily
- **Služba:** `App\Services\PhotoPoolImporter`
- **Trait:** `App\Traits\HasPhotoPoolImport` (použito v `EditPhotoPool` a `CreatePhotoPool`)
- **Model:** Přidány sloupce `pending_import_queue` (json) a `is_processing_import` (bool).
- **Konfigurace:** Dávka je nastavena na 5 kusů pro maximální kompatibilitu s Webglobe limity.
