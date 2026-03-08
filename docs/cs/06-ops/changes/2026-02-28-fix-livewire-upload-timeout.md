# Fix chyby UnableToRetrieveMetadata u hromadného nahrávání

## Popis problému
Při hromadném nahrávání velkého množství fotografií (až 200) do PhotoPoolu docházelo v produkčním prostředí k chybě `League\Flysystem\UnableToRetrieveMetadata: Unable to retrieve the file_size for file at location: livewire-tmp/...`.

## Příčina
1. **Timeout nahrávání:** Výchozí `max_upload_time` pro dočasné soubory v Livewire byl nastaven na 5 minut. Při nahrávání 200 fotografií (i po zmenšení v prohlížeči) mohl tento limit vypršet dříve, než uživatel formulář odeslal, což vedlo ke smazání dočasných souborů.
2. **Konfigurace disku:** Livewire pro dočasné soubory používal implicitní disk, který mohl mít v produkci (Webglobe hosting) problémy s přístupem nebo nesouladem cest mezi nahráváním a validací.
3. **Limity validace:** Validace velikosti souboru v Livewire byla nastavena na nižší hodnotu než v samotném komponentu FileUpload.

## Provedené změny
- **`config/livewire.php`**:
    - Zvýšen `max_upload_time` z 5 na 20 minut.
    - Explicitně nastaven disk pro dočasné nahrávání na `local` (s fallbackem na env).
    - Zvýšen limit velikosti souboru v Livewire pravidlech na 100 MB (`max:102400`), aby neblokoval validaci v konkrétních resourcích.
- **`.env.example` & `.env.production`**:
    - Přidána proměnná `LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=local`.

## Doporučení pro produkci
Zajistit, aby adresář `storage/app/livewire-tmp` existoval a byl zapisovatelný pro webserver. Tento adresář je automaticky čištěn Livewirem dle konfigurace (`cleanup => true`).
