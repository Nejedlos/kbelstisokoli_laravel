# Informační architektura nové nápovědy

Tento dokument definuje strukturu obsahu, hierarchii kategorií a standardy pro tvorbu článků v novém help systému projektu Kbelští sokoli. Cílem je vytvořit **živou encyklopedii klubu**, která pokryje vše od sportovní agendy po ekonomiku a technickou správu.

## 1. Uživatelské role a cílové skupiny (Audience)

Nápověda je strukturována tak, aby obsloužila různé úrovně přístupu v systému:

- **Administrátor (`admin`)**: Kompletní správa klubu, uživatelů, financí a obsahu webu.
- **Trenér (`coach`)**: Správa sportovní agendy, týmů, tréninků, zápasů a docházky.
- **Hráč / Člen (`player`)**: Přístup k vlastnímu profilu, programu akcí, docházce a přehledu plateb.
- **Rodič (`parent`)**: Správa účtů svých dětí (přepínání profilů), placení příspěvků.
- **Hospodář / Pokladník**: Specifická role pro správu plateb, párování banky a finanční reporting.
- **Redaktor**: Správa aktualit, fotogalerií a statických stránek webu.
- **Superadmin (`super_admin`)**: Technické nastavení, správa rolí, auditování logů a údržba systému.

---

## 2. Hlavní kategorie nápovědy (Help Tree)

Navržená struktura eliminuje duplicity a logicky rozděluje systém do oblastí odpovídajících navigaci v administraci i členské sekci.

### A. Úvod a Onboarding (`uvod`)
- **Popis**: Základní informace pro nové uživatele, jak se v systému zorientovat a nastavit si účet.
- **Ikona**: `fa-light fa-house-sparkles`
- **Barva**: `blue` (Tailwind `sky`)
- **Cílové role**: Všichni
- **Články**:
    - **První kroky**: Přihlášení, obnova hesla a první nastavení.
    - **Můj profil**: Jak udržovat své údaje aktuální (telefony, maily).
    - **Role v systému**: Přehled co která role může a vidí.
    - **Mobilní aplikace**: Jak si přidat web na plochu jako aplikaci (PWA).
    - **Slovník pojmů**: Vysvětlení termínů jako "Sokol", "Příspěvek", "Soupiska", "Předpis".
    - **Často kladené otázky (FAQ)**: Rychlé odpovědi na nejčastější dotazy.
    - **Bezpečnost**: Jak chráníme vaše data a jak si nastavit silné heslo.

### B. Sportovní agenda (`sport`)
- **Popis**: Srdce systému – správa týmů, hráčů, zápasů a tréninkového procesu.
- **Ikona**: `fa-light fa-basketball-hoop`
- **Barva**: `orange` (Tailwind `orange`)
- **Cílové role**: Trenér, Administrátor, Hráč
- **Články**:
    - **Správa týmů**: Vytváření kategorií, přiřazování trenérů a barvy týmů.
    - **Soupisky a členství**: Kdo hraje v jakém týmu, správa "is_on_roster".
    - **Plánování sezóny**: Jak vytvořit novou sezónu a překlopit týmy.
    - **Tréninkový proces**: Zadávání tréninků, opakované události.
    - **Zápasy a nominace**: Od vytvoření soupeře po nominaci hráčů na utkání.
    - **Vedení docházky**: Jak efektivně značit účast (pro trenéry).
    - **Omlouvání z akcí**: Postup pro hráče a rodiče (mobile-first).
    - **Hráčské profily (Stinty)**: Historie působení hráče, změny čísel dresů.
    - **Statistiky a výkony**: Sledování docházky v čase a sportovních výsledků.
    - **Zdravotní prohlídky**: Evidence platnosti lékařských potvrzení.
    - **Výpůjčky dresů**: Sledování klubového vybavení přiděleného hráčům.

### C. Členové a komunikace (`lide`)
- **Popis**: Evidence uživatelů, správa rolí, GDPR a vnitřní informační kanály.
- **Ikona**: `fa-light fa-users`
- **Barva**: `teal` (Tailwind `teal`)
- **Cílové role**: Administrátor, Redaktor
- **Články**:
    - **Evidence členů**: Přidávání uživatelů, importy z Excelu/matriky.
    - **Rodinné vazby**: Propojení rodiče s dítětem pro společnou správu.
    - **Role a oprávnění**: Detailní nastavení přístupů do administrace.
    - **GDPR a souhlasy**: Správa souhlasů se zpracováním osobních údajů a focením.
    - **Interní oznámení**: Jak poslat zprávu všem členům na dashboard.
    - **Emailové kampaně**: Hromadné rozesílání informací členům.
    - **Náborové formuláře**: Jak zpracovat přihlášky nových zájemců.
    - **Exporty dat**: Jak vygenerovat podklady pro ČBF nebo pojišťovnu.

### D. Ekonomika a finance (`finance`)
- **Popis**: Správa členských příspěvků, plateb, tarifů a finanční integrity klubu.
- **Ikona**: `fa-light fa-wallet`
- **Barva**: `emerald` (Tailwind `emerald`)
- **Cílové role**: Administrátor, Pokladník, Rodič/Hráč
- **Články**:
    - **Finanční tarify**: Nastavení sazebníku (Měsíční vs. Sezónní paušály).
    - **Předpisy plateb**: Jak systém generuje "dluhy" členům.
    - **Párování plateb**: Import bankovních výpisů a automatické párování.
    - **QR platby**: Jak usnadnit členům placení přes mobil.
    - **Sourozenecké slevy**: Automatizace a manuální úpravy slev pro rodiny.
    - **Pokuty a mimořádné platby**: Zadávání sankcí nebo plateb za soustředění.
    - **Historie plátce**: Sledování platební morálky člena napříč sezónami.
    - **Dluhy a upomínky**: Proces vymáhání neuhrazených příspěvků.
    - **Finanční uzávěrka**: Generování reportů pro vedení klubu.

### E. Obsah a web (`obsah`)
- **Popis**: Editace veřejného webu, správa článků, fotogalerií, partnerů a statických informací.
- **Ikona**: `fa-light fa-newspaper`
- **Barva**: `amber` (Tailwind `amber`)
- **Cílové role**: Administrátor, Redaktor
- **Články**:
    - **Aktuality a blog**: Psaní článků, formátování a plánování publikace.
    - **Správa médií**: Nahrávání fotek, práce s "Fotopoolem" a optimalizace.
    - **Fotogalerie**: Vytváření alb ze zápasů a akcí.
    - **Sponzoři a partneři**: Správa log a odkazů na partnery klubu.
    - **Statické stránky**: Úprava sekcí "O nás", "Kontakty", "Haly".
    - **Menu a navigace**: Správa odkazů v hlavičce a patičce webu.
    - **Bannery a upozornění**: Nastavení vyskakovacích oken a informačních lišt.
    - **SEO pro redaktory**: Jak psát texty, aby nás Google našel.

### F. Systém a nastavení (`system`)
- **Popis**: Globální konfigurace, technická údržba, logy a integrace.
- **Ikona**: `fa-light fa-gear`
- **Barva**: `slate` (Tailwind `slate`)
- **Cílové role**: Superadmin
- **Články**:
    - **Branding klubu**: Nastavení log, barev a kontaktních údajů klubu.
    - **Nastavení sezón**: Definice aktivní sezóny a termínů.
    - **E-mailové šablony**: Úprava vzhledu a textů systémových e-mailů.
    - **API a integrace**: Napojení na banku, ČBF nebo externí služby.
    - **Audit logy**: Sledování kdo, kdy a co v systému změnil.
    - **Zálohování**: Jak funguje ochrana dat v systému.
    - **Importy dat**: Technický popis formátů pro hromadné nahrávání.

---

## 3. Scénáře a postupy (Master How-to)

Kromě popisu modulů nápověda obsahuje komplexní průvodce pro klíčové momenty v životě klubu:

1.  **Start nové sezóny**: (Založení sezóny -> Překlopení týmů -> Inicializace plateb -> Aktualizace webu).
2.  **Nábor nového hráče**: (Přijetí přihlášky -> Vytvoření uživatele -> Přiřazení týmu -> Nastavení tarifu).
3.  **Organizace turnaje**: (Vytvoření akce -> Nominace -> Komunikace s rodiči -> Zveřejnění výsledků).
4.  **Ukončení členství**: (Deaktivace profilu -> Vyrovnání financí -> Archivace dat).

---

## 4. Strukturní bloky obsahu (Article Schema)

Každý článek v databázi musí následovat tuto strukturu pro zajištění maximální srozumitelnosti:

1.  **Metadata (Stabilní identifikace)**
    - `title`: Jasný a stručný název (např. "Správa docházky").
    - `short_intro`: 1-2 věty vysvětlující, co uživatel na stránce vyřeší.
    - `purpose`: Cíl dané sekce v systému.
    - `audience`: Pro koho je článek určen (vybrané role).
2.  **Hlavní tělo (Markdown)**
    - `screen_overview`: Popis obrazovky v systému (co kde je).
    - `step_by_step`: Číslovaný postup pro provedení hlavní akce.
    - `fields`: Vysvětlení významu konkrétních polí (zejména těch méně jasných).
    - `filters`: Jak efektivně filtrovat data v tabulkách.
    - `actions`: Popis tlačítek a operací (např. "Hromadná změna stavu").
3.  **Doplňkové vizuální a interaktivní bloky**
    - `quick_actions`: Tlačítka s přímými odkazy do administrace.
    - `video_guide`: Odkaz na krátký screen-recording (pokud existuje).
    - `faq`: Specifické otázky k tématu.
    - `common_mistakes`: **Varování** před častými chybami (vizuálně zvýrazněno červeně).
    - `best_practices`: **Tipy** pro efektivnější práci (vizuálně zvýrazněno modře).
    - `related_sections`: Odkazy na související články nápovědy.

---

## 5. Standardy pro tvorbu obsahu

### Jazyk a styl
- **Tón**: Profesionální, přátelský, ale věcný.
- **Pojmenování prvků**: Vždy používáme přesné názvy z UI (např. tlačítko "Uložit změny", nikoliv "to potvrďte").
- **Dvojjazyčnost**: Každý článek existuje v `cs` a `en` verzi.

### Vizuály
- **Ikony**: Používáme výhradně Font Awesome 7 Pro (`fa-light`).
- **Screenshoty**: Musí být aktuální, s rozmazanými osobními údaji (GDPR), pořízené v brandingovém schématu.

---

## 6. Naming Conventions (Standardy pojmenování)

Pro zajištění stability seedů a čistého UI dodržujeme tato pravidla:

### Slugy (URL identifikátory)
- Vždy malá písmena, bez diakritiky, slova oddělená pomlčkou.
- **Kategorie**: Jednoslovné nebo krátké (např. `sport`, `finance`, `lide`).
- **Články**: Musí být unikátní v rámci celého helpu (např. `sprava-dochazky`, `nastaveni-tarifu`).

### Stabilní klíče (pro Seedery)
- Pro identifikaci záznamů v seederech používáme slugy jako `key`.
- Struktura: `category.slug` a `article.slug`.

### Vyhledávací klíčová slova (`search_keywords`)
- Seznam termínů (včetně synonym a hovorových výrazů), které nejsou přímo v textu, ale uživatelé je mohou hledat (např. pro článek "Platby" přidáme: `penize, prispevky, dluhy, prevod, bano, ucet, qr`).

---

## 7. Realizace a napojení na seedy

Tato architektura bude přímo promítnuta do:
1.  `HelpStructureSeeder`: Vytvoří 6 definovaných kategorií.
2.  `HelpContentSeeder`: Naplní kategorie rozsáhlým obsahem na základě této architektury.
