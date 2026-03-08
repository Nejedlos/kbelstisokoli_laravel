# Lokalizované řádkování (Line-height)

Tento dokument popisuje implementaci specifického řádkování pro českou verzi webu, která zohledňuje přítomnost diakritiky a zajišťuje lepší čitelnost a vizuální stabilitu.

## Účel úpravy
České texty (zejména v kapitálkách a u tučných nadpisů) často působí při velmi těsném řádkování "přeplněně" kvůli akcentům nad písmeny (č, š, ž, ř atd.). Úprava mírně zvyšuje vertikální prostor v české verzi, zatímco anglická verze si zachovává svůj původní kompaktní styl.

## Technické řešení

### 1. Globální CSS pravidla
V souboru `resources/css/app.css` byla definována pravidla využívající selektor `html[lang="cs"]`. Tato pravidla mají vyšší prioritu a upravují jak základní HTML elementy, tak utility třídy.

**Upravené hodnoty:**
| Prvek / Třída | Výchozí (EN) | Česká verze (CS) |
| :--- | :--- | :--- |
| `h1`, `h2` | `1.1` (md: `1.05`) | `1.6` (md: `1.5`) |
| `h3` - `h6` | `1.25` (tight) | `1.5` |
| `.leading-display` | `1.1` (md: `1.05`) | `1.6` (md: `1.5`) |
| `.leading-tight` | `1.25` | `1.5` |
| `.leading-snug` | `1.375` | `1.6` |
| `.leading-relaxed` | `1.625` | `1.85` |

### 2. Implementace v komponentách
Všechny komponenty, které dříve používaly napevno zapsané třídy jako `leading-[1.1]`, byly upraveny tak, aby využívaly novou utilitu `.leading-display`.

**Dotčené komponenty:**
- **Hero block:** Hlavní nadpis H1 nyní používá `.leading-display`.
- **CTA block:** Nadpisy sekcí používají `.leading-display`.
- **Patička (Footer):** Nadpisy sloupců byly změněny z `leading-none` na `leading-tight`.
- **Hlavička (Header):** Text loga a slogan mají zvětšené řádkování pro lepší čitelnost diakritiky.
- **Section Heading:** Podnadpisy sekcí nyní používají `leading-tight`.

## Verifikace
Změny se aplikují automaticky na základě atributu `lang` v tagu `<html>`, který je řízen aktuální lokalizací aplikace. Při přepnutí jazyka v hlavičce se styl řádkování okamžitě přizpůsobí.

## Související soubory
- `resources/css/app.css`
- `resources/views/public/blocks/hero.blade.php`
- `resources/views/public/blocks/cta.blade.php`
- `resources/views/components/footer.blade.php`
- `resources/views/components/header.blade.php`
- `resources/views/components/section-heading.blade.php`
