# Přístupnost a čitelnost Help Centra (v2)

Tento dokument mapuje vylepšení v oblasti uživatelského zážitku (UX), přístupnosti (A11y) a čitelnosti obsahu v novém systému nápovědy. Cílem je zajistit, aby nápověda byla pohodlně použitelná pro všechny skupiny uživatelů, včetně těch s omezením zraku nebo mobility.

## 1. Hlavní cíle a standardy
- **Kontrast (WCAG AA)**: Všechny texty a interaktivní prvky musí splňovat minimální kontrastní poměr.
- **Typografie**: Jasně čitelné písmo, dostatečná velikost a vhodné řádkování pro dlouhé texty.
- **Klávesnicová navigace**: Plná ovladatelnost systému bez použití myši.
- **Responzivita**: Optimalizované zobrazení pro mobily, tablety i velké monitory.
- **Srozumitelnost**: Přehledné členění informací pomocí vizuálních kotev (ikony, info bloky).

## 2. Provedená vylepšení

### 2.1 Vizuální kontrast a barvy
- **Zvýšení kontrastu u metadat**: Písmo původně v barvě `slate-300/400` bylo nahrazeno tmavšími odstíny (`slate-500/600`), aby bylo lépe čitelné na světlém pozadí.
- **Odstranění opacit v info blocích**: V komponentě `x-help.callout` byla odstraněna vlastnost `opacity`, která zhoršovala kontrast textu na barevném podkladu.
- **Zlepšení placeholderů**: Vyhledávací pole nyní používá kontrastnější barvu placeholderu pro lepší orientaci v hero sekci.

### 2.2 Klávesnicová navigace a Focus States
- **Explicitní focus ringy**: Všechny interaktivní karty (kategorie, články) a odkazy v navigaci dostaly jasně viditelný focus state (`ring-4`), který je aktivován při navigaci tabulátorem.
- **Logický Tab Index**: Prvky jsou uspořádány tak, aby sledovaly přirozený tok stránky (Hledání -> Kategorie -> Články -> Navigace).

### 2.3 Přístupnost pro čtečky (ARIA)
- **Popisky ikon**: Přidány skryté texty nebo `aria-label` atributy pro interaktivní prvky, které neobsahují text (např. vyhledávací pole).
- **Struktura nadpisů**: Opravena hierarchie nadpisů tak, aby odpovídala logickému stromu dokumentu (H1 pro titul článku, H2 pro sekce).
- **Breadcrumbs**: Navigace v drobečkové cestě je nyní označena jako `aria-label="Breadcrumb"`.

### 2.4 Čitelnost a Typografie
- **Zvětšení odznaků (Badges)**: Text v odznacích (např. "Doporučujeme", "Pro trenéry") byl zvětšen z `8px` na `10px` pro lepší čitelnost.
- **Optimalizace `prose`**: Články využívají rozšířené řádkování a jasně definované rozestupy mezi odstavci a nadpisy.
- **Zlepšení délky odstavců**: Obsahový standard doporučuje dělit dlouhé bloky textu na menší části (max 3-4 věty) a doplňovat je info bloky.

### 2.5 Mobilní UX (Touch Targets)
- **Zvětšení plochy pro dotyk**: Tlačítka a karty mají minimální rozměr `44x44px` (nebo ekvivalentní plochu), aby byly snadno stisknutelné na mobilu.
- **Sticky navigace**: Sidebar navigace se na mobilu chová jako scrollable horizontální menu nebo je umístěna logicky pod hlavním obsahem s rychlým návratem nahoru.

## 3. Kontrolní seznam pro tvůrce obsahu
Při psaní nových článků nápovědy dodržujte tato pravidla:
1. **Krátké věty**: Vyhýbejte se souvětím, buďte věcní.
2. **Odrážky**: Seznamy jsou přehlednější než odstavce.
3. **Tučné zvýraznění**: Klíčové pojmy a názvy tlačítek z UI pište tučně.
4. **Vizuální rozlišení**: Používejte `x-help.callout` pro tipy a varování.
5. **Alternativní texty**: Pokud vkládáte obrázky, vždy vyplňte jejich popis (alt).

---
*Poslední aktualizace: 11. 3. 2026*
