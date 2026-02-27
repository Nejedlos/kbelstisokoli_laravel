# Ekonomický modul (Economy Management)

Tento modul slouží ke správě financí, členských příspěvků a pokut. Je navržen tak, aby umožňoval historické sledování platební morálky a snadné "překlápění" konfigurací mezi sezónami.

## 1. Datový model

### Finanční tarify (`FinancialTariff`)
Definují sazebník členských příspěvků.
- **Vlastnosti:** Název, částka, jednotka (měsíc/sezóna).
- **Příklady:** "Hrající člen", "Student", "Senior".

### Sezónní konfigurace uživatele (`UserSeasonConfig`)
Klíčová entita, která drží stav uživatele v konkrétním čase (sezóně). Nahrazuje starou tabulku `web_platici`.
- **Vazby:** Uživatel + Sezóna (unikátní dvojice).
- **Parametry:**
    - Přiřazený tarif.
    - Rozsah měsíců účtování (`billing_start/end`).
    - Rozsah měsíců osvobození (`exemption_start/end`).
    - Příznak sledování docházky (`track_attendance`).
    - Počáteční zůstatek (převod z minulé sezóny).

### Předpisy (`FinanceCharge`)
Jednotlivé dluhy uživatele (paušály, pokuty).
- **Stavy:** Otevřeno, Částečně zaplaceno, Zaplaceno, Zrušeno, Po splatnosti.

### Platby (`FinancePayment`)
Evidence příchozích peněz (banka, hotovost).

### Alokace (`ChargePaymentAllocation`)
Propojuje platby s předpisy. Umožňuje, aby jedna platba pokryla více předpisů (nebo naopak).

## 2. Klíčové procesy

### Snapshoty a nová sezóna
Pro snadnou správu je k dispozici mechanismus automatického upozornění a hromadné inicializace:

1. **Dashboard Warning**: Pokud po 1. září aktuálního roku neexistují finanční konfigurace pro novou sezónu, zobrazí se administrátorům na dashboardu výrazné upozornění.
2. **Hromadná inicializace**: Odkaz z dashboardu vede na stránku **"Hromadná inicializace sezóny"** (`/admin/season-renewal`).
3. **Průběh inicializace**:
    - Administrátor zvolí cílovou sezónu.
    - Může načíst data z předchozí sezóny (stisknutím tlačítka "Načíst z předchozí sezóny").
    - V přehledném seznamu (Repeater) může u každého člena upravit tarif, počáteční zůstatek nebo vypnout sledování docházky.
    - Lze přidávat nové členy nebo odebírat ty, kteří v nové sezóně nebudou aktivní.
    - Po uložení se vytvoří/aktualizují všechny záznamy najednou.

### Debug mód
Pro testování upozornění a procesu inicializace lze použít URL parametr `?debug=1` (např. `/admin?debug=1`). Tento parametr vynutí zobrazení widgetu na dashboardu i v případě, že konfigurace již existují, a na stránce inicializace automaticky přednačte data.

### Integrace s docházkou
Ekonomický modul ovlivňuje sportovní agendu přes pole `track_attendance` v `UserSeasonConfig`.
- Pokud má uživatel v dané sezóně sledování docházky vypnuto (např. dlouhodobé zranění, trenér bez herní povinnosti), systém u něj **negeneruje rozpory** (mismatch) mezi nahlášenou a skutečnou účastí.

## 3. Administrace (Filament)

### Správa tarifů
Slouží k definici základních částek. Změna tarifu se neprojeví zpětně u již vygenerovaných předpisů, což zajišťuje finanční integritu.

### Sezónní profily
V detailu každého uživatele je záložka **"Historie plátce"**, kde je vidět vývoj jeho tarifů a zůstatků napříč sezónami.

## 4. Migrace historických dat
Data byla zmigrována ze starého systému s následujícím mapováním:
- `web_vypocty_platby` -> `FinancialTariff`.
- `web_platici` -> `UserSeasonConfig`.
- Automaticky vygenerovány `FinanceCharge` pro členské příspěvky na základě paušálů v původní DB.
- Platby a pokuty byly spárovány pomocí algoritmu chronologické alokace (nejstarší dluhy nejdříve).
