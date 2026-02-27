# Strategie migrace dat (`kbelstisokoli_old` -> Nový systém)

Tento dokument definuje technické mapování mezi starou databází a novými modely systému Kbelští sokoli.

## 1. Uživatelé a profily

Transformace tabulky `registrace` na model `User` a doplňující data do `PlayerProfile`.

### Detailní mapování uživatelů (`registrace` -> `User`)

| Staré pole | Nové pole | Logika transformace |
| :--- | :--- | :--- |
| `id` | `metadata->legacy_r_id` | Uložení pro budoucí migrace a integritu. |
| `user` | `metadata->legacy_username` | Původní login (pro referenci). |
| `jmeno` | `name` | Celé jméno (zachováno v původním znění). |
| `jmeno` | `last_name`, `first_name` | Rozdělení "Příjmení Jméno" (první slovo = příjmení, zbytek = jméno). |
| `email` | `email` | Bez změny. |
| `password` | `password` | Náhodné heslo. Uživatel si jej vyresetuje. |
| `mobil` | `phone` | Normalizace na E.164. |
| `admin` | Role: `admin` | Pokud `admin = '1'`, bude uživateli přiřazena role `admin`. Ostatní `player`. |
| `hrom` | `notification_preferences` | JSON: `{'hromadné_zprávy': true/false}`. |
| `zpr_doch` | `notification_preferences` | JSON: `{'zprávy_o_docházce': true/false}`. |
| `novinky` | `notification_preferences` | JSON: `{'novinky_na_webu': true/false}`. |
| `adresa` | `address_street` | Předpokládá se ulice a č.p. |

### Detailní mapování profilu (`web_soupiska` + `registrace` -> `PlayerProfile`)

Každý zmigrovaný uživatel dostane `PlayerProfile`, i když neexistuje v `web_soupiska`.

| Staré pole | Nové pole | Logika transformace |
| :--- | :--- | :--- |
| `cislo_dresu` | `jersey_number` | Převod na integer. |
| `vyska` | `height_cm` | Převod na integer. |
| `vaha` | `weight_kg` | Převod na integer. |
| `post` | `position` | Mapování textu na enum `BasketballPosition`. |
| `charakteristika`| `public_bio` | Textová pole ze soupisky. |
| `kariera` | `private_note` | Textová pole ze soupisky. |
| `team` (registrace)| `primary_team_id` | Mapování: 1->muzi-c, 2->muzi-e, 3->muzi-c (oba). |
| `team` (registrace)| `player_profile_team` | Synchronizace s pivot tabulkou (tým 3 se napojí na oba týmy). |
| `byvali` (soupiska) | `is_active` | Pokud `byvali = 'ano'`, `is_active` je `false`. |
| `byvali` (soupiska) | `membership_status` | Pokud `byvali = 'ano'`, status je `former`. |
| `zruseno` (reg) | `is_active` | Pokud `zruseno = '1'`, `is_active` je `false`. |
| `zruseno` (reg) | `membership_status` | Pokud `zruseno = '1'`, status je `inactive`. |

## 2. Sportovní činnost (Události a docházka)

Starý systém nerozlišoval modely, vše bylo v `zapasy`. My dělíme na `BasketballMatch` a `Training`.

| Stará tabulka | Nový model | Mapování a logika |
| :--- | :--- | :--- |
| `zapasy` (druh=zápas) | `BasketballMatch` | `souper`, `datum`, `cas`, `vysledek`, `stav` (V/P/R). |
| `zapasy` (druh=trénink) | `Training` | `datum`, `cas`, `adresa` (místo). |
| `dochazka` | `Attendance` | Plánovaná docházka uživatele (`ano` -> `attended`, `ne` -> `absent`, `omluven` -> `excused`). |
| `web_realna_dochazka` | `Attendance` | Skutečná docházka zapsaná trenérem. Přepíše nebo doplní status v modelu `Attendance`. |

**Klíč pro omluvy vs. realitu:** 
- `Attendance` bude mít dva typy statusů nebo příznaků (např. `is_official` pro trenérův zápis).
- Ve starém systému `web_realna_dochazka` obsahovala seznamy ID. To bude nutné rozparsovat a vytvořit/aktualizovat záznamy v `attendances` tabulce pro každého uživatele.

## 3. Ekonomika (Finance)

Toto je nejkomplexnější část kvůli víceúrovňové vazbě přes `p_id`.

### Logika vazeb:
1. `registrace.id` (staré) = `r_id` v `web_platici`.
2. `web_platici.id` = `p_id` v `web_platby` a `web_pokuty`.

### Mapování:

| Stará tabulka | Nový model | Mapování a logika |
| :--- | :--- | :--- |
| `web_platici` | - | Slouží jako propojovací tabulka. ID z této tabulky použijeme k nalezení správného `user_id`. |
| `web_platby` | `FinancePayment` | `kolik` -> `amount`, `kdy` -> `paid_at`, `typ` -> `payment_method`. Vazba na `user_id` přes `web_platici.r_id`. |
| `web_pokuty` | `FinanceCharge` | `castka * pocet` -> `amount_total`, `typ` -> `description`. Status `paid`, pokud `kdy_zap > 0`. |
| `web_platici` (paušál) | `FinanceCharge` | Vygenerujeme předpisy pro členské příspěvky na základě `druh` (tarif ze sazebníku) a `uctovat_od/do`. |

**Důležité:** Každá sezóna může mít pro stejného uživatele jiné `p_id`. Migrační skript musí vždy dohledat `r_id` -> `user_id` a k němu přiřadit platbu/pokutu.

## 4. Klubové soutěže

| Stará tabulka | Nový model | Mapování a logika |
| :--- | :--- | :--- |
| `web_trophy` | `ClubCompetition` | `nazev`, `popis` -> `name`, `description`. |
| `web_trophy` | `ClubCompetitionEntry` | Pole `prvni`, `druhy`, `treti` se převedou na samostatné záznamy s odpovídající hodnotou (rankem). |

## 5. Technické detaily migrace

- **Legacy ID:** Pro každou entitu budeme v `metadata` (JSON) ukládat původní ID ze staré DB (např. `legacy_r_id`, `legacy_p_id`, `legacy_z_id`). To umožní spouštět migraci opakovaně bez duplicit (idempotence).
- **Transformace textů:** Pole `kariera`, `charakteristika` (web_soupiska) a další texty jsou ve formátu Texy!. Pro nový web je převedeme pomocí regulárních výrazů na Markdown nebo HTML.
- **Sezóny:** Nejdříve musíme naplnit model `Season` (např. "2023/2024") a na něj navázat všechny události a finance.
- **Týmy:** Jelikož týmy v nové DB již existují, migrace bude vyžadovat mapování (např. Team ID 5 ve staré DB = "Muži A" v nové DB).

## 6. Realizace migrace (Seedery)

Konkrétní implementace migrace je rozdělena do několika specializovaných seederů. Jejich detailní popis, logiku transformací a pořadí spouštění naleznete v dokumentu:
**[Detailní popis migračních seederů](03-popis-seederu.md)**

## 7. Chybějící tabulky / modely

Většinu modelů již máme připravenou. Budeme však muset:
1. Rozšířit `User` nebo `Member` o pole pro staré ID (pokud nechceme používat jen JSON).
2. Přidat do `FinanceCharge` a `FinancePayment` pole `metadata` pro uložení legacy referencí.
3. Ověřit, zda `BasketballMatch` a `Training` pokrývají všechna pole ze starých `zapasy`.
