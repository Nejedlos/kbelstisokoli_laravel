# Plánované úlohy (Cron)

Plánované úlohy jsou "neviditelní pracovníci" systému KS. Na pozadí se starají o to, aby byla data vždy aktuální, aby odcházely e-maily a aby systém nebyl zahlcen starými soubory.

### Přehled úloh
V sekci **Systém > Plánované úlohy** vidíte seznam všech procesů:
- **Synchronizace plateb:** Stahuje pohyby z banky (typicky každou hodinu).
- **Synchronizace sportovních dat:** Stahuje výsledky a tabulky z ČBF.
- **Čištění cache:** Odstraňuje dočasné soubory pro zrychlení webu.
- **Odesílání notifikací:** Zpracovává frontu e-mailů a upozornění.

### Stav a monitoring
U každé úlohy vidíte její poslední stav:
- **Success (Zelená):** Úloha proběhla v pořádku.
- **Running (Modrá):** Úloha právě probíhá.
- **Failed (Červená):** Došlo k chybě. V takovém případě doporučujeme nahlédnout do **Logů úloh**, kde je popsána příčina selhání (např. výpadek API ČBF).

### Manuální spuštění (Run Now)
Někdy nechcete čekat na automatický interval (např. právě jste nahráli nový bankovní výpis nebo skončil zápas).
1. V seznamu úloh najděte tu správnou.
2. Použijte akci **Spustit nyní (Run Now)**.
3. Úloha se zařadí na začátek fronty a provede se okamžitě.

### Logy a historie
Sekce **Logy úloh** uchovává historii všech běhů. Pokud systém vykazuje neshody v datech, je toto první místo, kam by se měl administrátor podívat. Logy obsahují i technické detaily o počtu importovaných záznamů.

### Varování pro administrátory
- **Nemažte úlohy:** Pokud úlohu smažete, přestane se daná část systému aktualizovat. Pokud ji chcete jen dočasně zastavit, použijte pole "Aktivní".
- **Zacyklení:** Pokud spustíte ručně úlohu, která již běží, systém ji zařadí do fronty. Nepouštějte tutéž úlohu vícekrát za sebou v krátkém intervalu.
