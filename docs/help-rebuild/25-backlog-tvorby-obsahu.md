# Backlog tvorby help obsahu (v2)

Tento dokument slouží jako výrobní plán pro postupné naplňování systému nápovědy obsahem. Je rozdělen do logických dávek (batchů) s definovanými prioritami, cílovými skupinami a odhadem složitosti.

## 1. Přehled kategorií
| Kód | Název kategorie | Ikona | Barva |
|:--- |:--- |:--- |:--- |
| `uvod` | Úvod a Onboarding | `fa-rocket-launch` | Blue |
| `sport` | Sportovní agenda | `fa-basketball-hoop` | Orange |
| `lide` | Členové a komunikace | `fa-users` | Teal |
| `finance` | Ekonomika a finance | `fa-wallet` | Emerald |
| `obsah` | Obsah a web | `fa-newspaper` | Amber |
| `system` | Systém a nastavení | `fa-gear` | Slate |

---

## 2. Obsahové batche (Výrobní plán)

### Batch 01: Onboarding & Uživatelský základ
**Cíl**: Zorientovat nového uživatele a zajistit správné nastavení účtu.
**Priorita**: Kritická (Všichni uživatelé)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| První kroky | `prvni-kroky` | Všichni | P1 | S | - |
| Můj profil | `muj-profil` | Všichni | P1 | S | - |
| Role v systému | `role-v-systemu` | Všichni | P2 | S | - |
| Mobilní aplikace (PWA) | `mobilni-aplikace` | Všichni | P2 | S | - |
| Slovník pojmů | `slovnik-pojmu` | Všichni | P3 | S | - |
| Bezpečnost a hesla | `bezpecnost` | Všichni | P3 | S | - |

---

### Batch 02: Týmy, Soupiska & Sezóny
**Cíl**: Nastavit sportovní strukturu klubu.
**Priorita**: Vysoká (Admini a Trenéři)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Správa týmů | `sprava-tymu` | Admin/Coach | P1 | M | - |
| Soupisky a členství | `soupisky-clenstvi` | Admin/Coach | P1 | M | `sprava-tymu` |
| Plánování sezóny | `planovani-sezony` | Admin | P1 | L | - |
| Hráčské profily (Stinty) | `hracske-profily` | Admin/Coach | P2 | M | `soupisky-clenstvi` |

---

### Batch 03: Tréninkový proces & Docházka
**Cíl**: Pokrýt každodenní agendu trenéra a interakci hráče s programem.
**Priorita**: Vysoká (Trenéři, Hráči, Rodiče)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Tréninkový proces | `treninkovy-proces` | Admin/Coach | P1 | M | `sprava-tymu` |
| Vedení docházky | `vedeni-dochazky` | Coach | P1 | M | `treninkovy-proces` |
| Omlouvání z akcí | `omlouvani-z-akci` | Player/Parent| P1 | S | `treninkovy-proces` |
| Statistiky a výkony | `statistiky-vykony` | Admin/Coach | P2 | M | `vedeni-dochazky` |

---

### Batch 04: Zápasy & Vybavení
**Cíl**: Správa utkání, nominací a klubového majetku.
**Priorita**: Střední (Trenéři)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Zápasy a nominace | `zapasy-nominace` | Admin/Coach | P1 | L | `soupisky-clenstvi` |
| Zdravotní prohlídky | `zdravotni-prohlidky` | Admin/Player| P2 | S | - |
| Výpůjčky dresů | `vypujcky-dresu` | Admin/Coach | P2 | S | - |

---

### Batch 05: Správa členů & GDPR
**Cíl**: Evidence osob a právní integrita dat.
**Priorita**: Vysoká (Admini)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Evidence členů | `evidence-clenu` | Admin | P1 | M | - |
| Rodinné vazby | `rodinne-vazby` | Admin/Parent| P1 | M | `evidence-clenu` |
| GDPR a souhlasy | `gdpr-souhlasy` | Admin | P2 | S | - |
| Náborové formuláře | `naborove-formulare`| Admin | P2 | M | - |

---

### Batch 06: Komunikace & Oprávnění
**Cíl**: Informování členů a nastavení přístupů.
**Priorita**: Střední (Admini, Redaktoři)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Interní oznámení | `interni-oznameni` | Admin/Editor| P2 | S | - |
| Emailové kampaně | `emailove-kampane` | Admin | P2 | M | `evidence-clenu` |
| Exporty dat | `exporty-dat` | Admin | P2 | M | `evidence-clenu` |
| Role a oprávnění | `role-opravneni` | Superadmin | P3 | M | - |

---

### Batch 07: Finance I - Nastavení a Platby
**Cíl**: Zprovoznění platebního systému z pohledu uživatele a admina.
**Priorita**: Kritická (Admini, Pokladníci, Všichni)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Finanční tarify | `financni-tarify` | Admin/Finance| P1 | M | - |
| Předpisy plateb | `predpisy-plateb` | Admin/Finance| P1 | L | `financni-tarify` |
| QR platby | `qr-platby` | Player/Parent| P1 | S | `predpisy-plateb` |
| Historie plátce | `historie-platce` | Player/Parent| P2 | S | - |

---

### Batch 08: Finance II - Operativa a Dluhy
**Cíl**: Každodenní správa peněz a reporting.
**Priorita**: Vysoká (Pokladníci)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Párování plateb | `parovani-plateb` | Finance | P1 | L | `predpisy-plateb` |
| Dluhy a upomínky | `dluhy-upominky` | Admin/Finance| P1 | M | `parovani-plateb` |
| Sourozenecké slevy | `sourozenecke-slevy`| Admin/Finance| P2 | M | `predpisy-plateb` |
| Pokuty a mimořádné platby| `pokuty-platby` | Admin/Finance| P2 | S | - |
| Finanční uzávěrka | `financni-uzaverka`| Admin/Finance| P3 | M | - |

---

### Batch 09: Redakce & Webový obsah
**Cíl**: Správa veřejné prezentace klubu.
**Priorita**: Střední (Redaktoři, Admini)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Aktuality a blog | `aktuality-blog` | Editor/Admin| P2 | M | - |
| Správa médií & Galerie | `media-galerie` | Editor/Admin| P2 | M | - |
| Sponzoři a partneři | `sponzori-partneri`| Admin | P3 | S | - |
| Statické stránky a Menu| `staticke-stranky` | Admin | P3 | S | - |
| Bannery a upozornění | `bannery` | Admin | P3 | S | - |
| SEO pro redaktory | `seo-redakce` | Editor | P3 | S | - |

---

### Batch 10: Systém & Komplexní scénáře
**Cíl**: Technické nastavení a průvodci "krok za krokem" pro složité procesy.
**Priorita**: Různá (Superadmin, Admin)

| Článek | Slug | Role | Priorita | Složitost | Závislosti |
|:--- |:--- |:--- |:--- |:--- |:--- |
| Nastavení sezón (Tech) | `system-sezony` | Superadmin | P1 | S | - |
| Start nové sezóny | `scenar-nova-sezona`| Admin | P1 | L | Batch 02 + 07 |
| Nábor nového hráče | `scenar-nabor` | Admin | P1 | M | Batch 05 + 07 |
| Branding a e-maily | `branding-emaily` | Superadmin | P2 | M | - |
| Organizace turnaje | `scenar-turnaj` | Admin/Coach | P2 | M | Batch 03 + 04 |
| API a Audit logy | `api-audit` | Superadmin | P3 | L | - |
| Ukončení členství | `scenar-odchod` | Admin | P2 | S | - |

---

## 3. Metodika tvorby (Checklist pro prompt)

Při tvorbě článků v rámci jednotlivých batchů musí být dodržen tento postup:

1.  **Analýza modulu**: Junie si prohlédne kód a strukturu DB daného modulu.
2.  **Metadata**: Definice v `HelpArticleSeeder` (včetně `search_keywords`).
3.  **Markdown**: Vytvoření souboru podle `docs/help-rebuild/10-obsahovy-standard-help-stranky.md`.
4.  **Lokalizace**: Vždy `cs` i `en` verze.
5.  **Interaktivita**: Doplnění minimálně 2 FAQ a 1 Rychlé akce.
6.  **Verifikace**: Ověření renderu v UI.

## 4. Odhad kapacity
- **Malý článek (S)**: ~15 min
- **Střední článek (M)**: ~30 min
- **Velký článek (L)**: ~60 min (vyžaduje hlubší analýzu procesů)

*Poznámka: Tento backlog je živý dokument a může být upraven na základě priorit vedení klubu.*
