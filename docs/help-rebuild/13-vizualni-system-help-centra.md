# 13. Vizuální systém Help Centra

Tento dokument definuje vizuální standardy a vylepšení pro nové Help Centrum v projektu Kbelští sokoli. Cílem je vytvořit profesionální, moderní a vysoce použitelné prostředí.

## 1. Designové principy
- **Čitelnost na prvním místě:** Obsah nápovědy musí být snadno skenovatelný a čitelný.
- **Vizuální hierarchie:** Jasné rozlišení mezi nadpisy, tělem textu a doplňkovými informacemi.
- **Konzistence s Filamentem:** Využití Tailwind barev a spacingu, které ladí se zbytkem administrace.
- **Decentní interaktivita:** Jemné hover efekty a přechody, které napomáhají orientaci.

## 2. Typografie a barvy
- **Nadpisy:** Font s vysokou váhou (`font-black`) pro hlavní tituly, `font-bold` pro sekce. Využití `tracking-tight` pro moderní vzhled.
- **Tělo textu:** `text-slate-600` pro optimální kontrast na bílém pozadí. Velikost `text-lg` pro hlavní články.
- **Akcentní barvy:** Využití `primary-600` (klubová modrá/červená dle konfigurace) pro interaktivní prvky a důležité callouty.
- **Kategorizace:** Každá hlavní kategorie má svou barvu (oranžová, modrá, zelená atd.) pro lepší zapamatovatelnost.

## 3. Komponenty a jejich vylepšení

### 3.1 Karty (Categories & Articles)
- **Vzhled:** Bílé pozadí, velmi jemný border (`border-slate-100`), zaoblení odpovídající modernímu stylu projektu.
- **Hover:** Změna barvy borderu na `primary-200`, jemný shadow-xl, mírný posun nahoru (`-translate-y-1`).
- **Ikony:** Využití Font Awesome 7 Light v barevných kontejnerech se zaoblením.

### 3.2 Hledání (Search Box)
- **Umístění:** Dominantní prvek na landing page, v hlavičce s tmavým pozadím (`bg-slate-900`).
- **Styling:** Skleněný efekt (`backdrop-blur-xl`), bílé texty, výrazný focus state s glow efektem.

### 3.3 Článek (Content Area)
- **Markdown:** Vyladěné `prose` třídy pro Tailwind Typography.
- **Callouty:** Barevně odlišené bloky pro TIPY a VAROVÁNÍ s příslušnými ikonami.
- **Seznamy:** Vlastní odrážky pomocí Font Awesome ikon (např. checkmark) pro lepší vizuální zážitek.

### 3.4 FAQ a Rychlé akce
- **FAQ:** Akordeonový styl nebo čistý seznam s oddělovači.
- **Quick Actions:** Kompaktní karty v sidebaru s výraznou ikonou a jasným CTA.

## 4. Layout a Spacing
- **Třísloupcová struktura:** (Navigation | Content | Sidebar) pro detail článku na velkých obrazovkách.
- **Odsazení:** Velkorysé mezery mezi sekcemi (`space-y-12`) pro vzdušnost.
- **Responzivita:** Automatické skládání sidebaru pod hlavní obsah na mobilních zařízeních.

## 5. Implementační detaily (Tailwind v4)
- Využití moderních utilit jako `animate-in`, `fade-in`, `slide-in-from-bottom`.
- Práce s `line-clamp` pro konzistentní výšky karet.
- Využití `blur` a `opacity` pro dekorativní prvky v pozadí.
