# Mobilní menu a návrat na přihlašovací stránku

## Zjištění

Produkční audit zaznamenal mobilní Chrome 151 s User-Agentem `Android 10; K`. Tento údaj neurčuje přesný model telefonu ani spolehlivě skutečnou verzi Androidu. Heslo bylo přijato, člen měl roli `player` a oprávnění `view_member_section`.

Na variantě `www.kbelstisokoli.cz` byly ikony reprodukovatelně prázdné: aplikace generuje assety na `kbelstisokoli.cz`, ale odpověď fontů nemá CORS hlavičku. Po přechodu na kanonickou doménu se načítají ze stejného originu.

Výchozí `Filament Login::mount()` přesměrovával již přihlášeného uživatele na administraci nebo neověřenou `url.intended`. Člen tak mohl po opětovném otevření přihlášení dostat 403 a přitom zůstat správně přihlášený. Regresní test před opravou prokázal oba chybné cíle. Přesnou 403 hlášeného uživatele dostupná analytika neobsahovala; propojení s jeho konkrétním požadavkem proto není potvrzené.

## Oprava

- `CanonicalHost` převádí GET/HEAD mezi aliasem www a hostitelem z `APP_URL`, zachovává cestu a query. POST ani jiné hostitele nepřesměrovává.
- Produkční early-exit cache smí vracet obsah jen na `kbelstisokoli.cz`, aby alias neobešel middleware. Při nasazení se toto omezení aplikuje také na skutečný externí `www/index.php`; ostatní obsah vstupního souboru se zachovává.
- Hamburger a křížek jsou inline SVG s vlastními rozměry a nejméně 44px tlačítkem; nejsou závislé na stahování fontů. Tlačítko má český/anglický přístupný název a vazbu na menu.
- Přihlášený návštěvník přihlašovací stránky používá `AuthRedirect`. Tento návrat nepotvrzuje heslo ani nemaže platné 2FA. Přístupová pravidla administrace a členské sekce se nemění.

## Ověření a nasazení

`MobileLoginTest`, `CanonicalHostTest`, existující auth/2FA testy, následně `composer test` a `npm run build`. Produkční změna vyžaduje zálohu dotčených souborů, nahrání nových assetů před manifestem, vyčištění Blade pohledů a pouze cachovaných veřejných stránek. Staré assety se zachovávají pro otevřené stránky. V prohlížeči ověřit přesměrování www, mobilní menu a odkazy na přihlášení.

## Produkční ověření 2. 9. 2026

Oprava nasazena přes SSH. Původní dotčené soubory a oba Vite manifesty jsou v neveřejné záloze `/home/html/kbelstisokoli.cz/tmp/mobile-menu-login-20260902/backup`. Externí `www/index.php` byl upraven pouze přidáním podmínky hostitele; jeho ostatní produkční úpravy zůstaly zachované. Produkční šablona `secret/public/index.production.php` early-exit cache neobsahovala, takže ji nebylo třeba měnit.

- GET i HEAD na www vrací 301; cesta `/login?lang=en` se zachová.
- Hlavní stránka a `/admin/login` vrací 200.
- V prohlížeči při šířce 390 px ověřeno viditelné tlačítko, otevření/zavření menu a podnabídka Program. Ikony vyhledávání a ostatních prvků se na hlavní doméně načítají.
- Zneplatněno 41 záznamů veřejné page cache a vyčištěny kompilované Blade pohledy. Session ani přihlašovací limity nebyly mazány.
- Regresní testy přihlášení, přesměrování a 2FA prošly; nové testy domén také. První běh `composer test` přerušil výchozí limit 300 sekund; opakovaný běh používá `COMPOSER_PROCESS_TIMEOUT=0`.

### Výsledek celé sady

Dokončený běh: **201 úspěšných, 8 přeskočených, 29 neúspěšných testů, 854 assertions**, čas 521,39 s. Sada není zelená. Nové `MobileLoginTest` a `CanonicalHostTest` i existující `AuthAccessTest`, `AuthRedirectTest` a `TwoFactorPipelineTest` prošly.

Selhání mimo rozsah opravy zahrnují neaktivní uživatele ve starých testovacích datech (feedback, média, IDOR), duplicitní permission v testu ikon, chybějící HTML fixtures statistik, chybu DTO importu a zastaralá očekávání e-mailů/nápovědy. Některé testy se liší podle pořadí spuštění (registrace tras a widget administrace). Vybrané selhávající testy byly ověřeny také s původním bootstrapem a přihlašovací třídou z HEAD; samostatné porovnání reprodukovalo 12 selhání a 1 chybu, samostatný QA test chybějící fixture. Test widgetu administrace samostatně prošel s původní verzí i společně s novými testy domény. Tyto nesouvisející testy a jejich očekávání nebyly touto opravou měněny.

## Doplnění 3. 9. 2026: Apache a diagnostika zařízení

Přesměrování `www.kbelstisokoli.cz` nyní zajišťuje první pravidlo v `public/.htaccess` (na produkci také skutečný document root `www/.htaccess`). Odpověď 308 zachovává HTTP metodu i tělo požadavku, cestu a query string. Pravidlo platí i pro statické soubory a požadavky obsloužené časnou cache. Původní middleware `CanonicalHost` a jeho registrace jsou odstraněny. Kontrola hostitele u časné cache zůstává obranným opatřením.

`.htaccess` přidává `Accept-CH` pro model, verzi platformy a úplné verze prohlížeče. `DeviceContextService` ukládá omezený a očištěný seznam klientských hlaviček do `metadata.device` auditu a HTTP analytiky, bez dalšího fingerprintingu či změny databázového schématu. Chybějící údaje se neodhadují. Na prvním požadavku ani v nepodporujícím prohlížeči nemusí být dostupné. Historický model uživatele 142 nelze zpětně určit.

Ověření doplnění: 10 cílených testů přihlášení a analytiky prošlo. Izolovaný Apache ověřil 308 pro GET, HEAD i POST, zachování query stringu, mezer a znaku `#` v zakódované cestě, bez přesměrování kanonického a lokálního hostitele. V odpovědích byla přítomna hlavička `Accept-CH`.
