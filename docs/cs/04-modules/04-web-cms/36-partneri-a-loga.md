# Správa partnerů a log

Tento dokument popisuje modul Partnerů, jejich správu v administraci a specifika nahrávání log.

## 1. Účel modulu
Modul slouží ke správě partnerů klubu (hlavní, generální, mediální atd.) a jejich prezentaci na webu (homepage, patička, stránky zápasů).

## 2. Správa v administraci (Filament)
Partneři jsou spravováni přes `PartnerResource`. Každý partner obsahuje:
- **Základní údaje:** Název, slug (automaticky generovaný), typ partnera, webová URL.
- **Nastavení zobrazení:** Aktivní stav, zvýraznění, pořadí a přepínače pro umístění na konkrétních částech webu.
- **Lokalizované texty:** Štítek (label) a popis v češtině a angličtině.

## 3. Nahrávání log a formáty
Na rozdíl od standardní knihovny médií (která využívá Spatie Media Library) používá tento modul přímé nahrávání do `public/assets/img/partners/` pro maximální výkon a jednoduchost integrace do frontendu.

### 3.1 Proces nahrávání
1. **Zdrojový soubor:** Uživatel nahraje logo ve formátu PNG, JPG nebo WebP do pole "Logo (PNG/JPG/WebP)".
2. **Přejmenování:** Soubor je automaticky přejmenován podle vzoru `{slug}-{timestamp}.{ext}`.
3. **WebP Konverze:** Systém po nahrání automaticky vygeneruje (nebo sjednotí) WebP verzi obrázku pomocí knihovny GD.
    - **Průhlednost:** Konverze je optimalizována pro zachování alpha kanálu u PNG a WebP zdrojů (průhledné pozadí nezčerná).
4. **Uložení cest:** Do databáze se uloží relativní cesty k obou souborům:
    - `logo_path_png`: Cesta k originálu (např. `assets/img/partners/sponzor-12345.png`).
    - `logo_path_webp`: Cesta k WebP verzi (např. `assets/img/partners/sponzor-12345.webp`).

### 3.2 Technické detaily
- **Disk:** `public_path` (ukazuje přímo do složky `public/` projektu).
- **Adresář:** `assets/img/partners/`.
- **Kvalita WebP:** Konverze probíhá s kvalitou 85.
- **Zobrazení:** V administraci se v tabulce i ve formuláři zobrazuje náhled loga s šachovnicovým pozadím pro lepší čitelnost log s průhledností.

## 4. Použití na frontendu
Na frontendu se doporučuje používat komponentu `<picture>` pro zajištění WebP s fallbackem na PNG/JPG:

```html
<picture>
    <source srcset="{{ web_asset($partner->logo_path_webp) }}" type="image/webp">
    <img src="{{ web_asset($partner->logo_path_png) }}" alt="{{ $partner->name }}">
</picture>
```

Helper `web_asset()` zajistí správné sestavení URL nezávisle na prostředí (vývoj/produkce).
