### Změny v zobrazení zápasů (2026-02-27)

V rámci úkolu bylo vylepšeno zobrazení zápasů na frontendu a přidány pokročilé filtry.

#### Provedené změny:
- **Rozšíření MatchController:**
    - Přidána podpora pro filtrování podle týmu (`team_id`), sezóny (`season_id`) a typu zápasu (`match_type`).
    - Implementována logika pro výchozí sezónu (aktuální sezóna začíná 1. září).
    - Oprava zobrazení dat: Přidána podpora pro stavy zápasů `played` a `scheduled` (původně se počítalo pouze s `completed` a `planned`), což zpřístupnilo importovaná historická data.
    - Předávání dat pro filtry do Blade šablony.
- **Frontend (Blade):**
    - Do přehledu zápasů (`public.matches.index`) přidán filtrační formulář s automatickým odesíláním při změně.
    - Upravena komponenta `x-match-card`:
        - Podpora pro různé názvy stavů (`played` / `completed` a `scheduled` / `planned`).
        - Přidáno zobrazení typu zápasu (Mistrovské, Pohárové, Turnaj, Přátelské).
        - Vizuální zvýraznění klubového týmu (modrá barva textu).
        - Oprava lokalizace výsledků (Vítězství/Prohra/Remíza).
- **Lokalizace:**
    - Doplněny chybějící překlady pro typy zápasů a filtry v `lang/cs/matches.php` a `lang/en/matches.php`.

#### Technické detaily:
- Stavy zápasů: Podporovány jsou stavy `played` a `completed` pro výsledky a `scheduled` a `planned` pro budoucí zápasy.
- Typy zápasů jsou v databázi uloženy jako zkratky: `MI`, `PO`, `TUR`, `PRATEL`.
- Paginace na stránce nyní korektně zachovává všechny vybrané filtry.
- Pro zvýraznění aktuálního týmu se používá podmínka `$match->is_home`, kde se barva aplikuje na příslušnou stranu (domácí/hosté).
