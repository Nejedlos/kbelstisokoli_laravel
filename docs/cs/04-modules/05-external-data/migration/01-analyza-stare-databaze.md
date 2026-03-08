# Analýza staré databáze (`kbelstisokoli_old`) - Migrační plán

Tento dokument obsahuje detailní diagnostiku vybraných tabulek ze staré databáze, které jsou klíčové pro migraci do nového systému Kbelští sokoli. Na základě analýzy byly vyřazeny nedůležité technické tabulky, WP pozůstatky a mediální data.

## 1. Jádro: Členové a jejich profily

### Tabulka: `registrace`
Základní seznam členů klubu. V novém systému odpovídá modelům `User` (autentizace) a `Member` (klubová data).

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | Primární klíč (r_id v ostatních tabulkách). |
| **user** | varchar(128) | Login uživatele. |
| **email** | varchar(128) | E-mail (unikátní identifikátor). |
| **password** | varchar(32) | MD5 hash hesla (bude vyžadovat reset nebo specifický check). |
| **jmeno** | varchar(255) | Celé jméno člena. |
| **adresa** | varchar(255) | Kontaktní adresa. |
| **mobil** | varchar(20) | Telefonní číslo. |
| **cas** | int(11) | Timestamp registrace. |
| **hrom** | enum('y','n') | Souhlas s hromadnými e-maily. |
| **zpr_doch** | enum('y','n') | Notifikace týkající se docházky. |
| **novinky** | enum('y','n') | Odběr novinek. |
| **admin** | enum('1','0') | Příznak administrátora ve starém systému. |
| **zruseno** | enum('0','1') | Stav účtu (aktivní/neaktivní). |

### Tabulka: `web_soupiska`
Rozšiřující informace o hráčích, které doplňují profil člena.

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **prezdivka** | varchar(128) | Přezdívka hráče. |
| **jmeno** | varchar(128) | Jméno (pro párování, pokud chybí ID). |
| **cislo_dresu** | int(3) | Číslo dresu. |
| **vyska** | int(3) | Výška v cm. |
| **vaha** | int(3) | Váha v kg. |
| **post** | varchar(128) | Pozice (např. rozehrávač, pivot). |
| **charakteristika** | text | Slovní popis hráče. |
| **kariera** | text | Historie působení. |
| **byvali** | enum('ano','ne') | Příznak, zda jde o bývalého hráče. |

---

## 2. Sportovní činnost: Zápasy, tréninky a docházka

### Tabulka: `zapasy`
Centrální evidence všech akcí klubu (zápasy i tréninky).

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **datum** | date | Datum konání akce. |
| **cas** | varchar(10) | Čas zahájení. |
| **souper** | text | Název soupeře (pro zápasy). |
| **team** | int(5) | ID týmu, kterého se akce týká. |
| **kde** | enum('doma','venku') | Místo konání. |
| **druh** | enum(...) | Typ akce (Zápas, Trénink, Turnaj atd.). |
| **sport** | enum(...) | Basketbal / Volejbal. |
| **adresa** | text | Přesné místo konání. |
| **vysledek** | varchar(128) | Konečné skóre. |
| **stav** | enum('v','p','r','k') | Výsledek (Výhra, Prohra, Remíza, Kontumace). |
| **sezona** | varchar(20) | Sezóna (např. 2023/2024). |

### Tabulka: `dochazka`
Záznamy o omluvách a plánované účasti, které zadávají sami uživatelé.

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **id_zap** | int(11) | Vazba na konkrétní zápas/akci. |
| **r_id** | varchar(11) | Vazba na člena (registrace.id). |
| **dochazka** | varchar(120) | Stav: `ano` (přijde), `ne` (nepřijde), `omluven`. |
| **na** | date | Datum akce (redundantní k id_zap). |
| **sezona** | varchar(20) | Sezóna pro statistiky. |

### Tabulka: `web_realna_dochazka`
Záznamy o skutečné účasti na akci, pořízené trenérem/správcem na místě. Klíčové pro výpočet pokut v kombinaci s tabulkou `dochazka`.

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **zap_id** | int(11) | Vazba na akci (zapasy.id). |
| **datum** | int(22) | Timestamp pořízení záznamu. |
| **dochazka** | varchar(255) | Serializovaný nebo čárkou oddělený seznam přítomných ID. |
| **nebili** | text | Seznam těch, kteří chyběli bez omluvy. |
| **omluveno** | text | Seznam omluvených. |
| **sezona** | varchar(100) | Sezóna. |

---

## 3. Ekonomika: Platby a pokuty

### Tabulka: `web_platici`
Definuje členy, kteří jsou v dané sezóně zařazeni do ekonomického modulu (mají platit příspěvky).

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **r_id** | int(11) | Vazba na člena (registrace.id). |
| **druh** | int(11) | Typ plátce (vazba na sazebník `web_vypocty_platby`). |
| **sezona** | varchar(22) | Sezóna platnosti. |
| **uctovat_od/do** | int(11) | Rozsah měsíců/období, kdy se účtuje. |
| **osvobozen_od/do** | int(11) | Období prominutí plateb. |
| **hlidat_dochazku** | enum('ano','ne') | Příznak, zda se pro tohoto člena počítají pokuty z docházky. |
| **prevod_penez** | int(10) | Přeplatek/nedoplatek z minulé sezóny. |

### Tabulka: `web_platby`
Evidence skutečně přijatých plateb od členů.

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **p_id** | int(11) | Vazba na záznam v `web_platici`. |
| **kdy** | int(11) | Timestamp přijetí platby. |
| **kolik** | int(11) | Částka v Kč. |
| **typ** | enum('cash','banka') | Způsob úhrady. |

### Tabulka: `web_pokuty`
Evidence udělených pokut členům (např. za pozdní příchod, zapomenutý dres, technickou chybu).

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **p_id** | int(11) | Vazba na záznam v `web_platici`. |
| **druh** | int(11) | Typ pokuty (vazba na sazebník `web_vypocty_pokuty`). |
| **typ** | varchar(255) | Textový popis pokuty. |
| **castka** | int(5) | Výše pokuty. |
| **pocet** | int(10) | Násobitel (např. 3x nedaná šestka). |
| **kdy** | int(11) | Timestamp udělení. |
| **kdy_zap** | int(11) | Timestamp zaplacení (pokud je 0, je dluh aktivní). |

### Tabulka: `web_vypocty_platby` (Sazebník plateb)
Definuje "tarify" pro členské příspěvky.

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **nazev** | varchar(50) | Název tarifu (např. "Hrající člen", "Student"). |
| **pausal** | int(11) | Základní částka. |
| **jednotka** | varchar(100) | Časová jednotka (měsíc, sezóna). |
| **vypocet** | varchar(255) | Pomocný vzorec nebo popis výpočtu. |

### Tabulka: `web_vypocty_pokuty` (Sazebník pokut)
Definuje standardní výši pokut za různé prohřešky.

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(11) | PK. |
| **nazev** | varchar(50) | Název prohřešku (např. "Pozdní omluva", "Nedaná šestka"). |
| **pausal** | int(11) | Výše pokuty za jednotku. |
| **jednotka** | varchar(100) | Popis (např. "za zápas", "za kus"). |

---

## 4. Ostatní důležitá data

### Tabulka: `web_trophy`
Záznamy o klubových soutěžích a oceněních.

| Sloupec | Typ | Popis / Význam pro migraci |
| :--- | :--- | :--- |
| **id** | int(10) | PK. |
| **nazev** | varchar(255) | Název soutěže (např. "Střelec sezóny"). |
| **popis** | text | Podrobnější popis soutěže. |
| **prvni, druhy, treti** | varchar(255) | Jména nebo ID oceněných na 1.-3. místě. |
| **kdy** | date | Datum vyhlášení / sezóna. |

---

## 5. Ignorovaná data

Následující oblasti dat **nebudou** předmětem migrace do nového systému:
- **Média:** Všechny tabulky `foto_*`.
- **WordPress:** Všechny tabulky `wp_*`.
- **Týmy:** Tabulka `web_teamy` (týmy budou v novém systému definovány znovu).
- **Technické logy a statistiky:** `web_online`, `web_pristupy`, `web_backlinks`, `web_logovani_sql` atd.
- **Interakce:** `web_vzkazy`, `web_komentare`, `web_ankety`.
- **Zálohy:** Tabulky končící na `2` nebo `_zaloha`.
