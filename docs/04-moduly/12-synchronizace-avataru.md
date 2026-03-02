# Synchronizace výchozích avatarů

Tento modul zajišťuje import a synchronizaci systémových avatarů (ilustrací) do společné galerie `MediaAsset`, která slouží k výběru profilových obrázků členů.

## 1. Účel modulu
- Poskytnout členům a administrátorům výběr z předpřipravených ilustrací.
- Automatizovat proces nahrávání těchto obrázků do DB a Media Library.
- Zajistit konzistentní cesty k těmto souborům přes `CustomPathGenerator`.

## 2. Příkaz pro synchronizaci
Synchronizace se provádí pomocí Artisan příkazu:

```bash
php artisan sync:default-avatars
```

### Parametry a volby:
- `--force`: Přepíše existující avatary, pokud již existují (vynutí update v Media Library).
- `--limit=X`: Zpracuje pouze `X` souborů v této dávce. Užitečné při velkém množství souborů nebo na slabších serverech s nízkým timeoutem.
- `--offset=X`: Začne zpracovávat od `X`-tého souboru.
- `--stop-on-error`: Zastaví zpracování při první chybě (doporučeno pro debugování problematických souborů).

### Příklad dávkového zpracování (po 100):
```bash
php artisan sync:default-avatars --limit=100 --offset=0
php artisan sync:default-avatars --limit=100 --offset=100
php artisan sync:default-avatars --limit=100 --offset=200
```

## 3. Technické detaily
- **Zdrojová složka:** `storage/app/defaults/avatars/`
- **Cílová složka (veřejná):** `public/uploads/defaults/{media_id}/` (řízeno přes `CustomPathGenerator`).
- **Filtrace:** Příkaz automaticky přeskakuje složky `thumbs/` a zpracovává pouze běžné formáty obrázků (`jpg`, `png`, `webp`).
- **Metadata:** Vytváří záznamy v tabulce `media_assets` s `uploaded_by_id = null`, což v aplikaci indikuje systémový (výchozí) obsah.

## 4. Troubleshooting
Pokud se synchronizace "zasekne" (často kolem 55 % při velkém množství souborů), je to obvykle způsobeno timeoutem serveru nebo PHP při generování konverzí (webp, thumb).

**Doporučený postup při zaseknutí:**
1. Spusťte příkaz s `--limit=100` a sledujte logy, abyste identifikovali, u kterého souboru k problému dochází.
2. Pokud jde o timeout, pokračujte s nastavením `--offset` pro dokončení zbývajících souborů.
3. Pokud jde o poškozený soubor, opravte jej nebo odstraňte ze `storage` a spusťte znovu.

## 5. Správa administrátory (UI)

Od března 2026 mohou administrátoři spravovat klubovou galerii avatarů přímo v uživatelském rozhraní prostřednictvím `AvatarModal` (v profilu člena nebo v administraci uživatelů).

### Funkce pro administrátory:
- **Hromadný import:** Administrátoři mohou nahrát více souborů najednou do klubové galerie. Při výběru více souborů se automaticky přeskočí krok s manuálním ořezem a všechny obrázky jsou centrálně oříznuty na čtverec, změněna velikost na 400x400 px a převedeny na `.webp`.
- **Automatická optimalizace:** Systém automaticky převede nahraný avatar na formát `.webp` a uloží jej do `public/uploads/defaults` s rozměry 400x400 px. Pro hromadný import se používá serverové zpracování pomocí GD knihovny.
- **Mazání z galerie:** V záložce "Klubová galerie" se administrátorům u každého obrázku zobrazuje ikona koše. Po potvrzení v modálu dojde k trvalému smazání souborů i složky daného avataru z disku.
- **Bezpečnost:** Akce jsou povoleny pouze uživatelům s oprávněním přístupu do administrace (`canAccessAdmin`).
