# UI Audit - Batch 06: Systém a scénáře

Tento audit mapuje technické sekce administrace a komplexní procesy ("Master How-to"), které zasahují do více modulů. Sjednoceno pod kategorií `system`.

## 1. Sezóny (Seasons)
- **Resource**: `SeasonResource`
- **Menu**: Sportovní agenda > Sezóny
- **Role**: Admin, Superadmin
- **Tabulka**: Název (2025/2026), Aktivní (Boolean).
- **Akce (Kritická)**: **Inicializovat konfigurace**
    - Slouží ke zkopírování nastavení hráčů (tarify, docházka) z minulé sezóny do nové.
- **Formulář**: Název, Přepínač Aktivní.

## 2. Historie změn (Audit Logs)
- **Resource**: `AuditLogResource`
- **Menu**: Systém > Historie změn
- **Role**: Admin, Superadmin
- **Tabulka**: Událost (Created/Updated/Deleted), Model (User, Post atd.), Kdo změnil, Datum, Změny (JSON).
- **Účel**: Forenzní analýza, když někdo omylem smaže data nebo změní důležité nastavení.

## 3. Plánované úlohy (Cron Tasks & Logs)
- **Resource**: `CronTaskResource`, `CronLogResource`
- **Menu**: Systém > Plánované úlohy
- **Role**: Superadmin
- **Tabulka**: Název úlohy, Interval, Poslední běh, Status.
- **Účel**: Sledování automatických procesů (synchronizace s cz.basketball, párování plateb).

## 4. Přesměrování a Chyby (Redirects & NotFound)
- **Resource**: `RedirectResource`, `NotFoundLogResource`
- **Menu**: Systém > Přesměrování / Logy 404
- **Role**: Admin
- **Účel**: Oprava nefunkčních odkazů na webu a SEO optimalizace.

---

## Komplexní procesy (Scénáře)

### SCÉNÁŘ A: Start nové sezóny
1. Vytvoření nové sezóny v `Seasons`.
2. Spuštění akce "Inicializovat konfigurace" (překlopení hráčů).
3. Hromadné vystavení předpisů pro novou sezónu v `FinanceCharges`.
4. Úprava soupisek týmů (přesun hráčů do vyšších kategorií).

### SCÉNÁŘ B: Nábor a onboardingu hráče
1. Zpracování leadu z webu (`Leads`).
2. Ruční vytvoření uživatele (`Users`).
3. Přiřazení k týmu a sezóně.
4. Nastavení finančního tarifu.
5. Odeslání pozvánky do systému.

### SCÉNÁŘ C: Ukončení členství (Exit process)
1. Kontrola dluhů ve financích.
2. Vypnutí automatických předpisů v `UserSeasonConfig`.
3. Deaktivace uživatele (is_active = false).
4. Odebrání ze soupisek aktivních týmů.

---

### Časté úkony v této sekci
1. Překlopení klubu do nové sezóny (srpen/září).
2. Kontrola, proč neproběhla automatická synchronizace dat.
3. Zjištění, kdo změnil barvu v nastavení brandingu.

### Rizika a nejasnosti
- **Změna aktivní sezóny**: Má okamžitý dopad na to, co vidí hráči v členské sekci (docházka, platby).
- **Audit logy**: Neukládají se věčně (čistí se cronem).
- **Cron**: Pokud neběží systémový cron na serveru, nápověda i automatické funkce selžou.
