# Předzápasová predikce (Match Prediction)

Tento dokument popisuje implementaci, matematický model a způsob fungování systému předzápasových predikcí v projektu Kbelští sokoli.

## 1. Účel modulu
Cílem modulu je poskytnout členům klubu v členské sekci fundovaný odhad šance na výhru v nadcházejícím zápase. Predikce je založena na historických datech, aktuální formě a síle soupisky, nikoliv na náhodných generátorech.

## 2. Matematický model (Elo + Form + Roster)

Predikce kombinuje pět složek pomocí váženého logit mixu:

### A) Elo Rating (40 % váha)
Dlouhodobá síla týmu. 
- Každý tým (náš i soupeř) začíná na 1500 Elo bodech na začátku sezóny.
- Po každém odehraném zápase se Elo aktualizuje:
  `expected = 1 / (1 + 10^((elo_opp - elo_team - home_adv)/800))`
- **Home Advantage:** Domácí tým dostává bonus +50 Elo bodů; při utkání venku je výhoda na straně soupeře (−50). Všechny složky používají stejnou stupnici a stejnou domácí výhodu.
- **Margin of Victory:** Výpočet zohledňuje bodový rozdíl (výhra o 30 bodů zvýší Elo více než výhra o 2 body).
- **K-faktor:** 30 (pro < 10 zápasů), 20 (standard), 15 (pro > 30 zápasů).

### B) Aktuální forma (15 % váha)
Krátkodobá výkonnost v posledních 5 zápasech.
- `form_delta = clamp((win_rate * 5 - 2.5) * 20 + (avg_diff * 2), -60, +60)`
- Zohledňuje počet výher a průměrný bodový rozdíl.

### C) Síla soupisky (15 % váha)
Aktuální přínos hráčů na základě boxscore.
- Vypočítá se průměr bodů (PTS) pro TOP 8 hráčů z dosud odehraných zápasů sezóny.
- Pokud na kterékoli straně chybí data hráčů, váha této složky se přerozdělí mezi zbývající dostupné složky.

### D) Externí srovnání (15 % váha)
Průměrné body a úspěšnost dvoubodové střelby ze zdroje ČBF. Neúplné a nečíselné hodnoty (`-`, prázdný údaj) se ignorují, nikoli převádějí na nulu.

### E) Vzájemné zápasy (15 % váha)
Bilance a rozdíl skóre z dostupné historie. Strana našeho týmu se určuje podle oficiálního názvu ze zdroje, ne podle interního zobrazovaného názvu. Neznámá nebo nejednoznačná identita ani chybějící skóre se nezapočítají jako prohra.

### Finální mix
Pravděpodobnosti jsou převedeny na logity, smíchány podle vah a převedeny zpět na výsledné procento pomocí `invLogit` funkce. Výsledek se konzervativně mapuje do rozsahu 10–90 %. Chybějící srovnání a historie mají nulovou váhu, zbývající váhy se normalizují. Nízká důvěra značí nedostatek dat, nikoli automaticky nízkou šanci na výhru.

## 3. Confidence Score (Úroveň důvěry)
- **High:** Máme odehráno >= 10 zápasů v sezóně a známe data soupeře.
- **Medium:** Máme odehráno >= 5 zápasů.
- **Low:** Méně než 5 zápasů v sezóně nebo chybějící data o soupeři.

## 4. Technická implementace

### Databázové tabulky
- `team_elo_ratings`: Ukládá aktuální Elo hodnocení týmů v rámci sezóny.
- `match_predictions`: Ukládá vypočítané predikce, faktory a slovní vysvětlení.
- Predikce a Elo se počítají jen pro zápasy s jednoznačně vyplněným interním
  `team_id` a `opponent_id`. Vícetýmové zápasy bez primárního týmu se bezpečně
  přeskočí, protože jednu predikci nelze přiřadit více týmům současně.

### Proces výpočtu
1. **Trigger (web i konzole/fronta):** `MatchPredictionObserver` zachytí změnu v modelu `BasketballMatch` (např. vytvoření zápasu, zadání výsledku) nebo `StatisticRow` (nahrání boxscore).
2. **Job:** Spustí se `ComputeMatchPredictionJob` asynchronně přes frontu.
3. **Service:** `PredictionService` provede matematický výpočet a uloží výsledek (`elo+form+roster+preview+h2h:v3`). U odehraného zápasu zachová existující předzápasový snímek; pokud chybí, nevytváří zpětně předzápasový odhad s pozdějšími daty. Změna týmu, soupeře, sezóny či strany utkání zneplatní původní predikci.
4. **Elo:** Po změně výsledku, stavu, termínu nebo kontextu odehraného utkání a po jeho smazání se celá sezóna přehraje od 1500 v pořadí termín/ID. Tím se oprava výsledku nezapočte dvakrát. Zahrnují se jen dokončené zápasy se skóre, nikoli plánované zástupné hodnoty 0:0; výsledek 20:0 je platný. Přepočty stejné sezóny se serializují transakcí se zámkem sezóny.
5. Po dokončení přepočtu Elo se do fronty zařadí nové predikce pro plánované zápasy sezóny, včetně ostatních týmů se společnými soupeři.

### Artisan příkazy
Pro přepočet historie Elo ratingů (např. po opravě starých výsledků) použijte:
```bash
php artisan stats:elo:recompute {season?} {team?}
```

Filtr `team` vybírá sezóny; každá vybraná sezóna se přepočítá kompletně, aby se zachovala historie společných soupeřů. Opakované spuštění má stejný výsledek.

Po nasazení opravy modelu spusťte `php artisan stats:elo:recompute` a `php artisan stats:predictions:recompute` a ověřte dokončení úloh ve frontě. Přepočet Elo sám zařadí plánované zápasy do fronty; druhý příkaz je vhodný i pro samostatnou obnovu predikcí. `--all` zachovává snímky odehraných utkání. Samotné zařazení úloh nepotvrzuje dokončený výpočet.

## 5. UI Komponenta
Číslo vždy označuje šanci interního týmu `team_id`, i při utkání venku. Hodnota se ukládá v rozsahu 0–1 a všechny šablony ji převádějí na procenta násobením 100. Kontrolovaná místa: program/dashboard (Livewire EventCard), docházka/detail akce, modal, členské statistiky/detail zápasu, veřejné karty a veřejný detail. Modal řadí náš tým první a přiřazuje správná data domácích/hostů podle `is_home`.

Predikce se zobrazuje v detailu zápasu v členské sekci (`/clenska-sekce/statistiky/zapasy/{id}`). 
Obsahuje:
- Velké procentuální vyjádření šance na výhru.
- Barevný progress bar (gradient z brand barev).
- Slovní body "Proč si to myslíme" generované dynamicky z dat.
- Collapsible sekci s metodikou.

## 6. Budoucí rozvoj
Model je připraven na přidání dalších faktorů (vážení zranění, dojezdová vzdálenost, čas zápasu) úpravou `PredictionService` a přidáním nového kalkulátoru.

## 7. Jednorázová produkční obnova stávajících predikcí

Workflow **Repair production match predictions** se spouští ručně z `main` po úspěšném nasazení stejného commitu. Sdílí zámek deploymentu a používá produkční SSH secrets s povinným ověřením hostitele. Skript `scripts/repair-production-predictions.php` odmítne jiné prostředí nebo nesoulad `.release-sha`.

Před zápisem uloží soukromou zálohu původních `team_elo_ratings` a `match_predictions` do `storage/app/private/prediction-repairs/`. Poté v jedné transakci přehraje Elo a synchronně přepočítá všechny plánované zápasy s jednoznačným týmem a soupeřem bez skutečného výsledku. Během této obnovy potlačí pouze duplicitní joby predikcí. Ověří verzi v3, konečnost a rozsah pravděpodobností, jejich součet 1 a nezměněné historické snímky; nesoulad vrací celou databázovou změnu zpět. Výstup obsahuje počty a příklady procent před/po. Workflow předem požádá trvalé workery o restart.
