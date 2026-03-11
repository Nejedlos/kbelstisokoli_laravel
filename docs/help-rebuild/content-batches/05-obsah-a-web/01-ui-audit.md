# UI Audit - Batch 05: Obsah a web

Tento audit mapuje sekce pro správu veřejného a interního obsahu webu, sjednocené pod kategorií `obsah`.

## 1. Aktuality a články (Posts)
- **Resource**: `PostResource`
- **Menu**: Obsah a média > Aktuality
- **Role**: Admin, Editor
- **Tabulka**:
    - Sloupce: Obrázek, Název, Kategorie, Autor, Stav (Draft/Published), Datum publikace, Featured (ikona hvězdy).
    - Filtry: Kategorie, Stav, Autor.
    - Akce: Edit, Delete, View (na webu).
- **Formulář**:
    - Záložka **Obsah**: Název, Slug, Kategorie, Perex, Hlavní obsah (Rich Editor), Hlavní obrázek.
    - Záložka **Nastavení**: Autor, Stav, Featured, Datum publikace.
    - Záložka **SEO**: Meta titulek, Meta popis, Kód před </body> (pro specifické skripty).

## 2. Kategorie článků (PostCategories)
- **Resource**: `PostCategoryResource`
- **Menu**: Obsah a média > Kategorie aktualit
- **Role**: Admin, Editor
- **Tabulka**: Název, Slug, Počet článků.
- **Formulář**: Název, Slug (auto), Barva (pro štítek na webu).

## 3. Galerie a Fotoalba (Galleries)
- **Resource**: `GalleryResource`
- **Menu**: Obsah a média > Galerie
- **Role**: Admin, Editor
- **Tabulka**: Název, Datum konání, Počet fotek, Viditelnost.
- **Formulář**:
    - Základní údaje: Název, Slug, Datum, Popis.
    - Média: Upload fotek (Spatie Media Library), hromadný upload, drag&drop řazení.

## 4. Sponzoři a partneři (Partners)
- **Resource**: `PartnerResource`
- **Menu**: Obsah a média > Partneři
- **Role**: Admin
- **Tabulka**: Logo, Název, Typ (Generální, Hlavní, Partner), Priorita, Aktivní.
- **Formulář**:
    - Logo: Upload (čtverec/obdélník).
    - Údaje: Název, Typ (Select), URL webu partnera.
    - Zobrazení: Priorita (číslo), Přepínač aktivní.

## 5. Bannery a oznámení (Announcements)
- **Resource**: `AnnouncementResource`
- **Menu**: Obsah a média > Oznámení
- **Role**: Admin
- **Tabulka**: Název, Typ (Info, Success, Warning, Danger), Platnost od-do, Aktivní.
- **Formulář**:
    - Text: Titulek, Zpráva.
    - Vzhled: Typ (Barva), Ikonka (Select).
    - Časování: Platnost od (DateTime), Platnost do (DateTime), Aktivní.

## 6. Menu a navigace (Menus)
- **Resource**: `MenuResource`
- **Menu**: Systém > Menu (Pozn: v auditu přesunuto logicky pod obsah v nápovědě)
- **Role**: Admin
- **Tabulka**: Název, Lokace (Header, Footer, Mobile), Počet položek.
- **Formulář**:
    - Definice: Název, Lokace.
    - Položky: Stromová struktura položek (Relation Manager), Název, URL/Route, Ikonka, Cílové okno.

## 7. Média a soubory (MediaAssets)
- **Resource**: `MediaAssetResource`
- **Menu**: Obsah a média > Knihovna médií
- **Role**: Admin, Editor
- **Tabulka**: Náhled, Název, Typ, Velikost, Veřejné.
- **Formulář**: Soubor, Název, Alternativní text, Veřejná dostupnost.

---

### Časté úkony v této sekci
1. Napsat novou aktualitu a nastavit jí SEO.
2. Vytvořit galerii z víkendového zápasu.
3. Přidat nového partnera klubu.
4. Nastavit vyskakovací banner (Oznámení) pro nábor.
5. Upravit pořadí odkazů v horním menu.

### Rizika a nejasnosti
- **Vite Build**: Změny v designu vyžadují build, ale změny obsahu (články) ne.
- **Cache**: Web může používat cache pro menu a partnery, změna se nemusí projevit hned.
- **Rozměry obrázků**: U partnerů a aktualit je důležité dodržet doporučené poměry stran (bude v nápovědě).
