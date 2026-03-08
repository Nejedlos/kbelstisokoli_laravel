# AI Nastavení (Uživatelská příručka)

Tato sekce v administraci (Nástroje -> AI Nastavení) slouží k ovládání umělé inteligence v rámci webu Kbelští sokoli.

## Hlavní Dashboard

V horní části vidíte rychlý přehled:
- **Status:** Zda je AI aktivní.
- **Provider:** Aktivní poskytovatel a zdroj konfigurace (DB vs ENV).
- **Výchozí Model:** Který model se aktuálně používá pro chat.
- **Debug Mode:** Indikace, zda se logují podrobné informace pro vývojáře.

## Záložky nastavení

### 1. Obecné
- **Aktivovat AI funkce:** Globální vypínač. Pokud je vypnuto, AI search a další funkce přestanou reagovat.
- **Používat nastavení z databáze:** Pokud zapnete, systém ignoruje `.env` soubor a bere vše z tohoto formuláře. **Doporučeno pro produkci.**

### 2. OpenAI / API
Zde nastavujete propojení s OpenAI.
- **API Key:** Váš tajný klíč. Pro zachování stávajícího klíče nechte pole prázdné.
- **Timeout:** Jak dlouho má systém čekat na odpověď AI.

### 3. Modely
Zde můžete měnit konkrétní modely pro různé účely:
- **Hlavní chat model:** Používá se pro běžné dotazy uživatelů. (Např. `gpt-4o-mini` pro rychlost a cenu, `gpt-4o` pro kvalitu).
- **Model pro analýzy:** Používá se pro složitější úkoly v pozadí.

### 4. Chování (Inference)
- **Temperature:** Ovlivňuje kreativitu. Nižší hodnota (např. 0.2) znamená přesnější a faktické odpovědi. Vyšší hodnota (0.8+) znamená kreativnější odpovědi.
- **Systémové prompty:** Zde definujete "osobnost" AI. Jak se má chovat a co o klubu ví.

### 5. Debug a Logy
Zde můžete sledovat, jak AI funguje.
- **Logovat do databáze:** Ukládá historii dotazů, kterou vidíte v dolní části stránky.
- **Retence logů:** Po kolika dnech se mají staré záznamy automaticky mazat.

## Nástroje a akce

- **Test připojení:** Ověří, zda je zadaný API klíč platný a systém se spojí s OpenAI.
- **Vymazat AI Cache:** Pokud AI odpovídá nesmysly nebo jste změnili kontextové informace, vymažte cache, aby AI vygenerovalo nové odpovědi.
