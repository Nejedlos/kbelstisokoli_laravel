# Předzápasová predikce (Match Prediction)

Tento dokument popisuje implementaci, matematický model a způsob fungování systému předzápasových predikcí v projektu Kbelští sokoli.

## 1. Účel modulu
Cílem modulu je poskytnout členům klubu v členské sekci fundovaný odhad šance na výhru v nadcházejícím zápase. Predikce je založena na historických datech, aktuální formě a síle soupisky, nikoliv na náhodných generátorech.

## 2. Matematický model (Elo + Form + Roster)

Predikce kombinuje tři hlavní složky pomocí váženého logit mixu:

### A) Elo Rating (60 % váha)
Dlouhodobá síla týmu. 
- Každý tým (náš i soupeř) začíná na 1500 Elo bodech na začátku sezóny.
- Po každém odehraném zápase se Elo aktualizuje:
  `expected = 1 / (1 + 10^((elo_opp - elo_team + home_adv)/400))`
- **Home Advantage:** Domácí tým dostává bonus +50 Elo bodů.
- **Margin of Victory:** Výpočet zohledňuje bodový rozdíl (výhra o 30 bodů zvýší Elo více než výhra o 2 body).
- **K-faktor:** 30 (pro < 10 zápasů), 20 (standard), 15 (pro > 30 zápasů).

### B) Aktuální forma (25 % váha)
Krátkodobá výkonnost v posledních 5 zápasech.
- `form_delta = clamp((win_rate * 5 - 2.5) * 20 + (avg_diff * 2), -60, +60)`
- Zohledňuje počet výher a průměrný bodový rozdíl.

### C) Síla soupisky (15 % váha)
Aktuální přínos hráčů na základě boxscore.
- Vypočítá se průměr bodů (PTS) pro TOP 8 hráčů z dosud odehraných zápasů sezóny.
- Pokud u soupeře chybí data pro hráče, váha této složky se vynuluje a přerozdělí mezi Elo a Formu.

### Finální mix
Pravděpodobnosti jsou převedeny na logity, smíchány podle vah a převedeny zpět na výsledné procento pomocí `invLogit` funkce.

## 3. Confidence Score (Úroveň důvěry)
- **High:** Máme odehráno >= 10 zápasů v sezóně a známe data soupeře.
- **Medium:** Máme odehráno >= 5 zápasů.
- **Low:** Méně než 5 zápasů v sezóně nebo chybějící data o soupeři.

## 4. Technická implementace

### Databázové tabulky
- `team_elo_ratings`: Ukládá aktuální Elo hodnocení týmů v rámci sezóny.
- `match_predictions`: Ukládá vypočítané predikce, faktory a slovní vysvětlení.

### Proces výpočtu
1. **Trigger:** `MatchPredictionObserver` zachytí změnu v modelu `BasketballMatch` (např. vytvoření zápasu, zadání výsledku) nebo `StatisticRow` (nahrání boxscore).
2. **Job:** Spustí se `ComputeMatchPredictionJob` asynchronně přes frontu.
3. **Service:** `PredictionService` provede matematický výpočet a uloží výsledek.

### Artisan příkazy
Pro přepočet historie Elo ratingů (např. po opravě starých výsledků) použijte:
```bash
php artisan stats:elo:recompute {season?} {team?}
```

## 5. UI Komponenta
Predikce se zobrazuje v detailu zápasu v členské sekci (`/clenska-sekce/statistiky/zapasy/{id}`). 
Obsahuje:
- Velké procentuální vyjádření šance na výhru.
- Barevný progress bar (gradient z brand barev).
- Slovní body "Proč si to myslíme" generované dynamicky z dat.
- Collapsible sekci s metodikou.

## 6. Budoucí rozvoj
Model je připraven na přidání dalších faktorů (vážení zranění, dojezdová vzdálenost, čas zápasu) úpravou `PredictionService` a přidáním nového kalkulátoru.
