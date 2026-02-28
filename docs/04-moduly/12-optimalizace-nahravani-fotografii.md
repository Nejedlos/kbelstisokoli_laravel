# Optimalizace nahrávání fotografií

Tento dokument popisuje technické úpravy provedené pro výrazné zrychlení hromadného nahrávání fotografií (zejména v administraci Photo Pools) a řešení s tím spojených limitů.

## Provedené změny

Pro vyřešení problémů s pomalým nahráváním velkého množství souborů (až 200 najednou) a chybami typu 429 (Too Many Attempts) byly aplikovány následující optimalizace:

### 1. Paralelní nahrávání (Paralelismus)
Dříve se soubory nahrávaly sekvenčně jeden po druhém. Nyní je u komponenty `FileUpload` nastaven parametr `maxParallelUploads(10)`, což umožňuje nahrávat až 10 souborů současně.
- **Dopad:** Výrazné zkrácení celkového času nahrávání hromadných dávek díky eliminaci sekvenčního čekání.

### 2. Klientská optimalizace (Image Resize v prohlížeči)
Při nahrávání velkých fotografií (často 10 MB a více přímo z fotoaparátu) docházelo k dlouhému přenosu dat. Nyní se fotografie **zmenšují přímo v prohlížeči** před odesláním na server:
- Maximální rozměr: **1920px** (šířka i výška, zachování poměru stran).
- **Výhoda:** Na server se přenáší řádově menší objem dat (typicky 200–500 KB místo 10 MB), což dramaticky zrychluje upload i na pomalejším připojení.
- **Kvalita:** Rozlišení 1920px je pro webové galerie plně dostačující.

### 3. Zvýšení limitů požadavků (Throttle fix)
Zavedení paralelismu (10 požadavků najednou) vedlo k narážení na výchozí bezpečnostní limit Laravelu/Livewire (60 požadavků za minutu). Při nahrávání 200 fotek se tak upload po 60. souboru zastavil s chybou 429.
- **Řešení:** V `config/livewire.php` byl limit pro `temporary_file_upload.middleware` zvýšen na `throttle:300,1`.
- To umožňuje bezproblémové nahrání celého limitu 200 fotek v jedné dávce.

### 4. Vypnutí Image Editoru pro hromadné akce
U komponent pro hromadný upload byl vypnut `imageEditor()`. Inicializace editoru pro stovky souborů v prohlížeči způsobovala extrémní vytížení RAM a CPU, což vedlo k zamrzání UI. Pro hromadné nahrávání je preferována rychlost a jednoduchost.

### 5. Synchronizace maximální velikosti (Rules)
V konfiguraci `config/livewire.php` byla zvýšena maximální povolená velikost dočasně nahrávaného souboru na **35 MB** (`max:35840`), aby byla v souladu s nastavením formulářů (30 MB) a nedocházelo k zamítnutí velkých souborů předčasně.

### 6. Podpora náhledů pro HEIC/HEIF
Do konfigurace `preview_mimes` byly přidány formáty `heic` a `heif`, což umožňuje zobrazení náhledů v dropzoně i pro moderní formáty z mobilních telefonů (Apple).

## Technické detaily (Soubory)

Optimalizace byla aplikována v těchto souborech:
- `app/Filament/Resources/PhotoPools/PhotoPoolResource.php` (Hlavní formulář PhotoPool)
- `app/Filament/Resources/PhotoPools/Pages/ListPhotoPools.php` (Wizard pro hromadné vytvoření poolu)
- `config/livewire.php` (Globální nastavení limitů a náhledů)

## Diagnostika (Debug logování)
Do dropzony byly přidány JS události (vyžadují otevřenou konzoli prohlížeče), které vypisují:
- Inicializaci FilePond.
- Přidání souboru do fronty.
- Úspěšné dokončení uploadu na server.
- Tyto logy jsou uvozeny prefixem `KS DEBUG:`.
