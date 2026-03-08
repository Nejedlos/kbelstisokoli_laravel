# Oddělení kontaktních údajů: Patička vs. Administrátor (Branding Settings)

## Přehled
- V nastavení brandingu (Filament > Branding) byly dosud v jedné sekci smíchány kontakty pro patičku webu a administrátorský kontakt pro členskou sekci.
- Tyto dvě role nejsou totožné osoby, proto byly nastavení rozdělena do dvou samostatných sekcí.

## Co se změnilo
- Sekce „Kontaktní údaje (Patička)“ nyní obsahuje pouze veřejné kontakty, které se zobrazují v patičce webu:
  - Kontakt E‑mail (`contact_email`)
  - Kontaktní telefon (`contact_phone`)
  - Adresa / Sídlo (`contact_address`)
- Nová sekce „Administrátorský kontakt (Členská sekce)“ obsahuje údaje pro komunikaci členů s administrátorem:
  - E‑mail administrátora (`admin_contact_email`) – pokud není vyplněn, používá se `ERROR_REPORT_EMAIL` z `.env`
  - Jméno administrátora (`admin_contact_name`)
  - Telefon administrátora (`admin_contact_phone`)
  - Fotografie administrátora (`admin_contact_photo_path`)

## Důvod změny
- Administrátor je jiná osoba než veřejný kontakt v patičce, což vedlo k nejasnostem a chybnému vyplnění.
- Rozdělení zvyšuje srozumitelnost a snižuje riziko chybné konfigurace.

## Dopad na kód a data
- Klíče v databázi (tabulka `settings`) zůstávají beze změny – jedná se pouze o přesun položek do dvou sekcí ve Filamentu.
- Neproběhla žádná migrace ani refaktor modelů/služeb. Vykreslení patičky i směrování zpráv z členské sekce funguje beze změny.

## Jak postupovat (Admin)
1. Otevřete Filament administraci > Branding.
2. V sekci „Kontaktní údaje (Patička)“ vyplňte veřejné kontakty zobrazené v patičce.
3. V sekci „Administrátorský kontakt (Členská sekce)“ vyplňte údaje osoby, která spravuje členský systém (cílový e‑mail pro zprávy členů atd.).
4. U fotografie administrátora preferujte portrét na bílém pozadí.

## Lokalizace
- Přidán překlad pro novou sekci `sections.admin_contact` v `lang/cs/admin/branding-settings.php` a `lang/en/admin/branding-settings.php`.

## Ověření
- Otevřete stránku Filament > Branding a zkontrolujte, že se zobrazují dvě samostatné sekce s příslušnými poli.
- Na veřejném webu zkontrolujte patičku (zobrazuje pouze „veřejné“ kontakty).
- Ověřte odeslání zprávy z členské sekce – pokud není vyplněn `admin_contact_email`, použije se `ERROR_REPORT_EMAIL` z `.env`.
