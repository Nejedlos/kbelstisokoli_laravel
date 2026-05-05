# Oprava mizení výsledků zápasů (Finální řešení) - 05.05.2026

## Popis problému
Uživatel opakovaně hlásil, že po automatické synchronizaci (cron) mizí z webu výsledky odehraných zápasů (skóre), ačkoliv tam dříve byly.

## Analýza
Při hloubkové analýze `MatchSyncService` byl identifikován chybný mechanismus v logice slučování duplicit:

1. **Scénář vzniku chyby:**
   - Zdroj (cz.basketball) mírně změní metadata zápasu (např. čas začátku o 5 minut nebo název haly).
   - Systém při synchronizaci seznamu zápasů (`syncMatchesList`) nenajde zápas podle `match_identity_key` (protože se změnil čas).
   - Vytvoří se nový záznam zápasu v DB, který zatím nemá skóre (seznam zápasů na webu občas skóre dočasně neobsahuje).
   - Na konci metody `sync` se zavolá `mergeDuplicatesForMatch`, která identifikuje starý záznam (se skóre) jako kandidáta na sloučení (shoda dne a soupeře).
   - **Kritická chyba:** Metoda `mergeDuplicatesForMatch` sice přesunula statistiky a pivoty, ale **nepřenesla skóre a status** ze starého záznamu na nový. Starý záznam pak smazala.
   - Výsledek: Zápas v DB zůstal, ale bez výsledku.

2. **Sekundární problém:**
   - Metoda `sync` sice inicializovala skóre z existujícího zápasu, ale neobsahovala explicitní ochranu proti jeho přepsání prázdnou hodnotou, pokud web v seznamu zápasů skóre dočasně vynechal.

## Provedené změny
1. **`MatchSyncService->mergeDuplicatesForMatch`**:
   - Přidán kód pro transfer `score_home`, `score_away` a `status` ze slučovaného záznamu na primární, pokud primární tyto hodnoty nemá.
   - Přidán transfer `scheduled_at` a `opponent_id` pro zajištění maximální integrity.

2. **`MatchSyncService->sync`**:
   - Posílena ochrana u odehraných zápasů (`finished`, `played`, `completed`). Pokud zdroj v seznamu zápasů neposkytne skóre, systém si vynutí ponechání stávajícího skóre z DB.
   - Přidáno logování těchto událostí (`Log::info`) pro snadnější audit.

## Verifikace
- **Obnova dat:** Spuštěn `stats:import --force` pro aktuální sezónu na produkci.
- **Výsledek:** Počet zápasů bez skóre v sezóně 2025/2026 klesl ze **46 na 4** (zbývající 4 jsou neoficiální turnaje bez externích ID).
- **Zátěžový test:** Opakovaný běh cronu potvrdil, že skóre u testovacího zápasu (ID 2259, 38:61) zůstalo zachováno i po synchronizaci seznamu.

## Dokumentace
Tato oprava navazuje na předchozí pokusy a definitivně řeší strukturální problém v logice `MatchSyncService`.
