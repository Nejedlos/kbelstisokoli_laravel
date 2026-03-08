# Statistiky: UX a Vizualizace

Tento dokument popisuje způsob prezentace statistických dat v uživatelském rozhraní (frontend a member sekce) se zaměřením na UX, interpretaci a moderní vizualizaci.

## 1. Architektura datových služeb

Pro efektivní zobrazení dat bez nutnosti runtime scrapingu se používají dvě hlavní služby:

- **`PlayerStatsService`**: Poskytuje data pro osobní dashboard hráče.
    - `getSeasonSummary`: Sezónní souhrn (GP, PTS, PPG, procenta).
    - `getPerGameSeries`: Časová řada zápas po zápase pro tabulky a grafy.
    - `getRankings`: Výpočet umístění hráče v rámci týmu (v bodech, minutách atd.).
    - `getInsights`: Automaticky generované "postřehy" (nejlepší zápas, aktuální forma, trend).
- **`TeamStatsService`**: Poskytuje data pro týmové přehledy.
    - `getPointsSeries`: Bodový vývoj týmu (vstřelené vs inkasované body).
    - `getRecentForm`: Posledních 5 zápasů (V/P).
    - `getTopScorers`: Seznam nejlepších střelců týmu.

## 2. Členská sekce (Member Dashboard)

Hráč má k dispozici interaktivní modul **"Moje statistiky"**, který je rozdělen na dva pohledy:

### A) Osobní přehled (Personal View)
- **Summary Cards**: Rychlý přehled klíčových metrik (Zápasy, Body, PPG, Minuty, % úspěšnosti).
- **Sezónní postřehy**: Textové interpretace dat (např. "Hraješ lépe než je tvůj průměr").
- **Týmový ranking**: Vizualizace postavení hráče v týmu (např. #3 v bodech).
- **Grafy (ApexCharts)**:
    - *Vývoj bodů*: Spojnicový graf ukazující stabilitu výkonů.
    - *Srovnání*: Sloupcový graf osobního PPG proti průměru celého týmu.
- **Zápisník výkonů**: Detailní tabulka všech zápasů v sezóně.

### B) Týmový přehled (Team View)
- Umožňuje hráči vidět detailní statistiky svého týmu, ale i **ostatních týmů** v klubu.
- Obsahuje bilanci V-P, průměrnou ofenzívu a vizuální indikátor formy.
- Seznam nejlepších střelců daného týmu.
- Plošný graf bodového vývoje (My vs Soupeři).

## 3. Veřejná část (Frontend)

Veřejné týmové stránky jsou optimalizovány pro **prezentaci** klubu:
- Jsou vybrány pouze metriky s vysokou vypovídající hodnotou pro fanoušky.
- Skrývají se technické detaily (např. pokusy/střely), pokud nejsou relevantní nebo kompletní.
- Hlavním prvkem je vizualizace "Bodová ofenzíva vs defenzíva", která ukazuje atraktivitu hry týmu.

## 4. Technická implementace vizualizací

- **Knihovna**: Používáme **ApexCharts.js**.
- **Reaktivita**: Grafy jsou integrovány s Livewire pomocí `statsLoaded` eventu a `@script` direktivy. Při změně sezóny nebo týmu se data v grafu plynule aktualizují bez refreshu stránky.
- **Mobile-First**: Všechny tabulky a karty jsou plně responzivní. Na mobilních zařízeních se méně důležité sloupce tabulek skrývají nebo se grafy přizpůsobují šířce displeje.
- **Empty States**: Pokud pro danou kombinaci data neexistují, zobrazuje se přehledný placeholder s informací pro uživatele.

## 5. Výpočet metrik a fallbacky

- **PPG (Points Per Game)**: Vypočteno jako `total_pts / gp`.
- **Rankings**: Počítají se v reálném čase z předpočítaných sezónních souhrnů všech hráčů v týmu.
- **Insights**: Používají jednoduchou logiku porovnávání průměrů posledních zápasů se sezónním průměrem pro detekci trendů.
- **W/L Balance**: Odvozuje se z výsledků v tabulce `matches` pro daný tým a sezónu.
