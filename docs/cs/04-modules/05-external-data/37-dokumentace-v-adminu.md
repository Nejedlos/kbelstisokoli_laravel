# Modul: Dokumentace v administraci

Tento modul umožňuje prohlížení interní dokumentace (Markdown souborů ve složce `docs/`) přímo v administraci Filament.

## Účel
Zpřístupnit technickou a uživatelskou dokumentaci administrátorům v přehledném a vyhledatelném formátu, aniž by museli opouštět administrační rozhraní nebo přistupovat k souborovému systému.

## Technické řešení
- **Service:** `App\Services\DocumentationService` se stará o skenování adresáře `docs/`, převod Markdownu do HTML (pomocí `league/commonmark`) a fulltextové vyhledávání v souborech.
- **Filament Page:** `App\Filament\Pages\Documentation` slouží jako frontend pro tento modul.
- **Zabezpečení:** Stránka je přístupná pouze uživatelům s oprávněním `access_admin`.
- **Vyhledávání:** Implementováno reaktivně přes Livewire s úryvky textu a zvýrazněním hledaného výrazu.

## Způsob použití
1. V levém navigačním menu administrace klikněte na položku **Dokumentace**.
2. V levém panelu můžete procházet strukturu složek nebo použít vyhledávací pole.
3. Kliknutím na soubor se jeho obsah vyrenderuje v hlavním okně.

## Přidávání nových souborů
Stačí přidat nový `.md` soubor do složky `docs/` (nebo její podsložky). Modul jej automaticky detekuje a zařadí do navigace. Názvy složek a souborů jsou automaticky formátovány (odstranění číselných předpon a převedení na Headline formát).
