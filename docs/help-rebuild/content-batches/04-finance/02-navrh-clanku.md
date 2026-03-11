# Návrh článků - Batch 04: Finance

Na základě UI auditu navrhuji následující strukturu help článků pro finanční modul.

## 1. Seznam článků

| Název článku | Slug | Role | Účel |
| :--- | :--- | :--- | :--- |
| **Finanční systém klubu** | `financni-system-prehled` | admin, coach, parent, player | Vysvětlení pojmů Předpis, Platba a Alokace. |
| **Správa finančních tarifů** | `sprava-tarifu` | admin | Jak nastavit ceník příspěvků a dalších poplatků. |
| **Předpisy plateb (dluhy členů)** | `predpisy-plateb` | admin, coach | Jak zadat členovi povinnost zaplatit (příspěvky, soustředění). |
| **Evidence a příjem plateb** | `evidence-plateb` | admin | Jak zapsat přijatou platbu z banky nebo v hotovosti. |
| **Párování plateb (Alokace)** | `parovani-plateb` | admin | Jak propojit platbu s konkrétním předpisem. |
| **Moje platby a vyúčtování** | `moje-platby` | parent, player | Návod pro členskou sekci – jak zaplatit a kde najít VS. |

## 2. Detailní návrh článků

### A. Finanční systém klubu (`financni-system-prehled`)
- **Typ:** Onboarding / Koncepční
- **UI vazba:** Dashboard financí (v budoucnu), hlavní menu Finance.
- **Obsah:** Rozdíl mezi tím, co má člen zaplatit (Předpis) a co skutečně poslal (Platba). Proč je nutné tyto dvě věci "spárovat" (Alokovat).
- **Quick Actions:** Zobrazit moje finance, Seznam předpisů.

### B. Správa finančních tarifů (`sprava-tarifu`)
- **Typ:** Sekční / Detailní
- **UI vazba:** `FinancialTariffResource`
- **Obsah:** Definice tarifů (např. "Příspěvky Mladší žáci", "Soustředění léto"). Nastavení částky a jednotky (Měsíc/Sezóna).
- **FAQ:** Lze částku u konkrétního člena změnit? (Ano, přímo v předpisu).

### C. Předpisy plateb (`predpisy-plateb`)
- **Typ:** Sekční / Detailní
- **UI vazba:** `FinanceChargeResource`
- **Obsah:** Ruční vytvoření předpisu. Výběr člena, typu platby (příspěvek, kemp, turnaj) a nastavení splatnosti. Význam stavů (Draft vs. Open).
- **Rizika:** Pokud není předpis "Viditelný pro člena", hráč ho neuvidí ve svém profilu.

### D. Evidence a příjem plateb (`evidence-plateb`)
- **Typ:** Sekční / Detailní
- **UI vazba:** `FinancePaymentResource`
- **Obsah:** Zápis platby. Důležitost Variabilního symbolu pro pozdější dohledání. Rozlišení mezi stavem "Zapsáno" a "Potvrzeno".
- **FAQ:** Co když přijde platba bez VS? (Zapište ji k uživateli "Neznámý" a dohledejte později).

### E. Párování plateb (`parovani-plateb`)
- **Typ:** Detailní / Workflow
- **UI vazba:** `RelationManagers/AllocationsRelationManager`
- **Obsah:** Krok za krokem: Otevření platby -> Tab "Přiřazeno" -> "Přiřadit k předpisu". Vysvětlení, že jedna platba může pokrýt více předpisů (např. sourozenci nebo příspěvky + dres).
- **Quick Actions:** Seznam neuhrazených předpisů.

### F. Moje platby a vyúčtování (`moje-platby`)
- **Typ:** Členská sekce
- **UI vazba:** `/member/finance` (pokud existuje) nebo profil člena.
- **Obsah:** Kde najdu kolik mám zaplatit. Kde najdu číslo účtu a variabilní symbol. Co dělat, když jsem zaplatil, ale v systému je stále "K úhradě".
- **Related:** Zabezpečení účtu, Můj profil.

---
**Poznámka:** Všechny články budou využívat terminologii "Předpis" a "Alokace", která je v kódu konzistentní.
