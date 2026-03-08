# Slučování uživatelů a řešení duplicit

Tento dokument popisuje proces slučování uživatelů v systému Kbelští sokoli. Tato funkce je nezbytná zejména pro propojování automaticky vytvořených „Ghost“ profilů (vzniklých při synchronizaci statistik) se skutečnými uživatelskými účty.

## 1. Co je to Ghost uživatel?
Ghost uživatel je dočasný profil, který systém vytvoří v momentě, kdy během synchronizace statistik z externího zdroje (např. cz.basketball) narazí na jméno, které nedokáže spolehlivě přiřadit k žádnému existujícímu uživateli v naší databázi.
- **Identifikace:** Ghost uživatelé mají email ve formátu `ghost_{zdroj}_{id}@kbelstisokoli.cz`.
- **Účel:** Zajistit, aby statistiky nebyly ztraceny a byly dočasně uloženy pod tímto jménem, dokud administrátor neprovede spárování nebo sloučení.

## 2. Proces slučování (Merge)
Slučování probíhá přes `UserMergeService` a zajišťuje kompletní přenos všech historických dat ze zdrojového (zanikajícího) uživatele na cílového (zůstávajícího) uživatele.

### Co vše se přenáší?
Při sloučení uživatele A (zdroj) do uživatele B (cíl) dojde k převodu následujících dat:
1.  **Externí mapování:** Všechny vazby na externí zdroje (ID z cz.basketball) budou směřovat na uživatele B.
2.  **Statistiky:** Všechny odehrané zápasy a statistické řádky budou připsány uživateli B.
3.  **Hráčské profily:** Historie profilů (výška, váha, číslo dresu atd.) se převede pod uživatele B.
4.  **Docházka:** Kompletní historie účasti na trénincích a akcích.
5.  **Finance:** Všechny vystavené předpisy plateb a zaznamenané platby.
6.  **Rodinné vztahy:** Pokud byl uživatel A rodičem nebo dítětem někoho jiného, tyto vazby přebírá uživatel B.
7.  **Trenérské vazby:** Pokud byl uživatel A trenérem týmu, stává se jím uživatel B.
8.  **Role a oprávnění:** Uživatel B získá všechny role a oprávnění, které měl uživatel A (pokud je již nemá).
9.  **Auditní logy:** Historie akcí provedených uživatelem A bude v logu vidět pod uživatelem B.
10. **Média:** Fotografie a avatary nahrané uživatelem A budou zkopírovány k uživateli B.
11. **Systémové záznamy:** Vytvořené importy, AI logy, přesměrování a nahlášená zpětná vazba.

Po úspěšném přenosu je uživatel A **trvale smazán** a pro uživatele B je automaticky spuštěn **přepočet sezónních statistik**.

## 3. Administrace duplicit
V sekci **Správa uživatelů** jsou k dispozici nástroje pro identifikaci a řešení těchto stavů:

### Identifikace v tabulce
- <i class="fa-light fa-circle-exclamation text-warning"></i> **Žlutý vykřičník:** Signalizuje, že v databázi existuje jiný uživatel se stejným jménem.
- <i class="fa-light fa-ghost text-gray-400"></i> **Ikona ducha:** Označuje automaticky vytvořený Ghost profil.

### Způsoby sloučení
1.  **Individuální sloučení:** V menu akcí u konkrétního uživatele zvolte „Sloučit s...“ a vyberte cílového uživatele.
2.  **Hromadné sloučení (Doporučeno):**
    - Zaškrtněte v tabulce uživatele, které chcete zpracovat (nebo všechny).
    - V hromadných akcích zvolte **„Sloučit Ghosty automaticky“**.
    - Systém projde vybrané Ghost profily a pokud pro ně najde právě jednoho reálného uživatele se shodným jménem, automaticky provede sloučení.

## 4. Bezpečnostní pravidla
- Slučování je **nevratná operace**.
- Automatické hromadné sloučení proběhne pouze tehdy, pokud je shoda jména **jednoznačná** (existuje právě jeden reálný kandidát). Pokud je nalezeno více uživatelů se stejným jménem (např. otec a syn), systém záznam přeskočí a vyžaduje ruční zásah administrátora.
- Při konfliktu v sezónní konfiguraci (oba uživatelé mají nastavení pro stejnou sezónu) je upřednostněno nastavení cílového uživatele.
