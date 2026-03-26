# AI Search Indexer (Artisan & System Console)

Tento dokument popisuje funkčnost příkazu pro kompletní reindexaci vyhledávacího indexu (AI Search).

## 1. Účel modulu
Příkaz `ai:index` slouží k hromadnému sestavení nebo obnově vyhledávacího indexu. Prochází různé části webu (frontend, členská sekce, administrace, dokumentace) a ukládá jejich obsah do tabulky `ai_documents`.

## 2. Technický popis
Indexace probíhá ve dvou fázích:
1.  **Sběr dat:** `AiIndexService` prochází definované zdroje (modely, soubory, routy), extrahuje z nich text a ukládá checksumy pro detekci změn.
2.  **Obohacení (volitelné):** Pokud je použit příznak `--enrich`, jsou dokumenty odeslány do OpenAI API pro vygenerování sémantických shrnutí, klíčových slov a dotazů.

## 3. Způsob použití

### Artisan Command
Příkaz lze spustit z terminálu:
```bash
php artisan ai:index [options]
```

**Dostupné volby:**
-   `--fresh`: Smaže stávající index před začátkem (pro daný jazyk/sekci).
-   `--enrich`: Provede AI obohacení (vyžaduje OpenAI API klíč v `.env`).
-   `--locale=all|cs|en`: Omezí indexaci na konkrétní jazyk (výchozí: `all`).
-   `--section=all|frontend|member|admin|documentation|help`: Omezí indexaci na konkrétní sekci.
-   `--force`: Vynutí reindexaci i v případě, že se obsah dokumentu nezměnil (ignoruje checksum).

### Administrace (System Console)
Příkaz je integrovaný do **System Console** ve Filamentu pod skupinou **🧠 AI & Vyhledávání**.
Zde lze pohodlně naklikat potřebné parametry a sledovat průběh v reálném čase v terminálu.

Doporučuje se používat **Internal Execution** pro přímé spuštění v rámci PHP procesu, což je stabilnější na některých hostinzích (např. Webglobe), kde CLI PHP může mít jinou konfiguraci.

## 4. Implementované sekce
-   **Frontend:** Stránky a aktuality.
-   **Member:** Hlavní routy členské sekce.
-   **Admin:** Filament resources, custom stránky a Photo Pooly.
-   **Documentation:** Markdown soubory v adresáři `docs/`.
-   **Help:** Markdown soubory nápovědy v `docs/help/`.
