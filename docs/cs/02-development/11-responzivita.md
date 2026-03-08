# Responzivita – Best Practices Guidelines

Tento dokument definuje standardy a postupy pro zajištění perfektní responzivity napříč celým projektem Kbelští sokoli. Tyto pravidla jsou závazná pro všechny nové komponenty a úpravy stávajících.

## 1. Breakpointy a škálování
- **Mobile-first přístup:** Vždy začínáme návrhem pro nejmenší zařízení a postupně přidáváme pravidla pro větší obrazovky.
- **Standardní breakpointy (min-width):**
    - `360px` (Základ)
    - `480px`
    - `640px` (sm)
    - `768px` (md)
    - `1024px` (lg)
    - `1280px` (xl)
    - `1536px` (2xl)
- **Plynulý přechod:** Mezi breakpointy se layout nesmí "rozbít". Používáme plynulé škálování (fluid design) pomocí `clamp()`, procent, `vw` nebo `rem`.
- **Fixní hodnoty:** Pixely (`px`) používáme pouze pro velmi malé prvky, jako jsou linky (hairlines) nebo ikonky.

## 2. Viewport a Základní nastavení
- Každá stránka musí obsahovat: `<meta name="viewport" content="width=device-width, initial-scale=1" />`.
- **Box-sizing:** Globálně nastaven na `border-box` pro všechny prvky.
- **Root font-size:** 16px (1rem). Veškerá typografie a spacing musí být v jednotkách `rem`.

## 3. Kontejner a Maximální šířka
- **Max šířka obsahu:** 1100–1280px pro desktop, vždy centrované.
- **Padding kontejneru:**
    - Mobile: `16px` (1rem)
    - Tablet: `24px` (1.5rem)
    - Desktop: `32px–48px` (2rem–3rem)
- **Flexibilní šířka:** Hlavní bloky nesmí mít pevnou šířku (`width: 500px`). Vždy používáme `max-width` v kombinaci s `width: 100%`.

## 4. Grid Systém
- Výhradní používání **CSS Grid** nebo **Flexbox**. Nikdy nepoužíváme `float`.
- **Počet sloupců:**
    - Desktop: 12 sloupců
    - Tablet: 6 sloupců
    - Mobile: 4 sloupce
- **Mezery (Gap):**
    - Mobile: `12–16px`
    - Tablet: `16–24px`
    - Desktop: `24–32px`
- **Skládání sloupců:** Typicky 3→2→1 pro karty nebo 2→1 pro sekce.

## 5. Typografie
- **Body text:**
    - Mobile: `16–18px`
    - Desktop: `18–20px`
- **Line-height:** `1.5–1.7` pro běžný text, `1.1–1.25` pro nadpisy.
- **Fluidní nadpisy (pomocí clamp):**
    - **H1:** `clamp(1.75rem, 5vw, 3rem)` (cca 28px–48px)
    - **H2:** `clamp(1.375rem, 3.5vw, 2.25rem)` (cca 22px–36px)
    - **H3:** `clamp(1.125rem, 2.5vw, 1.75rem)` (cca 18px–28px)
- **Šířka odstavce:** Maximálně 60–75 znaků pro optimální čitelnost.

## 6. Mezery (Spacing Scale)
- Používáme jednotnou škálu založenou na násobcích 4 (např. 4/8/12/16/24/32/48/64/96).
- **Proporcionální mezery:** Na mobile používáme menší mezery (např. vertikální padding sekce 32–48), na desktopu větší (64–96).

## 7. Obrázky a Média
- **Základní pravidlo:** `img { max-width: 100%; height: auto; display: block; }`.
- **Responzivní obrázky:** Používáme `srcset` a `sizes` pro optimalizaci přenosu dat.
- **Formáty:** Preferujeme moderní formáty (WebP/AVIF) s fallbackem na JPG/PNG.
- **Layout Shift:** Vždy definujeme `aspect-ratio` nebo rozměry pro rezervaci místa.
- **Lazy loading:** Používáme `loading="lazy"` pro obrázky mimo první obrazovku.

## 8. Hero sekce (Above the Fold)
- Obsah hero sekce na mobile by neměl přesáhnout 1–2 výšky obrazovky.
- Hlavní akce (CTA) musí být viditelná bez scrollu na šířce 360–430px.
- **Kontrast:** Text na obrázku musí být vždy čitelný (používáme poloprůhledné overlaye nebo gradienty).

## 9. Navigace
- **Mobile:** Implementujeme hamburger menu nebo offcanvas navigaci. Viditelná by měla být maximálně 1 úroveň menu.
- **Sticky header:** Pouze pokud je vizuálně lehký a nezabírá příliš místa na výšku.
- **Tap targety:** Minimálně `44x44px` pro všechny klikatelné prvky.
- **Mezery:** Mezi interaktivními prvky musí být mezera alespoň `8px`.

## 10. Formuláře
- **Labely:** Vždy čitelné, nepoužívat pouze placeholder.
- **Input výška:** `44–48px` pro snadné ovládání prstem.
- **Šířka:** 100 % na mobile, omezený `max-width` na desktopu.
- **Chybové hlášky:** Vždy pod polem, nikdy v tooltipech, které na mobile překrývají obsah.
- **UX:** Používat správné typy inputů (`email`, `tel`, `number`) pro vyvolání správné klávesnice.

## 11. Tabulky
- Na mobile nesmí tabulky přetékat kontejner (horizontální scroll celého webu).
- **Řešení pro mobile:**
    - "Stacked rows" (label a hodnota pod sebou).
    - Horizontální scroll pouze v rámci kontejneru tabulky s jasným vizuálním indikátorem.

## 12. Karty a Listy
- **Zobrazení:** 1 sloupec (mobile), 2 sloupce (tablet), 3–4 sloupce (desktop).
- **Konzistence:** Akční tlačítka zarovnáváme jednotně (např. vždy na spodek karty).

## 13. Modaly a Dialogy
- **Mobile:** Full-screen modaly nebo "bottom sheets".
- **Padding:** `16–24px`.
- **Scroll:** Pokud je obsah delší, scrolluje se pouze vnitřek modalu (`max-height: 80–90vh`).
- **Zavírání:** Viditelné "X", kliknutí mimo modal a klávesa ESC.

## 14. Přetečení (Overflow)
- **Kritické:** Nepoužívat `width: 100vw` na kontejnery (způsobuje horizontální scroll kvůli scrollbaru).
- **Dlouhé řetězce:** Používat `overflow-wrap: anywhere; word-break: break-word;` pro zabránění rozbití layoutu dlouhými e-maily nebo URL.

## 15. Přístupnost a Výkon (Core Web Vitals)
- **Kontrast:** Minimálně WCAG AA.
- **Focus state:** Vždy viditelný pro navigaci klávesnicí.
- **CLS:** Rezervovat místo pro obrázky a dynamický obsah.
- **LCP:** Optimalizovat největší prvky v hero sekci.

## Kontrolní Checklist (Základní)
- [ ] Žádný horizontální scroll na šířce 360px.
- [ ] CTA viditelná na 360px bez složitého scrollu.
- [ ] Tap targety mají alespoň 44x44px.
- [ ] Obrázky mají definovaný aspect-ratio a lazy loading.
- [ ] Textové bloky mají max 75 znaků na řádek.
- [ ] Všechny formulářové prvky mají výšku min 44px.
- [ ] Použita jednotná spacing škála.
- [ ] Modaly fungují správně na mobile (včetně zavírání).
