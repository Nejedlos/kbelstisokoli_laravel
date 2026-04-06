# Laravel 13 AI SDK Migration Design - Kbelští sokoli

Tento dokument definuje strategii migrace stávající AI vrstvy na nativní **Laravel AI SDK** v Laravelu 13. Cílem je nahradit vlastní implementaci standardizovaným řešením při zachování veškeré business logiky.

## 🏁 Současný stav vs. Cílový stav

| Oblast | Současný stav (L12) | Cílový stav (L13 AI SDK) |
| :--- | :--- | :--- |
| **Orchestrace (Chat)** | `AiSearchService` (vlastní volání API) | `ClubAssistantAgent` (extends `Agent`) |
| **Strukturovaný výstup** | Ruční JSON Schema a prompt v `AiTextEnhancer` | `Schema` definice a `StructuredOutput` |
| **Vyhledávání kontextu** | `AiIndexService->search` (heuristika LIKE) | `VectorStore` (vektorové hledání přes Embeddings) |
| **Nástroje (RAG)** | Skládání promptů v `AiSearchService` | `SearchTool` registrovaný pro Agenta |
| **Historie konverzací** | Ruční předávání `array $history` | Nativní `Thread` / `Conversation` storage |
| **Konfigurace** | `AiSettingsService` (DB + Config) | `AI Provider` konfigurace s využitím `SettingsService` |

## 🏗️ Navržená cílová struktura (app/Ai)

Stávající rozptýlené služby v `app/Services` budou konsolidovány do nového jmenného prostoru `App\Ai`:

- `app/Ai/Agents/` – Logika agentů (např. `ClubAssistantAgent`).
- `app/Ai/Tools/` – Nástroje pro agenty (např. `SearchClubDocsTool`).
- `app/Ai/Schemas/` – Definice struktur pro AI (např. `PhotoPoolMetadataSchema`).
- `app/Ai/Actions/` – Doménové AI operace (např. `NormalizeStatsAction`, `EnhanceTextAction`).
- `app/Ai/DTO/` – Datové přenosové objekty pro AI vstupy/výstupy.
- `app/Ai/Enums/` – Výčty pro modely, role a typy AI úloh.
- `app/Ai/Concerns/` – Společné traity (např. `HasAiLogging`).

## 🗺️ Mapování stávajících tříd

| Stávající třída | Nová role / Umístění | Poznámka |
| :--- | :--- | :--- |
| `AiSearchService` | `App\Ai\Agents\ClubAssistantAgent` | Převezme logiku RAG a orchestraci. |
| `AiIndexService` | `App\Ai\Support\VectorIndexer` | Bude volat nativní `Embedding` API pro indexaci chunks. |
| `AiTextEnhancer` | `App\Ai\Actions\EnhanceMetadata` | Bude využívat `app/Ai/Schemas`. |
| `OpenAiNormalizer` | `App\Ai\Actions\NormalizeStatsTable` | Bude využívat `app/Ai/Schemas/StatsSchema`. |
| `AiSettingsService` | `App\Ai\Support\AiConfigManager` | Zůstane jako most mezi DB nastavením a AI SDK Providers. |

## 🚀 Migrační strategie

### 1. Postupná migrace (Feature Flags)
V `config/ai.php` zavedeme přepínač:
```php
'use_sdk' => env('AI_USE_SDK', false),
```
Stávající služby budou refaktorovány tak, aby interně volaly buď starou logiku, nebo novou přes SDK, podle stavu tohoto příznaku.

### 2. Pořadí migrace
1. **Infrastruktura:** Registrace OpenAI providera v Laravel AI SDK.
2. **Strukturované výstupy:** Migrace `AiTextEnhancer` (nejmenší riziko).
3. **Embeddings:** Implementace vektorového indexu pro `ai_chunks`.
4. **Agenti:** Implementace `ClubAssistantAgent` a nahrazení `AiSearchService->chat`.
5. **UI:** Přepojení Filament a Livewire komponent na nové agenty.

### 3. Fallback strategie
Pokud selže nativní SDK (např. nekompatibilita modelu), systém se automaticky přepne zpět na `LegacyAiClient` (původní Http volání).

## 🧪 Testovací strategie

- **Unit Testy:** Testování `Schema` validací bez nutnosti volání LLM.
- **Integration Testy:** Využití `Ai::fake()` pro simulaci odpovědí agentů.
- **Regression Tests:** Porovnání výstupu původního `OpenAiNormalizer` s novou `NormalizeStatsAction` nad sadou reálných HTML fragmentů.
- **Observability:** Využití `AiRequestLog` pro monitoring latence a tokenů u nových SDK volání.

## 🛡️ Rollback plán
V případě kritického selhání AI SDK na produkci:
1. Nastavit `AI_USE_SDK=false` v `.env`.
2. Provést `optimize:clear`.
3. Systém se okamžitě vrátí k původním service třídám v `app/Services/`.

## 🔍 Místa pro ruční validaci výsledků (QA)

Během migrace je nutná ruční kontrola v těchto klíčových oblastech:
- **Filament AI Search:** Ověření, že asistent stále správně odkazuje na URL v administraci.
- **Photo Pool Metadata:** Kontrola bilingvních návrhů (CS/EN), zda nedochází k halucinacím v překladu.
- **Import statistik:** Verifikace, že `NormalizeStatsAction` správně mapuje atypické HTML tabulky (např. s `rowspan`).
- **Help Center (Členská sekce):** Testování, zda AI asistent správně interpretuje oprávnění uživatele (zda neradí věci, ke kterým uživatel nemá přístup).

## 🍰 Implementation Slices

Migrace bude rozdělena do 5 bezpečných kroků (slices):

### Slice 1: SDK Base & Config
- Instalace/příprava Laravel AI SDK (pokud není součástí core).
- Konfigurace v `AppServiceProvider` nebo `AiServiceProvider`.
- Zprovoznění základního volání `Ai::generate()` pro test spojení.

### Slice 2: Metadata & Stats (Structured Output)
- Vytvoření `app/Ai/Schemas/` pro PhotoPool a Eventy.
- Implementace `EnhanceMetadataAction`.
- Náhrada metod v `AiTextEnhancer`.

### Slice 3: Vectorization (Embeddings)
- Přechod `AiIndexService` na nativní `Embedding` SDK.
- Doplnění sloupce `embedding` (vector) do tabulky `ai_chunks`.
- Implementace `AiVectorStore` (wrapper nad Eloquentem s podporou Cosine Similarity).

### Slice 4: The Agent (RAG)
- Vytvoření `ClubAssistantAgent`.
- Implementace `SearchTool` pro přístup k indexu.
- Migrace system promptů z `AiSearchService`.

### Slice 5: Cleanup & Deprecation
- Odstranění starých metod a tříd v `app/Services/`.
- Finalizace dokumentace pro vývojáře v `docs/cs/01-general/06-ai-sdk.md`.
