# Návrh help článků - Batch 06: Systém a scénáře

Návrh článků pro kategorii `system`, které pokrývají technickou správu a komplexní procesy ("Master How-to").

## 1. Správa sezón a překlopení dat
- **Slug**: `system-sezony`
- **Audience**: Admin, Superadmin
- **Účel**: Návod na založení nové sezóny a inicializaci dat hráčů.
- **Témata**: Aktivní sezóna, Kopírování konfigurací (Initializovat), Dopad na finanční tarify.
- **Quick Actions**: Seznam sezón, Vytvořit novou sezónu.

## 2. Historie změn a Audit logy
- **Slug**: `api-audit`
- **Audience**: Superadmin, Admin
- **Účel**: Jak zjistit, kdo a kdy provedl konkrétní změnu v datech.
- **Témata**: Filtrování podle uživatele, Detail změny (Old/New value), Sledování smazaných záznamů.
- **Quick Actions**: Prohlížet audit log.

## 3. Start nové sezóny (Master Scenario)
- **Slug**: `scenar-nova-sezona`
- **Audience**: Admin
- **Účel**: Komplexní checklist kroků pro spuštění nového roku (září).
- **Témata**: Sezóny -> Týmy -> Soupiska -> Finance -> QR platby.
- **Related Sections**: `system-sezony`, `predpisy-plateb`, `soupisky-clenstvi`.

## 4. Nábor a integrace nového hráče
- **Slug**: `scenar-nabor`
- **Audience**: Admin, Coach
- **Účel**: Cesta od zájemce na webu po platícího člena týmu.
- **Témata**: Lead -> User -> Team Assignment -> Finance Tariff -> Welcome Email.
- **Related Sections**: `evidence-clenu`.

## 5. Ukončení členství a odchod z klubu
- **Slug**: `scenar-odchod`
- **Audience**: Admin
- **Účel**: Jak správně "uklidit" profil odcházejícího člena.
- **Témata**: Vypnutí plateb, Kontrola dluhů, Deaktivace účtu, Historická data.

## 6. Branding a e-mailové šablony
- **Slug**: `branding-emaily`
- **Audience**: Superadmin
- **Účel**: Úprava barev, log a textů v automatických e-mailech.
- **Témata**: Globální nastavení, Barvy tlačítek, Footer webu, Odkazy na sociální sítě.

---

### FAQ Návrh
- **Otázka**: Co se stane, když inicializuji konfigurace sezóny dvakrát?
- **Odpověď**: Systém používá `updateOrCreate`, takže duplicity nevzniknou. Jen se přepíší data ze zdrojové sezóny.
- **Otázka**: Jak obnovím omylem smazaného uživatele?
- **Odpověď**: V Audit logu najdete ID smazaného záznamu, ale obnova vyžaduje zásah technického správce (databáze). V administraci není tlačítko "Undo".
