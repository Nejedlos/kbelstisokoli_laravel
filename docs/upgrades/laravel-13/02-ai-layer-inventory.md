# AI Layer Inventory - Kbelští sokoli

Tento dokument obsahuje detailní přehled AI komponent v projektu a jejich aktuální implementace.

## 🤖 Hlavní Služby (Services)

| Třída | Odpovědnost |
| :--- | :--- |
| `AiSettingsService` | Centrální správa nastavení (DB + Config fallback), logování requestů. |
| `AiSearchService` | Chat asistent, orchestrace kontextového vyhledávání, komunikace s OpenAI. |
| `AiIndexService` | Indexace obsahu (Filament, Frontend, Docs) do DB (`ai_documents`). |
| `AiTextEnhancer` | Bilingvní návrhy metadat pro Photo Pooly a akce (OpenAI JSON Schema). |
| `OpenAiNormalizer` | Extrakce a normalizace sportovních dat z HTML fragmentů (Structured Output). |

## 📦 Datový Model (Models)

- `AiDocument`: Ukládá zaindexované stránky a zdroje.
- `AiChunk`: Segmenty dokumentů pro sémantické vyhledávání.
- `AiSetting`: Globální konfigurace AI v databázi.
- `AiRequestLog`: Historie API volání na OpenAI (tokeny, latence, status).

## 🧩 Integrace v UI

- **Filament Page:** `AiSearch` (Vlastní stránka pro chat s asistentem v adminu).
- **Filament Page:** `AiSettings` (UI pro nastavení modelů, klíčů a promptů).
- **Global Search:** `AiGlobalSearchProvider` (Zahrnuje AI výsledky do horního vyhledávání).
- **Livewire Components:** `member.HelpCenter` (AI asistent v členské sekci).
- **Console Commands:** `ai:index`, `ai:reindex` (Správa indexu).

## 🔌 API Provider a Modely

- **Provider:** OpenAI (přes `Http` fasádu a `Guzzle` v `AiTextEnhancer`).
- **Modely:**
  - `gpt-4o-mini` (Default, Fast, Chat)
  - `gpt-4o` (Analyze)
  - `text-embedding-3-small` (Embeddings - připraveno, ale search nyní používá heuristické LIKE)

## 📊 Využití Structured Output

- **JSON Object:** Používáno v `AiSearchService` (nepřímo přes prompty), `AiIndexService` (pro sémantické obohacení), `OpenAiNormalizer`.
- **JSON Schema:** Používáno v `AiTextEnhancer` (`suggestPhotoPoolMetadata`) pro striktní validaci metadat.

## 💾 Databáze a Perzistence

- Data se ukládají do tabulek `ai_documents`, `ai_chunks`, `ai_settings`, `ai_request_logs`.
- Indexace probíhá "Render-then-Analyze" – systém si interně vyrenderuje stránku jako uživatel a výsledek zaindexuje.

## 🛠️ Environment Proměnné (ENV)

| Proměnná | Účel |
| :--- | :--- |
| `AI_ENABLED` | Globální vypínač AI funkcí. |
| `AI_USE_DATABASE_SETTINGS` | Priorita DB nastavení před configem. |
| `OPENAI_API_KEY` | Klíč k OpenAI API. |
| `OPENAI_DEFAULT_MODEL` | Výchozí model pro chat. |
| `OPENAI_ANALYZE_MODEL` | Model pro složité analýzy. |
| `OPENAI_EMBEDDINGS_MODEL` | Model pro tvorbu vektorů. |

## 🔄 Queue & Workflow

- Indexace je synchronní (v commandech), ale logování requestů je navrženo tak, aby neshodilo hlavní flow při selhání DB.
- Integrace s `laravel/scout` je připravena pro budoucí přechod na vektorovou databázi.
