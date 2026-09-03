# Opravy regresních testů (3. 9. 2026)

## Výchozí stav
Celá PHP sada vykazovala 29 selhání a 8 přeskakovaných testů. Základní problémy byly chybějící HTML fixtures, neaktuální testovací účty a očekávání, přenos stavu mezi testy a několik chyb aplikačního kódu.

## Opravy aplikace
- Chybějící boxscore vytváří prázdné řádky DTO a samostatné upozornění; serializace již nevolá `toArray()` na metadatech.
- DOM extraktory používají pro sourozenecké prvky `nextAll()` a ošetřují nepřítomný obalový element. Tím správně načítají tabulky pod nadpisy a aktuální klub hráče.
- Plánovač detailních statistik používá v SQL vázaný čas místo databázově specifického `NOW()`.
- Hromadná změna týmů tréninků explicitně předává relační vícenásobný výběr do dat akce; aktualizují se pouze označené tréninky.
- Fallback sitemap vypisuje XML deklaraci bezpečně přes PHP, aby ji Blade nepovažoval za začátek PHP kódu.

## Opravy testů
- Syntetické HTML podklady pro CzBasketball a legacy import jsou verzované v `tests/Fixtures/Stats/`, bez závislosti na živém webu nebo místních historických datech. `.gitignore` má cílenou výjimku.
- Testy přístupu používají aktivní účty a skutečně přidělená oprávnění. Kontroly 401/403 zůstávají zachované; oprávnění ani middleware se kvůli výsledkům neuvolňují.
- Test registrace veřejných tras čistí předchozí resolved facade instance před připojením izolovaného routeru.
- Test limitu hlášení ověřuje hranici skutečně nakonfigurovaného limitu, s pevným časem a různými reporty.
- Test sitemap ověřuje generování mimo lokální předem vytvořený export, validní XML a aktuální kořenovou URL.
- Testovací data nápovědy obsahují administrátorskou sekci a seeder test používá existující slugs.
- Dříve přeskakované testy kontaktů používají současné Volt komponenty a ověřují příjemce i potvrzení. Administrátorské smoke testy pokrývají existující redakční resource Posts namísto již neexistující PageResource.
- Hromadné akce a URL parametry nápovědy se již nepřeskakují. Zastaralé PHPUnit anotace `@test` jsou nahrazené atributy `#[Test]`.

## Ověření
Cílené skupiny prošly. Celá sada: **241 úspěšných testů, 1 105 assertions, 0 selhání, 0 přeskočených**, za 490,55 s. Finální ověření: `COMPOSER_PROCESS_TIMEOUT=0 composer test` (SQLite v paměti), formátování `vendor/bin/pint --dirty` a kontrola `git diff --check`.

Běžný příkaz `composer test` nově používá `Composer\Config::disableProcessTimeout`, protože celá sada přesahuje výchozích 300 sekund. Konfigurace prošla `composer validate --no-check-publish` a callback byl ověřen samostatným spuštěním Composeru.

## Falešný diagnostický alarm
E-mail `Diagnostic request not recorded` z 08:38:49 vytvořil dočasný SSH skript `tmp/device-20260903/verify.php`, který bootoval Laravel a při nenalezeném testovacím záznamu vyhodil výjimku. Nešlo o chybu návštěvníka. Následující kontrola veřejného požadavku potvrdila ukládání Client Hints. Dočasné skripty `verify.php` a `check-analytics.php` byly odstraněny; hlášení skutečných aplikačních chyb zůstalo zapnuté.

## Izolace režimu screenshotu
Společný běh odhalil statický stav `ScreenshotMode`, který po podepsaném odkazu z docházkového e-mailu zůstal aktivní a změnil vykreslení dalšího administračního požadavku. Dvojice testů `signed_email_action_records_attendance` a `test_auth_admin_sees_widget_in_admin_panel` problém reprodukovala. Middleware nyní stav inicializuje pro každý požadavek a uklízí jej v `finally`, tedy i při výjimce. Nové regresní testy ověřují dobu platnosti příznaku i odstranění uloženého ID. Podmínky autorizace a výjimky 2FA se nemění.
