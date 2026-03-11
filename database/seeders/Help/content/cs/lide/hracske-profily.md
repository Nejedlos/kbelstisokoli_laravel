Sekce **Hráčské profily** se zaměřuje na sportovní historii a digitální identitu sportovce. Každý aktivní hráč má v systému svou historii rozdělenou podle sezón a týmů – tzv. **Sezónní konfigurace** (Stinty).

### Sezónní konfigurace a historie (Stinty)
Klíčovým konceptem systému je uchování historie působení hráče. Tato data určují, v jakém týmu, v jaké sezóně a s jakým číslem hráč nastupoval.
- **Kde to najít**: V detailu člena v dolní části stránky (Relation Manager "Sezónní konfigurace").
- **Co se eviduje**: Sezóna, Tým, Číslo dresu (pro konkrétní sezónu), Role v týmu (Hráč/Kapitán/Trenér).
- **Změna dresu**: Pokud hráč v průběhu sezóny změní dres, doporučujeme přidat nový záznam s novým číslem a platností, aby statistiky zápasů s původním číslem zůstaly historicky správné.

### Veřejný profil hráče na webu
Pokud má hráč aktivní "Hráčský profil" a je v aktuální sezóně přiřazen k týmu, systém pro něj automaticky generuje podstránku na webu kbelstisokoli.cz.
- **Viditelnost**: Lze ovlivnit globálně nebo individuálně v nastavení (přepínač "Zobrazit na webu" v modelu PlayerProfile).
- **Data na webu**: Zobrazuje se jméno (nebo zobrazované jméno), fotka (první z galerie), aktuální tým a číslo dresu.
- **Statistiky**: Systém automaticky agreguje data z odehraných zápasů (body, trojky, fauly) a docházky a zobrazuje je ve vizuálních grafech a tabulkách na profilu hráče.

### Práce s médii (Galerie hráče)
V detailu hráče (záložka Hráč) najdete sekci **Fotografie hráče**.
- **Primární fotka**: Systém vždy bere první fotografii v řadě jako "profilovou" pro soupisku týmu. Fotky lze měnit pouhým přetažením (drag & drop).
- **Zpracování fotek**: Nahrané fotografie jsou automaticky optimalizovány pro rychlé načítání na webu a v mobilní aplikaci.

### Sportovní výsledky a agregace
Hráčský profil slouží jako sběrné místo pro data z různých modulů:
1. **Docházka**: Procento účasti na trénincích za aktuální měsíc/sezónu.
2. **Zápasy**: Počet odehraných minut, bodový průměr, trestné hody.
3. **Mismatches**: Sledování "rozporů" v docházce (nahlášeno vs. skutečnost), což pomáhá trenérům v hodnocení spolehlivosti hráče.

### Jak vytvořit sportovní historii pro nového hráče?
1. Otevřete detail člena a záložku **Hráč**.
2. Zapněte **Aktivní hráčský profil**.
3. Srolujte dolů k tabulce **Sezónní konfigurace**.
4. Klikněte na **Nová sezónní konfigurace** a vyberte aktuální sezónu a tým.
5. Nastavte roli "Hráč" a zadejte číslo dresu. Uložte.
6. Od tohoto okamžiku se hráč objevuje v nominacích týmu na zápasy a tréninky.
