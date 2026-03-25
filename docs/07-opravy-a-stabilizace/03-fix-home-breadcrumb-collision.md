# Oprava kolize klíčů breadcrumbs a ViewException (2026-03-25)

Tento dokument zaznamenává opravu kritické chyby `ViewException` (Cannot access offset of type array on array), která se objevila po předchozí opravě vyhledávání.

## 1. Popis chyby
- **Chyba:** `Cannot access offset of type array on array`
- **Soubor:** `resources/views/components/page-header.blade.php`
- **Příčina:** V komponentě `page-header` byl jako klíč pro breadcrumb "Úvod" použit klíč `__('general.home')`. V souboru `lang/cs/general.php` je však pod klíčem `home` definováno **pole** (obsahující vítací texty pro homepage), nikoli řetězec. PHP nepovoluje použití pole jako klíče asociativního pole, což vedlo k pádu při volání `array_merge`.

## 2. Provedené změny

### Komponenta `page-header`
- Klíč `__('general.home')` byl nahrazen správným klíčem `__('general.nav.home')`, který vrací řetězec "Úvod" (resp. "Home").

### BreadcrumbService a Controllery
- Zjištěno, že se v projektu (např. v `BreadcrumbService` a `NewsController`) používaly klíče `__('nav.home')` a `__('nav.news')`. 
- Protože soubor `lang/cs/nav.php` neexistuje, tyto volání vracela pouze surový řetězec klíče (např. "nav.home").
- Všechna tato volání byla sjednocena na existující klíče v `general.php`, tedy `__('general.nav.home')` a `__('general.nav.news')`.

### SeoService
- Opravena fallback logika pro generování titulků stránek. Neexistující klíče `nav.*` byly nahrazeny klíči `general.nav.*`.
- Pro vyhledávání byl použit klíč `__('search.title')`.

## 3. Prevence
- Při přidávání breadcrumbs v budoucnu je nutné dbát na to, aby klíč překladu vždy vracel řetězec.
- Doporučuje se používat `BreadcrumbService` pro konzistentní generování cest.

## 4. Verifikace
- **Vyhledávání:** Stránka se nyní korektně vykresluje i s breadcrumbs.
- **Aktuality:** Stránka funguje a zobrazuje správné překlady v breadcrumbs.
- **SEO:** Metadata titulků pro systémové stránky (novinky, kontakty atd.) nyní používají správné překlady místo surových klíčů.
