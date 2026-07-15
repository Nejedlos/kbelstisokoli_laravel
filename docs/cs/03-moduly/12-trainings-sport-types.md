# Rozlišení sportů u tréninků

Tento modul rozšiřuje systém tréninků o možnost specifikovat sport (Basketbal nebo Volejbal). Tato funkce byla přidána primárně pro offseason tréninky.

## Účel modulu
Umožnit správu docházky a informovanost členů o typu tréninku v obdobích, kdy klub provozuje i jiné sporty než hlavní (Basketbal).

## Technický popis
- Do tabulky `trainings` byl přidán sloupec `sport` (string) s výchozí hodnotou `basketball`.
- Model `Training` obsahuje pole `sport` v `$fillable`.
- V administraci (Filament) bylo přidáno výběrové pole do formuláře tréninku a ikona do tabulky tréninků.
- Členská sekce (dashboard a docházka) dynamicky mění ikony a popisky událostí podle zvoleného sportu.
- Veřejný frontend zobrazuje ikonu a název sportu u každého nadcházejícího tréninku.

## Způsob použití

### Pro administrátory / Trenéry
Při vytváření nebo editaci tréninku v sekci **Tréninky** vyberte v poli **Sport** požadovanou hodnotu.
- **Basketbal:** Výchozí volba, používá ikonu basketbalového míče.
- **Volejbal:** Volba pro offseason tréninky, používá ikonu volejbalového míče.

**Hromadná změna sportu:**
V seznamu tréninků můžete vybrat více záznamů a pomocí hromadné akce **Změnit sport** (v menu pod ikonou tří teček u vybraných záznamů) změnit sport pro všechny označené tréninky najednou. To je užitečné například při hromadném klonování tréninků pro offseason.

### Pro uživatele / Členy
Uživatelé uvidí typ sportu přímo u události na své nástěnce nebo v kalendáři docházky. Popis události se automaticky změní na "Basketbalový trénink" nebo "Volejbalový trénink".

## Lokalizace
Podporovány jsou obě jazykové mutace (CS/EN) prostřednictvím standardních překladových souborů:
- `lang/{locale}/trainings.php`
- `lang/{locale}/member.php`
- `lang/{locale}/admin.php`

## Dynamické odkazování (Smart Linking)
Systém automaticky detekuje stav přihlášení uživatele na veřejném webu:
- **Přihlášený uživatel:** Odkazy na tréninky (v Hero sekci na hlavní straně a v seznamu tréninků) vedou přímo do členské sekce na detail docházky daného tréninku.
- **Anonymní návštěvník:** Odkazy vedou na obecný veřejný přehled tréninků (pro tréninky v Hero sekci) nebo je odkaz do členské sekce skryt (v seznamu tréninků).

Tato funkce zajišťuje rychlou cestu k nahlášení docházky pro aktivní členy.
