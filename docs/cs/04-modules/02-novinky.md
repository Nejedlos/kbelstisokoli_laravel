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
1.  **Změna velikosti:** Obrázek je automaticky zmenšen u klienta tak, aby se vešel do rozměrů **1920x1080 px** (poměr stran zachován, ořez cover). To šetří čas nahrávání a místo na serveru.
2.  **Konverze na WebP:** Originál je nahrán v původním formátu (JPEG/PNG) pro maximální stabilitu, ale systém automaticky generuje WebP varianty (`thumb`, `large`) pro použití na webu.
3.  **Pojmenování:** Soubor je automaticky pojmenován podle českého titulku článku (slugifikováno) se zachováním původní přípony.
4.  **Zpětná kompatibilita:** Pokud dojde ke změně titulku článku, fyzický soubor je na serveru automaticky přejmenován, aby odpovídal novému titulku (důležité pro SEO).

## 4. Uživatelské rozhraní
- **Globální loader:** Během nahrávání a ukládání obrázku se v administraci zobrazuje globální basketbalový loader. Je implementován tak, aby pokrýval i čas pro přípravu souboru (resize u klienta) a následné automatické uložení, čímž je dosaženo plynulého uživatelského zážitku.
- **Automatické uložení (Autosave):** Po dokončení nahrávání obrázku se článek **automaticky uloží** do databáze. Redaktor nemusí ručně klikat na tlačítko "Uložit změny" jen kvůli nahrání fotky. Proces je chráněn logováním pro případné debugování na straně serveru.

## 5. Technické poznámky (Filament v5)
- Pro správnou funkci autosave je u pole `featured_image` zapnuta direktiva `live()`, která zajistí synchronizaci stavu před samotným uložením.
- Disky pro nahrávání jsou sjednoceny na `media_public`, aby odpovídaly definici v modelu.

## 6. Konverze (Media Library)
Kromě optimalizovaného originálu jsou generovány i tyto konverze:
- `thumb`: 400x300 px (WebP)
- `large`: 1200x800 px (WebP)
