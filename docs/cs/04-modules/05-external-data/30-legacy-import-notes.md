# Poznámky k importu historických statistik (Legacy Import)

Tento dokument popisuje technické detaily a strategii použitou pro import historických dat z HTML souborů uložených v `storage/app/legacystats`.

## 1. Klasifikace souborů a kódování

Systém automaticky detekuje typ souboru a sezónu podle názvu a obsahu:

- **Encoding:**
    - Soubory `sokoli_statistiky_*.htm` jsou detekovány jako **Windows-1250** a automaticky převedeny do UTF-8.
    - Ostatní soubory (ČBF exporty) jsou zpracovány jako **UTF-8**.
- **Typy souborů:**
    - `mixed`: Obsahuje více tabulek (střelba hráčů, souhrn hráčů, zápasy).
    - `players_stats`: Statistiky hráčů (ČBF export).
    - `team_stats`: Statistiky družstva/zápasů (ČBF export).
    - `league_table`: Konečné tabulky soutěží.

## 2. Strategie extrakce (DOM/XPath)

Pro parsování je použit `LegacyStatExtractor`, který řeší specifika historických HTML:

- **Hlavičky v datech:** Pokud tabulka nemá `<thead>`, systém bere první řádek dat jako záhlaví.
- **Ignorování balastu:** Jsou automaticky přeskakovány navigační tabulky ("detaily, seznam hráčů..."), rozpisy kol a prázdné tabulky.
- **Více tabulek:** Jeden soubor může obsahovat více datových sad (např. střelba a souhrn), které jsou rozděleny do samostatných `StatisticSet`.

## 3. Canonical Mapping a parsování hodnot

Hodnoty jsou normalizovány do sjednocených klíčů:

- **Střelba (2b, 3b, TH):**
    - Sloupec `TH` (např. "47/37") je rozdělen na `ft_att` (47) a `ft_made` (37).
    - Úspěšnost je uložena jako `ft_percent`.
- **Souhrn (GP, Fouls, Val, Points):**
    - Rozlišuje se mezi celkovými hodnotami (`pts`, `fouls`) a průměry na zápas (`pts_pg`, `fouls_pg`) podle pozice v tabulce.
- **Konečná tabulka:**
    - Mapuje pozice (Rank, Tým, Z, V, P, Skóre, Body).

## 4. Idempotence a perzistence

- **Content Hash:** Každý řádek má v `source_metadata` uložen SHA256 hash svých hodnot.
- **Re-run:** Při opakovaném spuštění importu pro stejný soubor jsou staré řádky smazány a nahrazeny novými (idempotence zajištěna).
- **Ghost Players:** Hráči, kteří nejsou v interní databázi, jsou uloženi pod `row_label`.

## 5. Výsledky importu (Leden 2026)

- Celkem zpracováno **41 souborů**.
- Vytvořeno **43 statistických sad**.
- Naimportováno **1083 datových řádků**.
- Sezóny pokryty v rozsahu **2010/2011 až 2020/2021**.

## 6. Jak spustit import znovu

Pro hromadný import z CLI lze použít:
```bash
php artisan legacy:import-batch {batch_id} --sync
```
Nebo přes administraci v sekci **Legacy Stats Import**.
