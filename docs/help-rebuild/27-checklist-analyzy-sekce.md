# Checklist analýzy sekce (Help v2)

Tento checklist slouží jako závazný podklad pro analýzu každé sekce systému před samotným psaním a seedováním obsahu nápovědy. Každý bod musí být ověřen proti reálnému kódu (PHP třídy) a živému uživatelskému rozhraní (Filament).

---

## A. Navigace a kontext
*Prověření umístění v systému a cílového publika.*

- [ ] **Název v menu:** Jaký je přesný text položky v levé navigaci? (včetně diakritiky)
- [ ] **Navigační skupina:** Do jakého bloku (Navigation Group) sekce patří? (např. "Sportovní agenda", "Finance")
- [ ] **URL Slug:** Jak vypadá cesta v prohlížeči? (např. `/admin/users`)
- [ ] **Oprávnění (Roles):** Které role tuto sekci vidí? (Ověřit v `canAccessPanel()` nebo Policy)
- [ ] **Souvislosti:** Které sekce logicky předcházejí nebo následují? (Breadcrumb logika)

## B. Reálné UI prvky
*Detailní dekompozice prvků na obrazovce.*

- [ ] **Titulek stránky:** Shoduje se H1 nadpis s názvem v menu?
- [ ] **Akce v záhlaví (Header Actions):** Jsou zde tlačítka jako "Nový", "Import", "Export", "Zpět"?
- [ ] **Sloupce tabulky (Table Columns):** Seznam všech viditelných sloupců. Jaké mají labely?
- [ ] **Filtry (Filters):** Jaké jsou dostupné filtry? (Selecty, Toggles, Date Range)
- [ ] **Akce řádku (Row Actions):** Co lze udělat s jedním záznamem? (Edit, Delete, View, Replicate)
- [ ] **Hromadné akce (Bulk Actions):** Co lze udělat s výběrem? (Delete, Export, Change Status)
- [ ] **Prázdné stavy (Empty States):** Co se zobrazí, když nejsou data? Jsou tam návodné texty?
- [ ] **Sekce formuláře (Form Sections/Tabs):** Jak je formulář logicky rozdělen? (Karty, Záložky)
- [ ] **Pole formuláře (Form Fields):** Seznam polí. Jsou tam povinná pole (`required`)?
- [ ] **Nápovědy v UI (Helper Texts):** Jsou u polí vysvětlivky (`helperText`) nebo tipy (`hint`)?
- [ ] **Validace:** Jaká jsou omezení? (Unikátní e-mail, formát telefonu, velikost souboru)
- [ ] **Stavy a Badge:** Jaké barvy a texty se používají pro stavy? (např. Aktivní = Emerald, Čekající = Amber)
- [ ] **Relation Managers:** Jsou vespod stránky tabulky s vazbami? (např. U týmu -> Seznam hráčů)
- [ ] **Widgety / Infolisty:** Jsou na stránce statistiky, grafy nebo informativní bloky?

## C. Reálné workflow
*Jak uživatel se sekcí skutečně pracuje.*

- [ ] **Hlavní scénář (Happy Path):** Jak vypadá typický proces "vytvoření a uložení"?
- [ ] **První krok:** Co musí uživatel udělat jako úplně první věc po vstupu?
- [ ] **Editace:** Která pole lze měnit dodatečně a která jsou po uložení zamčená?
- [ ] **Změna stavu:** Jak se mění životní cyklus záznamu? (např. Aktivace -> Deaktivace -> Archivace)
- [ ] **Hledání:** Jak funguje fulltextové vyhledávání v tabulce? (Která pole prohledává?)
- [ ] **Export dat:** V jakém formátu padají data ven? (CSV, XLSX, PDF)

## D. Riziková místa
*Prevence chyb a vysvětlení složitostí.*

- [ ] **Nevratné akce:** Co se stane po kliknutí na "Smazat"? (Soft delete vs. Hard delete)
- [ ] **Dopady (Side Effects):** Ovlivní změna zde i jiné moduly? (např. změna tarifu -> přepočet dluhů)
- [ ] **Pasti pro uživatele:** Co je neintuitivní? (např. nutnost uložit sekci před přidáním relace)
- [ ] **Externí závislosti:** Vyžaduje akce napojení na banku, e-mail nebo jiný servis?

## E. Obsahové bloky help článku
*Checklist pro finální sestavení Markdownu.*

- [ ] **Short Intro:** Obsahuje klíčový přínos sekce?
- [ ] **Purpose:** Je jasně řečeno, k čemu sekce slouží?
- [ ] **Audience:** Jsou role správně přiřazeny?
- [ ] **Breadcrumbs:** Je hierarchie nápovědy logická?
- [ ] **Quick Actions:** Vedou odkazy na správné routy v adminu?
- [ ] **Screen Overview:** Jsou popsány všechny oblasti obrazovky?
- [ ] **Fields & Filters:** Jsou názvy v uvozovkách přesně podle UI?
- [ ] **Common Mistakes:** Jsou varování dostatečně výrazná?
- [ ] **Best Practices:** Jsou tipy skutečně užitečné pro rychlost práce?
- [ ] **FAQ:** Odpovídají otázky na reálné problémy?
- [ ] **Related Sections:** Jsou odkazy na související témata funkční?
- [ ] **Search Keywords:** Obsahuje seznam i synonyma a hovorové výrazy?

---
*Poznámka: Pokud u některého bodu narazíte na rozpor mezi dokumentací a realitou, prioritou je realita v kódu. Nápověda musí reflektovat skutečný stav aplikace.*
