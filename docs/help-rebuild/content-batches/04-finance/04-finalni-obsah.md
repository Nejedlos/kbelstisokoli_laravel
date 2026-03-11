# Finální obsah - Batch 04: Finance

Tato dávka nápovědy pokrývá kompletní finanční agendu klubu, od základních konceptů po detailní návody pro administrátory i členy.

## Vytvořené články

1.  **Finanční systém klubu** (`financni-system-prehled`)
    - Koncepční článek vysvětlující Předpisy, Platby a Alokace.
2.  **Správa finančních tarifů** (`sprava-tarifu`)
    - Technický návod pro nastavení číselníku cen (admin).
3.  **Předpisy plateb (dluhy členů)** (`predpisy-plateb`)
    - Návod pro vystavování dluhů a sledování splatnosti (admin, coach).
4.  **Evidence a příjem plateb** (`evidence-plateb`)
    - Postup pro zápis příchozích peněz z banky nebo hotovosti (admin).
5.  **Párování plateb (Alokace)** (`parovani-plateb`)
    - Klíčové workflow pro propojování plateb s předpisy (admin).
6.  **Moje platby a vyúčtování** (`moje-platby`)
    - Samoobslužný návod pro hráče a rodiče (player, parent).

## Podklady a ověření

- **Analýza:** Vychází z UI auditu Filament resourců a `FinanceService`.
- **Terminologie:** Důsledně používány pojmy "Předpis", "Platba" a "Alokace" podle reálného kódu.
- **Role:** Nastaveno omezení viditelnosti (např. Tarify vidí pouze `admin`).
- **Seed:** Úspěšně naseedováno do DB (celkem 27 článků v systému, z toho 6 finančních).

## Ke kontrole / Otevřené body

- **Hromadné generování:** V systému zatím není jasné hromadné generování předpisů pro celý tým jedním kliknutím. V nápovědě je popsáno individuální zadávání.
- **Členská sekce:** Článek "Moje platby" předpokládá existenci sekce `/member/finance` nebo záložky v profilu. Je třeba ověřit přesnou URL po nasazení frontendu členské sekce.

---
**Dokončeno:** 2026-03-11
**Soubory:** `database/seeders/Help/content/cs/finance/*.md`, `database/seeders/Help/content/en/finance/*.md`
