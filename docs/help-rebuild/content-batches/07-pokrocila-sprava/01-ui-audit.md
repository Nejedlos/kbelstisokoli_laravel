# UI Audit - Batch 07: Pokročilá správa (Excesivní rozšíření)

## 1. Pool fotografií (Photo Pools)
- **Menu:** Obsah a média > Pool fotografií
- **Resource:** `PhotoPoolResource`
- **Klíčové prvky:**
    - **Hromadný import:** Vlastní `x-loader.basketball` při nahrávání (Glassmorphism design).
    - **AI Vylepšení:** Akce `regenerateAi` pro automatické popisky a tagy.
    - **Vazba:** Možnost propojení s `Team`, `ClubEvent` nebo `BasketballMatch`.
    - **Metadata:** Titulek (translatable), datum pořízení, autor.

## 2. Klubové soutěže (Club Competitions)
- **Menu:** Sportovní agenda > Klubové soutěže
- **Resource:** `ClubCompetitionResource`
- **Klíčové prvky:**
    - **Typy:** `custom` (vlastní turnaje), `cbf` (oficiální ČBF), `other`.
    - **Metadata:** Logo soutěže, popis, zobrazení na webu (nábor, homepage).
    - **Vazba:** Propojení s konkrétní sezónou.

## 3. Týmy (Teams) - Detailní správa
- **Menu:** Sportovní agenda > Týmy
- **Resource:** `TeamResource`
- **Klíčové prvky:**
    - **Branding:** Vlastní barvy týmu, logo, týmová fotka.
    - **Soupiska:** `PlayersRelationManager`.
    - **Párování:** `external_id` (pro synchronizaci výsledků).
    - **Kategorie:** Výběr věkové kategorie (např. U11, U13, Muži).

## 4. Finance (Platby a Předpisy)
- **Menu:** Finance > Platby / Předpisy
- **Resources:** `FinancePaymentResource`, `FinanceChargeResource`
- **Klíčové prvky:**
    - **Předpisy (Charges):** Hromadné generování z tarifů (`GenerateAction`), variabilní symboly.
    - **Platby (Payments):** Ruční zadání nebo import z banky (CSV/API).
    - **Alokace (Allocations):** `AllocationsRelationManager` - propojení platby s předpisem.
    - **Statusy:** Nezaplaceno, Částečně, Zaplaceno, Přeplatek.

## 5. Restart sezóny (Season Renewal)
- **Menu:** Systém > Restart sezóny
- **Page:** `SeasonRenewal.php`
- **Klíčové prvky:**
    - **Výběr sezón:** Zdrojová (stará) vs. Cílová (nová).
    - **Možnosti:** Převést aktivní členy, zachovat týmové soupisky.
    - **Důležité:** Resetování finančních předpisů pro novou sezónu.

## 6. Leady (Nábory a zájemci)
- **Menu:** Lidé a členové > Nábory (Zájemci)
- **Resource:** `LeadResource`
- **Klíčové prvky:**
    - **Zdroj:** Form na webu "Chci hrát".
    - **Data:** Jméno dítěte, rok narození, kontakt na rodiče.
    - **Workflow:** Změna stavu (Nový -> Kontaktován -> Přijat -> Zamítnut).
    - **Akce:** Nutno ručně založit `User` při přijetí (systém nenabízí 1-click convert z důvodu čistoty dat).

## 7. Partneři (Partners)
- **Menu:** Obsah a média > Partneři
- **Resource:** `PartnerResource`
- **Klíčové prvky:**
    - **Kategorie:** Generální, Hlavní, Mediální, Ostatní.
    - **Vizuál:** Logo, URL, priorita (řazení).
    - **Zobrazení:** Checkboxy pro homepage, patičku, zápasové odznaky.

## 8. Branding a nastavení systému
- **Menu:** Systém > Branding (Nastavení)
- **Page:** `BrandingSettings.php`
- **Klíčové prvky:**
    - **Identita:** Oficiální název, slogan, loga (velké, malé, vodoznak).
    - **Finance:** Bankovní účet, název banky (propisuje se do předpisů!).
    - **SEO:** Metadata, popisy, indexace.
    - **Hala:** Název haly, GPS, Google Maps embed (pro detail zápasu).

## 9. Párování a externí data
- **Menu:** Systém > Párování entit / Externí zdroje
- **Resources:** `ExternalEntityMappingResource`, `ExternalStatSourceResource`
- **Klíčové prvky:**
    - **Mappings:** Propojení `Player ID`, `Team ID` mezi KS a ČBF.
    - **Stats:** Nastavení odkud se tahají data (API url, API key).
    - **Intervaly:** Jak často probíhá sync.

## 10. Plánované úlohy (Cron)
- **Menu:** Systém > Plánované úlohy
- **Resource:** `CronTaskResource`
- **Klíčové prvky:**
    - **Přehled:** Kdy úloha naposledy běžela a s jakým výsledkem.
    - **Ruční spuštění:** Akce `RunNow` pro okamžitou synchronizaci (např. vynucení syncu plateb).
    - **Logy:** Detailní výpis chyb při selhání syncu.

---
**Poznámka:** Audit vychází z analýzy `app/Filament/Resources` a `app/Filament/Pages`.
**Ověřeno:** Existence všech modulů a jejich klíčových akcí v kódu.
