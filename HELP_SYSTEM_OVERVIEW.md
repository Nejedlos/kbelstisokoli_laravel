# Technický přehled systému nápovědy (Kbelští sokoli)

Tento dokument slouží jako detailní technický podklad popisující architekturu, implementaci a obsah interního systému nápovědy v administraci klubu Kbelští sokoli.

## 1. Technická architektura

Systém nápovědy je postaven jako dynamický prohlížeč Markdown souborů integrovaný přímo do Filament PHP administrace.

### Hlavní komponenty:
- **`App\Services\HelpService`**: Jádro systému. Zajišťuje skenování adresářové struktury, parsování Markdownu, extrakci metadat (FrontMatter/H1) a vyhledávání.
- **`App\Filament\Pages\Help`**: Custom stránka ve Filamentu, která slouží jako router a kontroler pro nápovědu. Podporuje zobrazení přehledu kategorií i detailu konkrétního článku.
- **Blade šablona (`resources/views/filament/pages/help.blade.php`)**: Zajišťuje finální renderování s využitím Tailwind CSS a interaktivních prvků (vyhledávání, navigace).
- **Markdown**: Obsah je uložen v `.md` souborech, což umožňuje snadnou správu verzí v Gitu a psaní bez nutnosti přístupu k DB.

### Klíčové vlastnosti:
- **Lokalizace**: Systém automaticky hledá soubory v `docs/help/{locale}/`. Pokud lokalizace neexistuje, použije češtinu (`cs`).
- **Dynamické mapování**: Názvy kategorií a popisy se čerpají prioritně z překladových souborů (`lang/cs/admin.php`), sekundárně z `README.md` v dané složce.
- **Vyhledávání**: Fulltextové vyhledávání v obsahu všech Markdown souborů s generováním náhledů (excerpts).
- **Ikony a barvy**: Automatické přiřazování ikon (Font Awesome) a barev na základě klíčových slov v názvech složek (např. "sport" -> orange/fa-basketball).

---

## 2. Adresářová struktura obsahu (`docs/help/cs/`)

Obsah je strukturován tak, aby kopíroval logické celky administrativního menu. Číslovaný prefix (např. `01-`) slouží pouze pro řazení v menu a v nápovědě se automaticky odstraňuje.

```text
docs/help/cs/
├── 01-sportovni-agenda/          # Sekce "Sport"
│   ├── 01-tymy.md                # Správa soupisek a realizačních týmů
│   ├── 02-zapasy.md              # Práce s kalendářem zápasů
│   ├── 03-treninky.md            # Plánování tréninků
│   └── 04-dochazka.md            # Evidence účasti (koučové)
├── 02-lide-a-clenove/            # Sekce "Lidé"
│   ├── 01-pridani-uzivatele.md   # Onboarding nových členů
│   ├── 02-hracske-profily.md     # Detaily hráčů pro web
│   └── 03-opravneni-a-role.md    # Nastavení přístupů (Admin)
├── 03-ekonomika/                 # Sekce "Finance"
│   ├── 01-platby.md              # Evidence příchozích plateb
│   └── 02-predpisy.md            # Generování členských příspěvků
├── 04-obsah-a-media/             # Sekce "Web"
│   ├── 01-clanky.md              # Publikace novinek
│   └── 02-galerie.md             # Správa fotografií a AI tagging
├── 05-system/                    # Sekce "Technické"
│   └── 01-nastaveni-brandingu.md # Změna barev a log klubu
└── 99-sprava-napovedy.md         # Návod, jak psát tuto nápovědu
```

---

## 3. Přehled položek menu v administraci

Systém nápovědy pokrývá (nebo má za cíl pokrýt) následující strukturu menu:

### A. Sportovní agenda (Group: `sports_agenda`)
- **Týmy** (`TeamResource`): Správa kategorií, soupisek, vazba hráč/trenér.
- **Zápasy** (`BasketballMatchResource`): Rozpis utkání, výsledky, soupeři.
- **Tréninky** (`TrainingResource`): Rozpisy hal, docházka.
- **Soupeři** (`OpponentResource`): Databáze klubů, slučování duplicit.
- **Sezóny** (`SeasonResource`): Definice časových rámců.

### B. Lidé a členové (Group: `users_and_people`)
- **Uživatelé** (`UserResource`): Centrální správa účtů, e-maily, telefony.
- **Hráčské profily** (`PlayerProfileResource`): Veřejná data o hráčích (čísla dresů, posty).
- **Zájemci / Leady** (`LeadResource`): Kontakty z náborových formulářů.

### C. Ekonomika (Group: `finance`)
- **Platby** (`FinancePaymentResource`): Evidence bankovních převodů.
- **Předpisy plateb** (`FinanceChargeResource`): Generování variabilních symbolů a částek.

### D. Obsah a média (Group: `content_and_media`)
- **Novinky** (`PostResource`): Články na hlavní stránku.
- **Kategorie novinek** (`PostCategoryResource`).
- **Galerie** (`GalleryResource`): Fotoalba.
- **Pool fotografií** (`PhotoPoolResource`): Hromadné nahrávání a AI zpracování fotek.
- **Knihovna médií** (`MediaAssetResource`).

### E. Nastavení webu (Group: `web_settings`)
- **Branding a vzhled** (`BrandingSettings` Page): Barvy, loga, fonty.
- **AI Nastavení** (`AiSettings` Page): Klíče a parametry pro asistenty.

### F. Systém a údržba (Group: `system`)
- **Role a Oprávnění** (`RoleResource`, `PermissionResource`).
- **Auditní logy** (`AuditLogResource`): Kdo, co a kdy v systému změnil.
- **Logy cronu** a **Plánované úlohy**.
- **Detekce 404** a **Přesměrování**.
- **Zpětná vazba** (`FeedbackReportResource`): Hlášení chyb od uživatelů.

---

## 4. Správa a rozšiřování

### Jak přidat nový článek:
1. Vytvořte `.md` soubor v příslušné podsložce `docs/help/cs/`.
2. První řádek musí být `# Nadpis článku`.
3. Soubor se okamžitě objeví v nápovědě.

### Jak přidat novou kategorii:
1. Vytvořte novou složku v `docs/help/cs/`.
2. Pro lidský název a popis přidejte klíče do `lang/cs/admin.php` v sekci `help.categories`.
3. Alternativně vytvořte ve složce soubor `README.md` s nadpisem H1.

### Formát obsahu:
Používáme standardní GitHub Flavored Markdown. Pro lepší srozumitelnost doporučujeme používat:
- **Krokové návody**: Číslované seznamy.
- **Upozornění**: Blokové citace nebo tučný text.
- **Breadcrumbs**: Ruční zápis na začátku souboru (např. `Nápověda > Sekce > Článek`).
