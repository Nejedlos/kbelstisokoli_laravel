# Metodika psaní help stránky (v2)

Tento dokument definuje závazný postup pro tvorbu obsahu nápovědy v projektu Kbelští sokoli. Cílem je zajistit, aby nápověda nebyla "psána od stolu", ale striktně vycházela z reálné implementace v kódu a aktuálního vzhledu uživatelského rozhraní (Filament PHP).

---

## 1. Identifikace cílové sekce (Pre-flight)
Před začátkem psaní musí být jasně definováno, o čem článek je a kde se daná funkce nachází.

- **Název sekce v menu:** Přesný text z navigace (např. "Členové a lidé").
- **Technická třída:** Cesta k Resource nebo Page třídě (např. `App\Filament\Resources\UserResource`).
- **Cílové role:** Seznam rolí, které mají k sekci přístup (např. `admin`, `coach`).
- **Umístění v navigaci:** Skupina navigace (Navigation Group).
- **Základ Breadcrumbu:** Hierarchie stránek vedoucí k sekci.

## 2. Technický rozbor sekce (Code Audit)
Autor nápovědy **musí** prozkoumat zdrojový kód a chování aplikace.

### A. Struktura stránek (Pages)
- **List page:** Jaká data jsou v hlavní tabulce?
- **Create/Edit page:** Jak vypadá formulář? Je rozdělen do sekcí (Section/Tabs)?
- **View page:** Existuje detailní zobrazení záznamu?
- **Relation Managers:** Jaké související záznamy lze v detailu spravovat (např. u týmu soupiska hráčů)?

### B. Prvky tabulky (Table Interface)
- **Sloupce (Columns):** Názvy a význam sloupců, ikony, badge/stavy (barvy).
- **Filtry (Filters):** Jak lze data omezit? (Selecty, toggles, vyhledávání).
- **Akce řádku (Row Actions):** Upravit, Smazat, Replikovat, Odhlásit atd.
- **Hromadné akce (Bulk Actions):** Export, Hromadná změna stavu.
- **Akce záhlaví (Header Actions):** Import, Nový záznam.

### C. Prvky formuláře (Form Interface)
- **Pole (Fields):** Typy polí (Select, DatePicker, atd.).
- **Nápovědy (Hints/Helper text):** Co aplikace uživateli radí přímo v UI.
- **Placeholdery:** Co je v polích před vyplněním.

### D. Logika a oprávnění
- **Viditelnost:** Jsou některé prvky (tlačítka/pole) viditelné jen pro určité role?
- **Překlady:** Používají se standardní překladové klíče z `lang/*.php`?

## 3. UX rozbor sekce (User Perspective)
Analýza toho, jak uživatel se sekcí reálně pracuje.

- **Hlavní úkoly:** Co je "Happy Path" uživatele? (např. "Přidat hráče a zařadit ho do týmu").
- **Kritická místa:** Kde hrozí chyba? (např. smazání záznamu s vazbami).
- **Matoucí prvky:** Co by začátečník nemusel pochopit bez vysvětlení? (např. rozdíl mezi "Členem" a "Uživatelem").
- **Závislosti:** Musí být něco hotovo předtím, než uživatel vstoupí sem? (např. musí existovat Sezóna).

## 4. Obsahový rozbor (Drafting)
Při psaní samotného Markdownu se držíme těchto standardů:

- **Short Intro:** 1-2 věty. Musí obsahovat klíčové sloveso (Spravujte, Nastavte, Sledujte).
- **Purpose:** Jasný cíl sekce v 1 větě.
- **Screen Overview:** Popis obrazovky. Používejte orientační body (Levý sloupec, Horní lišta, Tabulka).
- **Step-by-step:** Číslovaný seznam. Každý krok začíná akcí ("Klikněte na...", "Vyberte...").
- **Fields / Filters / Actions:** Tabulkový nebo seznamový popis s přesnými názvy z UI (v uvozovkách).
- **FAQ:** Minimálně 2 otázky, které řeší reálné dotazy uživatelů.
- **Common Mistakes (Varování):** Červený blok. Popisuje nevratné akce nebo logické pasti.
- **Best Practices (Tipy):** Modrý blok. Jak pracovat rychleji (např. klávesové zkratky, hromadné akce).
- **Search Keywords:** Seznam 10-15 slov včetně synonym (i těch, která nejsou v textu).

## 5. Validace reality (Verification)
Před odevzdáním musí proběhnout kontrola.

1.  **UI Match:** Sedí názvy tlačítek? (např. v UI je "Uložit změny", v nápovědě nesmí být "Potvrdit").
2.  **Skutečnost vs. Dedukce:** Pokud autor něco nevidí v kódu ani v UI, nesmí si to vymýšlet. Musí použít značku `[? TO VERIFY]`.
3.  **Lokalizace:** Sedí české texty nápovědy na české UI a anglické na anglické?
4.  **Funkčnost odkazů:** Fungují "Rychlé akce" (Quick Actions)?

---
**Poznámka:** Každý článek nápovědy musí být schopen projít touto metodikou nezávisle na autorovi.
