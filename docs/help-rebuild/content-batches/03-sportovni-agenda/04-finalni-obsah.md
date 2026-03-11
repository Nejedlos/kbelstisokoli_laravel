# Finální obsah: Batch 03 – Sportovní agenda

Tato dávka (Batch 03) úspěšně naplnila systém nápovědy v2 obsahem týkajícím se sportovní agendy klubu. Všechny články jsou plně lokalizovány a integrovány do databázového seedovacího procesu.

## Seznam vytvořených článků

| Slug | Název (CS) | Název (EN) | Cílové role |
| :--- | :--- | :--- | :--- |
| `sprava-tymu` | Správa týmů a kategorií | Team and Category Management | admin, coach, super_admin |
| `soupisky-a-clenstvi` | Soupisky a členství v týmu | Rosters and Team Membership | admin, coach |
| `planovani-sezony` | Plánování a start nové sezóny | Planning and Starting a New Season | admin, super_admin |
| `treninky-a-dochazka` | Tréninky a docházka | Training and Attendance | admin, coach |
| `zapasy-a-nominace` | Zápasy a nominace hráčů | Matches and Player Nominations | admin, coach |
| `souperi` | Adresář soupeřů | Opponent Directory | admin, coach |
| `sportovni-udaje-hrace` | Sportovní údaje hráče | Player Sports Data | admin, coach, player, parent |
| `omlouvani-z-akci` | Omlouvání z akcí | Excusing from Events | player, parent |

## Zdroje a metodika
- **UI Audit**: Veškerý obsah vychází z přímé analýzy Filament komponent (`TeamResource`, `BasketballMatchResource`, `TrainingResource` atd.).
- **Vazby**: Dokumentace pokrývá komplexní workflow od startu sezóny až po zápis výsledků a omlouvání hráčů.
- **Lokalizace**: Všechny texty byly připraveny paralelně v češtině i angličtině.

## Potvrzené skutečnosti
- **Synchronizace**: Potvrzena funkčnost akcí pro synchronizaci z cz.basketball v rámci `TeamResource` a `BasketballMatchResource`.
- **Mismatch**: Vysvětlen termín "Mismatch" v kontextu docházky, který je v UI vizuálně zvýrazněn.
- **Hráčská karta**: Zdokumentovány všechny sekce tabu "Hráč" v profilu uživatele, včetně lékařských poznámek a správy fotografií.

## K ruční kontrole v UI
- **Omlouvání**: Ověřit přesné barvy v kalendáři na mobilním zařízení (článek zmiňuje Modrou, Zelenou, Šedou a Oranžovou/Červenou).
- **Zápisy**: Zkontrolovat, zda pole "Skóre" je v editaci zápasu pojmenováno přesně jako "Skóre" (v auditovaném kódu bylo patrné, ale label se může lišit podle překladu).

## Vytvořené / Upravené soubory
- 16x Markdown (`database/seeders/Help/content/cs/sport/*.md` a `database/seeders/Help/content/en/sport/*.md`)
- `database/seeders/Help/HelpArticleSeeder.php` (registrace 8 nových článků)
- `docs/help-rebuild/content-batches/03-sportovni-agenda/01-ui-audit.md`
- `docs/help-rebuild/content-batches/03-sportovni-agenda/02-navrh-clanku.md`
