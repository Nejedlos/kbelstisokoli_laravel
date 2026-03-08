# Pravidla pro DOM extrakci dat z cz.basketball

Tento dokument definuje exaktní pravidla pro extrakci dat pomocí XPath a DomCrawleru pro web cz.basketball.

## 1. Týmová stránka (`/tym/{teamId}?y={year}`)

### Hlavička (Header)
- **Název týmu:** `//h1[1]`
- **Klub:** `//*[normalize-space(.)='Klub']/following::*[1]`
- **Kategorie:** `//*[normalize-space(.)='Kategorie']/following::*[1]`
- **Soutěž:** `//*[normalize-space(.)='Soutěž']/following::*[1]`

### Tabulka hráčů (Soupiska / Roster)
Identifikace pomocí XPath:
```xpath
//table[.//tr[1]//*[self::th or self::td][contains(normalize-space(.),'Hráč')]
  and .//tr[1]//*[contains(normalize-space(.),'Rok narození')]
  and .//tr[1]//*[normalize-space(.)='Z' or contains(normalize-space(.),' Z ')]
  and .//tr[1]//*[contains(normalize-space(.),'Min')]
  and .//tr[1]//*[normalize-space(.)='B' or contains(normalize-space(.),' B ')]
  and .//tr[1]//*[contains(normalize-space(.),'TH %')]
]
```
Podmínka: Tabulka musí obsahovat alespoň 3 odkazy typu `/hrac/{id}`.

### Tabulka zápasů (Schedule / Results)
Identifikace pomocí XPath:
```xpath
//table[
  .//tr[1]//*[contains(normalize-space(.),'Číslo utkání')]
  and .//tr[1]//*[contains(normalize-space(.),'Domácí/hosté')]
  and .//tr[1]//*[contains(normalize-space(.),'Datum')]
  and .//tr[1]//*[contains(normalize-space(.),'Soupeř')]
  and .//tr[1]//*[contains(normalize-space(.),'Skóre')]
  and .//tr[1]//*[contains(normalize-space(.),'TH %')]
]
```

## 2. Seznam zápasů (`/zapasy?c={teamId}&y={year}`)
- Vybere se tabulka s největším počtem odkazů na `/zapas/`.
- Threshold pro validitu je alespoň 5 odkazů.

## 3. Detail zápasu (`/zapas/{id}`)

### Hlavička zápasu
- **Skóre:** `.fi-match-header__score`
- **Týmy:** `.fi-match-header__team-name`
- **Detaily (datum, hala):** `.fi-match-header__details-item`

### Boxscore
- Hledá se sekce "Boxscore".
- Pro každý tým (označený `h4`) se vyhledá nejbližší následující tabulka s hlavičkou obsahující "Hráč".
- Ignorují se řádky "Tým/trenéři" a "Celkem".

## 4. Stránka hráče (`/hrac/{id}`)

### Tabulka kariéry (Career)
Identifikace hlavičky: `Sezona`, `Tým`, `Zápasy`, `B`, `TH %`.

### Seznam zápasů (Per-game)
Identifikace hlavičky: `Datum`, `Fáze sezóny`, `Soupeř`, `B`, `TH %`.

### Souhrn dle soupeřů (Opponent summary)
Identifikace hlavičky: `Soupeř`, `Z`, `B`.

## 5. Validace a Invarianty
- Týmová soupiska: alespoň 3 řádky, alespoň 1 odkaz na hráče.
- Boxscore zápasu: alespoň 1 tým s 5+ hráči.
- Detekce sezóny: kontrola textu "2025/26" pro rok 2025.
