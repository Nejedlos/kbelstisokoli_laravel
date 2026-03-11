# Feature Flag a přepnutí help systému

Tento dokument popisuje mechanismus přepínání mezi starou (v1) a novou (v2) verzí help systému v projektu Kbelští sokoli.

## 1. Princip přepínání

Přepínání je realizováno pomocí konfigurace a proměnné prostředí (feature flag). To umožňuje bezpečné testování nové verze v dev/test prostředí, zatímco produkce může dočasně běžet na staré verzi.

- **v1:** Původní systém založený na Markdown souborech v `docs/help/`.
- **v2:** Nový systém založený na databázi, seederu a moderním UX.

## 2. Konfigurace

Hlavní nastavení se nachází v souboru `config/help.php`:

```php
return [
    'version' => env('HELP_SYSTEM_VERSION', 'v2'),
];
```

V souboru `.env` (nebo v prostředí) můžete verzi změnit:

```env
# Pro použití starého systému
HELP_SYSTEM_VERSION=v1

# Pro použití nového systému (výchozí)
HELP_SYSTEM_VERSION=v2
```

## 3. Technická realizace

### 3.1 Filament Page
Stránka `App\Filament\Pages\Help` byla upravena tak, aby dynamicky vybírala:
- **View:** `help-v1.blade.php` nebo `help-v2.blade.php`.
- **Service Layer:** `App\Services\HelpService` (v1) nebo `App\Services\Help\HelpService` (v2).

### 3.2 Zpětná kompatibilita
Aby zůstala zachována funkčnost v1 systému, byly do `Help` stránky přidány legacy metody:
- `getHelpData()`
- `getCategoryInfo()`
- `getFile()`

Původní logika procházení souborů a parsování markdownu zůstává nedotčena v `App\Services\HelpService`.

## 4. Postup přepnutí v produkci

1. **Příprava:** Ujistěte se, že byla spuštěna migrace a seeder pro nový systém:
   ```bash
   php artisan migrate
   php artisan db:seed --class="Database\Seeders\Help\HelpSeeder"
   ```
2. **Import (volitelně):** Pokud chcete zachovat starý obsah v novém systému:
   ```bash
   php artisan help:import-legacy
   ```
3. **Přepnutí:** V `.env` souboru změňte:
   ```env
   HELP_SYSTEM_VERSION=v2
   ```
4. **Ověření:** Navštivte stránku nápovědy v administraci a zkontrolujte, zda se zobrazuje nové rozhraní.

## 5. Odstranění starého systému

Starý systém (v1) by měl být odstraněn až po úplném ověření a přemigrování veškerého obsahu do databáze. Poté bude možné smazat:
- `app/Services/HelpService.php`
- `resources/views/filament/pages/help-v1.blade.php`
- `docs/help/` (adresář s původními MD soubory)
- Legacy metody v `App\Filament\Pages\Help.php`
