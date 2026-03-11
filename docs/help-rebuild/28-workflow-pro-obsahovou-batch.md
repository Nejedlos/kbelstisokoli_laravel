# Workflow pro obsahovou dávku (Batch) nápovědy

Tento dokument definuje standardní výrobní proces pro plnění systému nápovědy po logických dávkách (batch). Cílem je zajistit, aby veškerý obsah odpovídal aktuálnímu stavu aplikace, byl konzistentní a technicky správně implementován v seedech.

---

## 1. Výběr batch (Dávky)
- **Vstupy**: `docs/help-rebuild/25-backlog-tvorby-obsahu.md`, aktuální priority klubu.
- **Výstupy**: Identifikátor dávky (např. `Batch 01`), seznam prioritních témat.
- **Povinné**: Shoda s backlogem nebo zdůvodnění změny priority.
- **Nesmí se přeskočit**: Ověření, že všechny články v dávce mají společný tematický jmenovatel (např. "Sportovní jádro").

## 2. Seznam sekcí v batchi
- **Vstupy**: Backlog článků v dané dávce.
- **Výstupy**: Seznam konkrétních Resource tříd, Page tříd a URL v administraci, které budou analyzovány.
- **Povinné**: Mapování článků na skutečné cesty v kódu.
- **Nesmí se přeskočit**: Identifikace závislostí (např. "Článek o tréninku vyžaduje existenci Týmů").

## 3. UI audit každé sekce
- **Vstupy**: Přístup do živé aplikace / lokálního prostředí, `docs/help-rebuild/27-checklist-analyzy-sekce.md`.
- **Výstupy**: Vyplněný checklist analýzy pro každou sekci.
- **Povinné**: Fyzické projití všech záložek, formulářů a akcí v dané sekci.
- **Nesmí se přeskočit**: Kontrola překladů v UI (v češtině i angličtině).

## 4. Mapa skutečných akcí a prvků
- **Vstupy**: Výsledky UI auditu.
- **Výstupy**: Strukturovaný seznam prvků (Sloupce tabulky, Filtry, Akce řádku, Pole formuláře, Relace).
- **Povinné**: Přesné názvy tlačítek a labelů tak, jak je vidí uživatel.
- **Nesmí se přeskočit**: Rozlišení mezi tím, co vidí různé role (např. trenér vs. admin).

## 5. Návrh seznamu článků
- **Vstupy**: Mapa prvků a backlog.
- **Výstupy**: Seznam slugů a názvů článků (v2 struktura).
- **Povinné**: Unikátní slugy napříč celým systémem.
- **Nesmí se přeskočit**: Definice `short_intro` pro každý článek.

## 6. Návrh Quick Actions
- **Vstupy**: Mapa akcí a navigace v administraci.
- **Výstupy**: Definice tlačítek pro nápovědu (Label, URL, Ikona).
- **Povinné**: Ikony musí být z Font Awesome 7 Light (`fa-light`).
- **Nesmí se přeskočit**: Ověření, že URL odkazují na správné Filament routy.

## 7. Návrh FAQ
- **Vstupy**: UX rozbor sekce (časté chyby, dotazy uživatelů).
- **Výstupy**: Seznam otázek a odpovědí (cs/en).
- **Povinné**: Odpovědi musí být věcné a krátké.
- **Nesmí se přeskočit**: Zahrnutí "Common Mistakes" (Časté chyby) do FAQ nebo calloutu.

## 8. Návrh Search Keywords
- **Vstupy**: Slovník pojmů, názvy prvků, synonyma.
- **Výstupy**: Pole klíčových slov pro každý článek.
- **Povinné**: Zahrnutí i hovorových výrazů (např. "peníze" pro Finance).
- **Nesmí se přeskočit**: Kontrola klíčových slov v obou jazycích (cs/en).

## 9. Seed implementace
- **Vstupy**: Všechny předchozí návrhy, `HelpArticleSeeder`, `HelpContentSeeder`.
- **Výstupy**: Kód v seederech, soubory v `database/seeders/Help/content/`.
- **Povinné**: Dodržení Markdown struktury podle `docs/help-rebuild/26-metodika-psani-jedne-help-stranky.md`.
- **Nesmí se přeskočit**: Spuštění `php artisan db:seed --class=HelpSeeder` v čistém prostředí.

## 10. Kontrola konzistence
- **Vstupy**: Nasazený obsah v lokálním prostředí.
- **Výstupy**: Protokol o kontrole (nebo OK stav).
- **Povinné**: Kontrola funkčnosti odkazů a zobrazení ikon.
- **Nesmí se přeskočit**: Ověření visibility podle rolí (zda uživatel vidí jen to, co má).

## 11. UX revize
- **Vstupy**: Vykreslená stránka nápovědy.
- **Výstupy**: Finální úpravy textace a formátování.
- **Povinné**: Kontrola čitelnosti (délka odstavců, zvýraznění důležitých prvků).
- **Nesmí se přeskočit**: Kontrola zobrazení na mobilním zařízení.

## 12. Finální dokumentace
- **Vstupy**: Hotová dávka obsahu.
- **Výstupy**: Aktualizovaný `CHANGELOG` nebo dokumentace dávky v `docs/help-rebuild/`.
- **Povinné**: Záznam o dokončení dávky v exekučním backlogu.
- **Nesmí se přeskočit**: Promazání aplikační a view cache na produkci/testu po nasazení.
