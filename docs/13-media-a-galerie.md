# Média a Galerie

Účel: Centrální správa médií (obrázků, dokumentů) a galerií s integrací do Page Builderu a veřejného webu.

## 1. Architektura
- **MediaAsset:** Centrální model pro všechna média. Využívá `Spatie Media Library` pro fyzické ukládání souborů a generování konverzí (náhledů).
- **Gallery:** Model pro kolekce médií. Umožňuje definovat název, popis, cover a styl zobrazení.
- **GalleryMedia:** Vazební tabulka (M:N) s podporou řazení a override popisků pro konkrétní galerie.

## 2. Admin správa (Filament)
- **Knihovna médií:** Přehled všech nahraných souborů s náhledy, správou metadat (Alt text, Title) a úrovní přístupu (Public/Member/Private).
- **Galerie:** Správa alb, kde lze snadno připojovat assety z knihovny, měnit jejich pořadí (Drag & Drop) a nastavovat viditelnost.

## 3. Integrace do Page Builderu
Bloky využívají vazbu na ID z knihovny médií:
- **Hero sekce:** Výběr obrázku na pozadí z knihovny.
- **Obrázek:** Zobrazení konkrétního assetu s volitelným popiskem.
- **Galerie:** Výběr existující galerie ze systému a volba layoutu (Grid/Masonry).

## 4. Veřejné zobrazení
- **URL prostor:** `/galerie` (listing) a `/galerie/{slug}` (detail).
- **Helpery:**
    - `media_url($id, $conversion)` – získá URL k souboru s volitelnou konverzí.
    - `media_alt($id)` – získá alternativní text pro SEO.
- **Fallbacky:** Pokud asset nebo galerie neexistuje, systém zobrazí sjednocený prázdný stav (`x-empty-state`).

## 5. Přístup a bezpečnost
- **Úrovně přístupu:** Každý asset má definovaný `access_level`.
- **Validace:** Při nahrávání jsou vynuceny limity na velikost a typy souborů (obrázky, PDF).
