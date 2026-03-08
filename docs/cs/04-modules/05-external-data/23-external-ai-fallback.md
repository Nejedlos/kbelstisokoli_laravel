# AI Fallback a Normalizace (OpenAI)

Tento dokument popisuje mechanismus AI fallbacku pro parsování externích sportovních dat, který se využívá v případě, že standardní DOM extraktory selžou nebo se změní struktura zdrojového webu.

## 1. Účel AI Normalizeru
AI Normalizer (`OpenAiNormalizer`) slouží jako robustní "záchranná síť". Zatímco DOM extraktory jsou rychlé a levné, jsou náchylné na sebemenší změny v HTML (změna ID, tříd, struktury tabulky). AI (GPT-4o) dokáže pochopit sémantický význam dat v HTML fragmentu a transformovat je do požadovaného formátu bez ohledu na konkrétní tagy.

## 2. Kdy se AI použije
Mechanismus je navržen pro zapojení v orchestraci importu:
1. Pokus o stažení a extrakci pomocí **DOM Extractoru**.
2. Pokud DOM extractor vyhodí výjimku nebo nevrátí žádná data (např. nenajde tabulku), přepne se na **AI Normalizer**.
3. Výsledek z AI je označen v metadatech (`ai_normalized: true`), aby bylo možné v audit logu dohledat, kde k fallbacku došlo.

## 3. Konfigurace a Promptování
AI Normalizer vyžaduje `mapping_config`, který definuje:
- **Type:** Typ tabulky (`roster`, `matches_list`, `match_boxscore`).
- **Canonical Keys:** Seznam klíčů, do kterých se mají data namapovat (např. `pts` pro body, `fg3` pro trojky).

### Pravidla promptu:
- **Žádné halucinace:** Pokud hodnota v HTML není, AI musí vrátit `null` a přidat varování do pole `warnings`.
- **Stabilní ID:** AI se pokouší extrahovat externí ID hráče z odkazů typu `/hrac/{id}`.
- **JSON Output:** Výstup je vynucen jako JSON objekt s fixní strukturou (`columns`, `rows`, `warnings`).

## 4. Struktura výstupu (JSON)
AI vrací data v tomto formátu, který je následně mapován na `NormalizedTableDTO`:

```json
{
  "name": "Název tabulky",
  "columns": [
    {"key": "pts", "label": "Body"}
  ],
  "rows": [
    {
      "player_external_id": "11246",
      "player_name": "Marek Novotný",
      "values": {
        "pts": 12,
        "fouls": 3
      }
    }
  ],
  "warnings": []
}
```

## 5. Debugování chyb
Pokud AI parsování selže:
- **Logování:** Chyba je zaznamenána v Laravel logu (`OpenAi Normalizer Exception`).
- **Audit Run:** V tabulce `external_import_runs` je status nastaven na `failed` nebo `partial_failed`.
- **Snapshoty:** K dispozici je původní HTML fragment a v metadatech běhu se ukládají případná varování přímo od AI.

## 6. Lokální testování
Pro otestování AI normalizeru bez reálného webu lze použít uložené fixtures v testech:
```bash
php artisan test --filter=OpenAiNormalizerTest
```
*(Poznámka: Vyžaduje nastavený `OPENAI_API_KEY` v `.env`)*
