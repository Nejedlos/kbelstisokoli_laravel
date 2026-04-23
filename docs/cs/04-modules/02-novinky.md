# Modul Novinky (Posts)

Tento modul slouží ke správě článků, aktualit a novinek na webu.

## 1. Účel modulu
Umožňuje redaktorům vytvářet a publikovat obsah, který je kategorizován a lokalizován do češtiny a angličtiny. Podporuje také SEO optimalizaci a správu médií.

## 2. Technické detaily
- **Model:** `App\Models\Post`
- **Resource:** `App\Filament\Resources\Posts\PostResource`
- **Lokalizace:** Využívá `spatie/laravel-translatable` pro pole `title`, `excerpt` a `content`.
- **Média:** Využívá `spatie/laravel-medialibrary` pro náhledový obrázek (`featured_image`).

## 3. Automatická optimalizace obrázků
Při nahrávání hlavního náhledového obrázku dochází k automatickému zpracování:
1.  **Změna velikosti:** Obrázek je automaticky zmenšen tak, aby se vešel do rozměrů **1920x1080 px** (poměr stran zachován, ořez cover).
2.  **Konverze do WebP:** Všechny nahrané obrázky jsou automaticky převedeny do formátu **WebP** pro dosažení minimální velikosti při zachování kvality.
3.  **Pojmenování:** Soubor je automaticky pojmenován podle českého titulku článku (slugifikováno) s příponou `.webp`.
4.  **Zpětná kompatibilita:** Pokud dojde ke změně titulku článku, fyzický soubor je na serveru automaticky přejmenován, aby odpovídal novému titulku (důležité pro SEO).

## 4. Uživatelské rozhraní
- **Globální loader:** Během nahrávání a optimalizace obrázku se v administraci zobrazuje globální basketbalový loader, aby uživatel věděl, že probíhá náročnější operace.
- **AJAX nahrávání:** Obrázky se nahrávají okamžitě po výběru souboru bez nutnosti ukládat celý formulář.

## 5. Konverze (Media Library)
Kromě optimalizovaného originálu jsou generovány i tyto konverze:
- `thumb`: 400x300 px (WebP)
- `large`: 1200x800 px (WebP)
