# Finální obsah - Batch 07: Pokročilá správa

Tato batch excesivně rozšířila systém nápovědy o pokročilá témata správy klubu, financí a technického zázemí systému.

## Seznam vytvořených/upravených článků

| Slug | Název (CS) | Stav | Kategorie |
| :--- | :--- | :--- | :--- |
| `pool-fotografii` | Pool fotografií (Hromadná správa) | NOVÉ | Obsah |
| `klubove-souteze` | Klubové soutěže (Turnaje a ligy) | NOVÉ | Sport |
| `sprava-tymu` | Správa týmu a soupisek (Excesivní) | UPRAVENO | Sport |
| `evidence-plateb` | Evidence a příjem plateb (Excesivní) | UPRAVENO | Finance |
| `predpisy-plateb` | Předpisy plateb a tarify (Excesivní) | UPRAVENO | Finance |
| `parovani-plateb` | Alokace a párování (Excesivní) | UPRAVENO | Finance |
| `scenar-nova-sezona` | Restart sezóny a migrace (Excesivní) | UPRAVENO | Systém |
| `nabory-a-zajemci` | Správa náborů (Leady) | UPRAVENO | Lidé |
| `sponzori-partneri` | Partneři a sponzoři (Excesivní) | UPRAVENO | Obsah |
| `branding-emaily` | Branding a nastavení klubu (Excesivní) | UPRAVENO | Systém |
| `api-audit` | Externí data a párování (Excesivní) | UPRAVENO | Systém |
| `planovane-ulohy-cron` | Plánované úlohy (Cron) | NOVÉ | Systém |

## Klíčová vylepšení
- **Technická hloubka:** Články nyní obsahují detaily o hromadných importech, AI vylepšeních a technických vazbách mezi moduly (např. vliv Brandingu na finance).
- **Interaktivita:** Doplněny **Quick Actions**, které uživatele z nápovědy rovnou přenesou na správnou stránku v administraci.
- **FAQ:** Přidány praktické odpovědi na nejčastější chyby (např. co dělat při "Špatné nahrávce" nebo jak vyřešit nespárované platby).
- **Bilingvnost:** Veškerý obsah byl plně lokalizován do češtiny i angličtiny.

## Ověření
- Obsah byl naseedován do databáze pomocí `HelpSeeder`.
- Verifikována délka obsahu (každý článek má ~1500-2000 znaků).
- Ověřena dostupnost pro role `admin` a `super_admin`.
