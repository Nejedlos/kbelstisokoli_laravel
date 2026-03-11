# UI Audit - Batch 04: Finance

Tento dokument mapuje reálný stav finančního modulu v administraci (Filament v5). Modul slouží ke správě předpisů plateb (členské příspěvky, soustředění atd.), evidenci přijatých plateb a jejich vzájemnému párování (alokacím).

## 1. Navigace a přístup

- **Skupina v menu:** Finance (`admin.navigation.groups.finance`)
- **Resource třídy:**
    - `FinanceChargeResource` (Předpisy plateb)
    - `FinancePaymentResource` (Platby)
    - `FinancialTariffResource` (Tarify)
- **Cílové role:**
    - `admin`, `super_admin`: Plný přístup (CRUD).
    - `coach`: Vidí finance (pokud má `access_admin`), ale politika (`FinanceChargePolicy`, `FinancePaymentPolicy`) omezuje zápis pouze na roli `admin`.

## 2. Sekce: Předpisy plateb (FinanceCharge)

Zde se definuje, co má člen zaplatit.

- **Seznam (Table):**
    - **Sloupce:** Člen (user.name), Položka / Účel (title + charge_type), Částka, Zaplaceno (suma alokací), Stav (badge), Splatnost, Viditelné (boolean).
    - **Stavy:**
        - `draft` (Koncept - šedá)
        - `open` (K úhradě / Po splatnosti - modrá/červená)
        - `partially_paid` (Částečně - žlutá)
        - `paid` (Zaplaceno - zelená)
        - `cancelled` (Zrušeno - červená)
    - **Filtry:** Stav, Typ (membership_fee, camp_fee, tournament_fee, other).
- **Formulář (Create/Edit):**
    - **Sekce 1 (Základ):** Člen (searchable select), Typ platby, Název/Účel, Podrobný popis.
    - **Sekce 2 (Finance):** Celková částka, Datum splatnosti, Období od/do.
    - **Sekce 3 (Nastavení):** Stav, Viditelnost pro člena (Toggle), Interní poznámka.
- **Relace:** `AllocationsRelationManager` (zobrazuje platby přiřazené k tomuto předpisu).

## 3. Sekce: Platby (FinancePayment)

Zde se evidují příchozí peníze.

- **Seznam (Table):**
    - **Sloupce:** Datum přijetí, Plátce, Částka, Alokováno (kolik z platby je již využito), VS, Metoda (badge), Stav (badge).
    - **Metody:** `bank_transfer` (Převod), `cash` (Hotovost), `other`.
    - **Stavy:** `recorded` (Zapsáno), `confirmed` (Potvrzeno), `reversed` (Stornováno).
- **Formulář (Create/Edit):**
    - **Sekce 1 (Základ):** Plátce (select), Částka, Datum/čas přijetí, Způsob úhrady.
    - **Sekce 2 (Identifikace):** Variabilní symbol, ID transakce / Reference, Poznámka z výpisu.
    - **Sekce 3 (Stav):** Stav záznamu, Zapsal (readonly).
- **Relace:** `AllocationsRelationManager` (zobrazuje předpisy, na které byla tato platba použita).

## 4. Sekce: Tarify (FinancialTariff)

Číselník částek pro opakované platby.

- **Seznam (Table):** Název, Základní částka, Jednotka (Měsíc/Sezóna).
- **Formulář:** Název tarifu, Základní částka, Jednotka, Popis.

## 5. Workflow a logika (`FinanceService`)

1. **Vytvoření předpisu:** Ručně nebo (v budoucnu) hromadně pro tým.
2. **Příjem platby:** Ruční zápis (např. z bankovního výpisu).
3. **Alokace (Párování):**
    - Administrátor otevře platbu a v tabulce "Přiřazeno k předpisům" klikne na "Přiřadit k předpisu".
    - Vybere otevřený předpis daného uživatele (systém nabízí pouze neuhrazené).
    - Zadá částku k započtení.
4. **Synchronizace stavu:** Po každé alokaci se automaticky přepočítá stav předpisu (`syncChargeStatus`).

## 6. Problematická a nejasná místa

- **Bankovní synchronizace:** V kódu nebyl nalezen automatický import z banky (Fio API apod.). Aktuálně se předpokládá ruční nahrávání/zápis.
- **Hromadné předpisy:** Filament resource umožňuje vytvářet předpisy po jednom. Není jasné, zda existuje "Action" pro hromadné generování (např. pro celý tým). *Poznámka: UserResource má Relations k SeasonConfigs, kde by se dalo očekávat propojení s tarify.*
- **Viditelnost pro trenéry:** I když mají trenéři `manage_economy`, politika jim v MVP verzi pravděpodobně nedovoluje předpisy vytvářet (pouze `admin`).

## 7. Návrh témat pro články

1. **Přehled financí v klubu:** Základní koncept Předpisy vs. Platby.
2. **Správa tarifů:** Jak nastavit ceny příspěvků.
3. **Vytvoření předpisu platby:** Individuální zadání dluhu členovi.
4. **Evidence přijaté platby:** Jak zapsat peníze z banky nebo hotovost.
5. **Párování plateb (Alokace):** Jak propojit platbu s dluhem.
6. **Stavy plateb a předpisů:** Co znamená "Částečně zaplaceno" nebo "Po splatnosti".
7. **Finance z pohledu člena:** Co vidí hráč/rodič ve svém profilu.

---
**Audit proveden:** 2026-03-11
**Podklad:** PHP třídy Filament resourců, `FinanceService`, `AuthServiceProvider`.
