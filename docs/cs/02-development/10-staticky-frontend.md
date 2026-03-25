# Nový statický frontend (Přechod z databázového CMS na Blade)

Tento dokument popisuje architekturu a plán přechodu z databázově řízeného CMS (modely `Page`, `Menu`, `MenuItem`) na fixní, kódem definovaný frontend využívající Blade šablony a konfigurační soubory.

## 1. Cíl změny
- **Robustnost:** Odstranění rizika smazání nebo poškození struktury webu (menu, stránky) v administraci.
- **Verzování:** Veškerá struktura a statický obsah budou součástí Git repozitáře.
- **Výkon:** Odstranění zbytečných DB dotazů na CMS tabulky při každém načtení stránky.
- **Jednoduchost:** Odstranění komplexity "blokového" systému CMS tam, kde není potřeba.

## 2. Navigace (Menu)
Stávající systém využívající modely `Menu` a `MenuItem` bude nahrazen rozšířenou konfigurací v `config/navigation.php`.

### Nová struktura v `config/navigation.php`:
- `public`: Hlavní horní menu (již částečně existuje).
- `footer`: Levý sloupec v patičce (O klubu, Nábor, Kontakt, GDPR).
- `footer_club`: Pravý sloupec v patičce (Týmy C/E, Externí odkazy na hlavní web).

## 3. Stránky a Blade šablony
Databázové stránky budou nahrazeny fixními Blade šablonami. Pro dynamické prvky (seznamy zápasů, formuláře) budou použity stávající komponenty.

### Plánované Blade soubory:
| Původní Slug | Blade šablona | Controller / Routa | Poznámka |
|--------------|---------------|-------------------|----------|
| `home` | `resources/views/public/home.blade.php` | `HomeController` | Statické bloky místo `$page->content` |
| `o-klubu` | `resources/views/public/about.blade.php` | `StaticPageController` / `/o-klubu` | Původní texty o klubu a historii |
| `nabor` | `resources/views/public/recruitment.blade.php` | Routa v `public.php` | Statický landing page pro nábor |
| `kontakt` | `resources/views/public/contact.blade.php` | `ContactController` | Kontaktní info + mapa + Livewire formulář |
| `gdpr` | `resources/views/public/gdpr.blade.php` | `StaticPageController` / `/gdpr` | Právní informace |
| `tymy` | `resources/views/public/teams/index.blade.php` | `TeamController` | Seznam aktivních týmů |
| `zapasy` | `resources/views/public/matches/index.blade.php` | `MatchController` | Výpis zápasů z DB |
| `treninky` | `resources/views/public/trainings/index.blade.php` | `TrainingController` | Rozpis tréninků |

## 4. Technický postup
1. **Příprava lokalizace:** Statické texty budou přesunuty do `lang/cs/general.php` (a `en`).
2. **Vytvoření šablon:** Přepis `rich_text` a `hero` bloků z DB přímo do Blade.
3. **Úprava `AppServiceProvider`:** Zrušení View Composerů, které tahají menu z DB.
4. **Úprava routování:** Zrušení catch-all routy pro `PageController` a nahrazení explicitními routami pro statické stránky.
5. **Vyčištění databáze:** (Volitelné) Odstranění nepotřebných tabulek a Filament Resource pro CMS po úspěšném nasazení.

## 5. Zachování dynamiky
Následující prvky zůstávají dynamické (vázané na sportovní modely v DB):
- Zápasy a výsledky.
- Týmy a jejich soupisky.
- Novinky (Blog/Aktuality).
- Galerie a fotky.
- Členská sekce.
