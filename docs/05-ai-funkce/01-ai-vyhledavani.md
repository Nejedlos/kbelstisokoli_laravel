# AI vyhledávání (Členská i Admin sekce)

Tento dokument popisuje implementaci AI vyhledávání v projektu Kbelští sokoli: architekturu, indexaci obsahu a způsob použití v UI.

## 1. Cíle
- Sjednocené vyhledávání (AI vlevo, standardní vpravo) v obou sekcích (Admin i Member).
- Nativní integrace do Filamentu: standardní globální input v administraci nyní využívá AI index (`AiGlobalSearchProvider`).
- Chytrá indexace: AI automaticky generuje synonyma a klíčová slova (např. "logo" najde Branding Settings).
- Inkrementální reindex: automatická synchronizace při změnách obsahu (podle checksumů).
- Možnost "AI obohacení" v systémové konzoli pro hloubkovou analýzu stránek.

## 2. Architektura (MVP RAG)
- Úložiště kontextu: tabulka `ai_documents`.
  - Sloupce: `type`, `source`, `title`, `url`, `locale`, `content`, `checksum`, timestamps.
  - Volitelný FULLTEXT index pro MySQL.
- Indexery (v `App\Services\AiIndexService`):
  - Blade views: `resources/views/member/**`, `resources/views/filament/**`.
  - Filament: Automatická extrakce navigace, Resources a Pages (včetně schémat formulářů).
  - Dokumentace: `docs/**.md`.
- Vyhledání kontextu: `LIKE` vyhledávání v `title`, `keywords` (AI generovaná synonyma) a `content` s váženým scoringem.
- Generování odpovědi: `App\Services\AiSearchService` (OpenAI Chat Completions) s vloženým lokálním kontextem.
- Integrace: `AiGlobalSearchProvider` registrovaný ve Filamentu pro nativní vyhledávání.

## 3. UI a UX
- Filament komponenta `resources/views/filament/components/ai-search.blade.php` nyní odesílá dotaz na `route('member.ai')`.
- Výsledková stránka AI: `resources/views/member/search/ai.blade.php`.
  - Zobrazuje odpověď AI a seznam použitých zdrojů (typ, název, soubor).
  - Klasické vyhledávání zůstává beze změny (`member.search`).

## 4. Konfigurace prostředí
V `.env` a `.env.example` jsou klíče:
```
OPENAI_API_KEY=
OPENAI_DEFAULT_MODEL=gpt-4o-mini
OPENAI_ANALYZE_MODEL=gpt-4o
OPENAI_BASE_URL=https://api.openai.com/v1
OPENAI_TIMEOUT=90
OPENAI_CACHE_TTL=0
```
- `OPENAI_CACHE_TTL` (sekundy): pokud > 0, odpovědi AI se krátce cachují pro rychlost.

## 5. Instalace a migrace
- Spusťte migraci tabulky `ai_documents`:
```
php artisan migrate --force --no-interaction
```
- Proveďte první indexaci (viz níže).

## 6. Indexace obsahu
- Inkrementální update (výchozí - indexuje frontend, member a admin, vynechává dokumentaci):
```
php artisan ai:index --locale=cs
```
- Indexace konkrétní sekce (frontend/member/admin/documentation):
```
php artisan ai:index --section=documentation
```
- Full reindex (smazání starých):
```
php artisan ai:index --locale=all --fresh
```
- AI obohacení (generování synonym/keywords):
```
php artisan ai:index --enrich
```
- Co se indexuje:
  - Filament: Navigace, Stránky, Resources (texty z formulářů a tabulek).
  - Member sekce: Stránky definované v routách.
  - Frontend: Veřejné stránky a aktuality.
  - Markdown: `docs/` – očištěný text (pouze při explicitním vyžádání sekce `documentation`).

## 7. Použití (uživatel)
- V horní liště klikněte na AI pole (ikona se „sparkles“) nebo využijte overlay v globálním vyhledávání.
- Po odeslání dotazu budete přesměrováni na stránku `Členská sekce > AI vyhledávání`, kde uvidíte odpověď a použitý kontext.

## 8. Nasazení (doporučení)
- Do nasazovací sekvence přidejte po migraci spuštění indexace:
```
php artisan ai:index --locale=cs --no-interaction
```
- Pokud používáte více jazyků, spusťte indexaci pro každý jazyk.

## 9. Bezpečnost a limity
- AI pracuje jen s lokálním, veřejně dostupným (v projektu) kontextem. Neodesíláme interní tajné klíče.
- Pokud se odpověď nedaří vygenerovat (např. vypršel limit), uživatel je vyzván k přeformulování dotazu.

## 10. Použité příkazy (Non-interactive Workflow)
- Migrace: `php artisan migrate --force --no-interaction`
- Indexace: `php artisan ai:index --locale=all --enrich --no-interaction`
- Systémová konzole: Akce "AI: Reindexace" v sekci "🧠 AI & Vyhledávání".

## 11. Vizuální design (v2.0)
- Stránka AI Search byla kompletně přepracována pro dosažení moderního a "fres" vzhledu.
- **Vylepšení:**
  - Šířka kontejneru zvětšena na `max-w-5xl`.
  - Implementován "Chat-like" interface s výrazně zaoblenými bublinami zpráv (`rounded-[2rem]`).
  - Vstupní pole využívá **glassmorphism** efekt (`backdrop-blur-xl`) a gradientní záři.
  - Vylepšený "Empty state" s rychlými tipy pro dotazy.
  - Zdroje informací (kontext) jsou zobrazeny v čistém gridu s ikonami a interaktivními hover stavy.
  - Plynulé animace (`animate-in`, `fade-in`, `slide-in`) pro lepší pocit z odezvy.
  - **Interaktivní chat:** Podpora pro kontinuální konverzaci, automatické odrolování na konec chatu a živá aktualizace stavu tlačítka při psaní.

## 13. Frontend vyhledávání
Od verze 1.2 je AI vyhledávání integrováno i do veřejné části webu (frontend).
- **Kontext:** Hledání na frontendu je striktně odděleno od admin/member sekce (`context => 'frontend'`).
- **Indexované zdroje:**
  - Veřejné stránky (`Page`) – indexuje se titulek a obsah (včetně bloků z page builderu).
  - Aktuality (`Post`) – indexuje se titulek, perex a obsah.
- **Vlastnosti:**
  - Plná podpora lokalizace (hledá se v jazyce aktuálně nastaveném na frontendu).
  - Výsledky obsahují náhledy (snippets) a u aktualit i náhledové obrázky.
  - Využívá stejný scoringový algoritmus jako admin vyhledávání (shoda v titulku > klíčová slova > obsah).

## 14. Další kroky
- Přidat indexer pro Eloquent modely a generovat smysluplné URL ke zdrojům.
- Přidat Filament administrativní akci „Rebuild AI index“ dostupnou pouze adminům.
