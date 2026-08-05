# Oprava chyby "Route not defined" u DMARC Authorized Senders

Tento dokument popisuje opravu chyby `Route [filament.admin.resources.dmarc-authorized-senders.index] not defined`, která se vyskytla v produkčním prostředí při renderování sidebaru administrace.

## Popis problému
Při pokusu o přístup do administrace docházelo k výjimce `Illuminate\View\ViewException`, protože systém nedokázal najít routu pro správu autorizovaných odesílatelů DMARC. I když soubory resource existovaly, routa nebyla v systému zaregistrována.

## Provedené změny

### 1. Sjednocení labels v DmarcAuthorizedSenderResource
Resource `DmarcAuthorizedSenderResource` postrádal explicitní definici labels, což mohlo způsobovat nekonzistentní chování při automatickém generování názvů. Byly přidány metody:
- `getModelLabel()`
- `getPluralModelLabel()`

### 2. Doplnění lokalizace
Byly doplněny chybějící překladové klíče do:
- `lang/cs/admin.php`
- `lang/en/admin.php`

Klíč: `dmarc_authorized_sender`

### 3. Explicitní registrace v AdminPanelProvider
Ačkoliv je v projektu zapnuto `discoverResources`, u DMARC modulů docházelo k problémům s detekcí (pravděpodobně kvůli vnořené struktuře adresářů v kombinaci s route cache). DMARC resources byly zaregistrovány explicitně v `app/Providers/Filament/AdminPanelProvider.php`:

```php
->resources([
    DmarcAuthorizedSenderResource::class,
    DmarcIncidentResource::class,
    DmarcMailboxResource::class,
    DmarcReportResource::class,
])
```

## Doporučení pro produkci
Pokud se chyba stále projevuje, je nutné na serveru vyčistit route cache:

```bash
php8.4 artisan optimize:clear
php8.4 artisan optimize
```

Tyto příkazy jsou součástí standardního deploy workflow popsaného v `docs/cs/06-ops/02-upgrade-laravel-13.md`.
