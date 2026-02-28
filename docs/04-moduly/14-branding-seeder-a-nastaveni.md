# Branding Seeder a automatické nastavení systému

Tento modul zajišťuje, aby základní identita klubu, kontaktní údaje a ekonomické informace (číslo účtu) byly v systému vždy správně vyplněny, a to i po čisté instalaci nebo synchronizaci dat.

## BrandingSeeder
Vytvořili jsme dedikovaný seeder `BrandingSeeder`, který naplňuje tabulku `settings` výchozími hodnotami definovanými pro klub Kbelští sokoli.

### Hlavní spravované údaje:
- **Identita:** Název klubu, zkrácený název, slogan.
- **Ekonomika:** Číslo bankovního účtu (`6022854477/6363`) a název banky.
- **Kontakty:** E-maily, telefony a adresy pro vedení klubu i pro veřejnost.
- **Lokace:** Informace o hale RumcajsArena (adresa, GPS, odkaz na mapu).
- **Design:** Výchozí barevné téma, varianty hlavičky a patičky.
- **SEO:** Výchozí popisky pro vyhledávače a roboty.
- **Výkon:** Nastavení optimalizací (cache, minifikace).

## Automatizace (app:sync)
Příkaz `php artisan app:sync` byl rozšířen tak, aby při každém spuštění (na lokálu i na produkci) automaticky spustil `BrandingSeeder`.

Tím je zajištěno, že:
1. Při prvním nasazení je systém okamžitě připraven k použití.
2. Pokud někdo omylem smaže nebo poškodí základní nastavení v administraci, další synchronizace jej vrátí do "oficiálního" stavu.
3. Vývojáři mají na lokálním prostředí vždy aktuální produkční defaulty bez nutnosti ručního nastavování.

## Použití v kódu
Nastavení jsou dostupná přes `BrandingService`:
```php
$branding = app(BrandingService::class)->getSettings();
echo $branding['bank_account']; // 6022854477/6363
```

V Blade šablonách jsou globálně dostupná přes proměnnou `$branding`.

## Ruční spuštění
Pokud potřebujete vynutit přepsání nastavení na výchozí hodnoty:
```bash
php artisan db:seed --class=BrandingSeeder
```
