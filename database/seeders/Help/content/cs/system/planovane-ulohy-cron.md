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

Pokud se úloha přeruší limitem času, systém ji označí jako Failed a zaznamená důvod. Nespouštějte ji opakovaně ručně; po odstranění příčiny ji nechte proběhnout v dalším termínu podle rozvrhu, případně ji spusťte ručně právě jednou.

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

### Dočasný výpadek schránky pro DMARC reporty
Pokud import DMARC reportů hlásí `[UNAVAILABLE] Temporary authentication failure`, poštovní server dočasně nedokázal ověřit přihlášení. Detail zůstává uložen u DMARC schránky a v logu importu; již zaznamenaná chyba připojení nemá vyvolat další aplikační hlášení při ukončení požadavku. Další plánovaný import připojení zkusí znovu. Při opakovaném selhávání ověřte dostupnost schránky u poskytovatele a její přístupové údaje. Samotné toto hlášení není důvodem k okamžité změně hesla.
