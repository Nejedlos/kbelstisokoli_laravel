# Seeding dat (Naplnění databáze)

Projekt využívá robustní systém pro seedování dat, který podporuje idempotenci (bezpečné opakované spouštění) i fresh režim (promazání předem).

## 1. Architektura

Základem je **Master Seeder** a vlastní **Artisan command**, který obaluje standardní Laravel seedování o další funkce.

### Klíčové komponenty:
- **`Database\Seeders\GlobalSeeder`**: Centrální registr všech seederů, které tvoří základní stav aplikace. Obsahuje také seznam tabulek pro promazání.
- **`app:seed`**: Artisan command pro spouštění seedování.

## 2. Použití

Pro běžné nasazení na produkci nebo lokální update použijte:

```bash
php artisan app:seed
```

### Fresh režim (Promazání dat)
Pokud potřebujete promazat stávající data (např. vyčistit CMS stránky a začít znovu), použijte flag `--fresh`. **Pozor: Toto nevratně smaže data v dotčených tabulkách!**

```bash
php artisan app:seed --fresh
```

Na produkci command vyžaduje potvrzení, pokud nepoužijete `--no-interaction`.

### Spuštění konkrétního seederu
Můžete spustit i jen jeden konkrétní seeder, přičemž command se postará o správné jmenné prostory:

```bash
php artisan app:seed --class=CmsContentSeeder
```

## 3. Registrace nových seederů

Při vytvoření nového seederu (např. `NewsSeeder`) jej přidejte do pole `SEEDERS` ve třídě `Database\Seeders\GlobalSeeder`.

Pokud seeder vytváří data, která by se měla při `--fresh` režimu smazat, přidejte příslušné názvy tabulek do pole `TABLES_TO_WIPE` ve stejné třídě.

## 4. Idempotence

Všechny systémové seedery (`RoleSeeder`, `CronTaskSeeder`, `CmsContentSeeder`) jsou navrženy jako **idempotentní**. To znamená, že:
- Pokud záznam neexistuje, vytvoří se.
- Pokud záznam existuje, zaktualizuje se na výchozí hodnoty ze seederu (pokud je to žádoucí) nebo se ponechá.
- Používáme metody `updateOrCreate()` nebo `firstOrCreate()`.

Díky tomu je bezpečné spouštět `php artisan app:seed` opakovaně i po každém deployi.
