# Fotopool (hromadné nahrávání a správa)

Tento modul umožňuje spravovat centrální „pool“ fotografií a hromadně je nahrávat do knihovny médií. Galerie lze následně naplnit náhodným výběrem fotek z poolu.

## Cíle a funkce
- Hromadné nahrávání fotek s metadaty akce (název, datum, typ, popis).
- Volitelná AI asistence pro vylepšení názvu a popisu před uložením.
- Normalizace obrázků (konverze do WebP pro náhledy/velké zobrazení, resize extrémních rozměrů přes Spatie konverze).
- Logická struktura: `PhotoPool` (akce) → `MediaAsset` (jednotlivé fotky) + pivot.
- Doplnění konkrétní galerie náhodnými fotkami z vybraného/všech veřejných poolů.

## Datový model
- `photo_pools`
  - `title` (JSON, translatable)
  - `slug` (unikátní)
  - `description` (JSON, translatable)
  - `event_type` (string: `tournament|match|training|club_event|other`)
  - `event_date` (date)
  - `is_public`, `is_visible` (bool)
- `photo_pool_media_asset` (pivot)
  - `photo_pool_id`, `media_asset_id`
  - `sort_order`, `caption_override`, `is_visible`

## Admin (Filament)
- Resource: „Pooly fotografií“ (`PhotoPoolResource`) ve skupině Média.
- Formulář:
  - Rozdělení do záložek (Základní informace, Fotografie, Nastavení).
  - Název, Slug (automatické generování), Typ akce, Datum akce.
  - Interaktivní AI asistence (ikonka u pole Název) – okamžitě navrhne vylepšená metadata (název, datum, popis) a promítne je do polí k revizi.
  - Hromadné nahrávání fotografií (JPG/PNG/WEBP/HEIC/HEIF, max ~30 MB/ks, až 200 souborů). Nahrávat lze při vytváření i editaci (doplňování).
  - Viditelnost: Veřejné, Viditelné v nabídce.

## Naplnění galerií z poolu
- V `GalleryResource` → „Média v galerii“ je akce „Doplnit z poolu“.
  - Parametry: volitelný výběr konkrétního poolu, počet fotek.
  - Při přidání se nastaví `caption_override` ve tvaru „Název akce — datum“ (z poolu), takže je informace viditelná i v UI.

## Optimalizace obrázků
- `MediaAsset` konverze:
  - `thumb` (300×300, WebP)
  - `large` (1600×1600, WebP, optimalizované)
- Původní soubor zůstává v nahrané podobě (neláme kompatibilitu). Frontend používá konverze.

## AI integrace
- Využívá globální `AiSettingsService` (OPENAI_*). Pokud je AI vypnuté nebo dojde k chybě, použije se bezpečný fallback (normalizace textu).

## Poznámky
- Překlady: `title` a `description` jsou translatable (Spatie). V administraci lze přepínat jazyky dle projektové konfigurace.
- HEIC/HEIF podpora závisí na PHP/GD/Imagick – pokud nebude k dispozici, soubor se nahraje, ale konverze do WebP může vyžadovat doplňkovou podporu serveru.

## Nasazení
- Spuštění migrací: `php artisan migrate --no-interaction`
- Pokud se mění assety/konverze, není třeba rebuildovat Vite (konverze probíhá na backendu přes Spatie ML).

## Omezení
- „Překlopení“ původního souboru na WebP se nedělá – používají se optimalizované konverze (WebP) pro zobrazení, originál zůstává zachován.
- Akce „Navrhnout AI“ vrací návrh pro aktuální jazyk administrace; druhý jazyk doplňte ručně přepnutím lokalizace ve Filamentu.
