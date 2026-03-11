# Fix memory leak na `/admin/help`

## Kontext
- Při renderu stránky `/admin/help` docházelo k fatální chybě `Allowed memory size exhausted`.
- Pád se často projevil v `spatie/laravel-translatable`, ale root cause byl v aplikační logice breadcrumbs/debug toku.

## Root cause
- Dočasná diagnostika ukládala velké množství breadcrumbs i při častém volání překladů (`getTranslation`) v modelech.
- `HelpNavigationService::getBreadcrumbs()` měl nejednoznačný vstup a homepage tok nebyl explicitně ukončen (`null` item).
- Chyběla robustní ochrana proti cyklickému parent-chain při skládání breadcrumbs.

## Oprava
- `HelpNavigationService::getBreadcrumbs()` nyní používá striktní signaturu `HelpCategory|HelpArticle|null`.
- Homepage (`null`) se vrací okamžitě se základním breadcrumbem bez dalšího zpracování.
- Přidána ochrana proti cyklům a limit hloubky (`MAX_BREADCRUMB_DEPTH = 50`).
- V breadcrumbs se vrací jen lehká scalar data (`label`, `slug`, `url`, `is_active`).
- Odstraněny dočasné debug override `getTranslation()` v help modelech.
- Pre-boot diagnostika byla zpevněna:
  - breadcrumb context se sanitizuje na scalar hodnoty,
  - interní breadcrumb buffer má pevný limit 200 položek,
  - odstraněno navyšování `memory_limit` v `public/index.php`.

## Ověření
- `HelpService::getHomeData()` běží stabilně (bez růstu paměti do stovek MB) a vrací validní data.
- Kontrola dat `help_categories` neodhalila self-reference ani sirotky parent vazeb.
- Endpoint `/admin/help` je připraven pro ověření v browseru bez předchozího nafukování diagnostických struktur.
