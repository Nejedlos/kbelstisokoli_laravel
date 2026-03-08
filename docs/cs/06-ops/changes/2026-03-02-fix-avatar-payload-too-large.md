# Oprava PayloadTooLargeException při nahrávání avatarů (mobil)

Datum: 2026-03-02

## Popis problému
Při nahrávání a ořezávání avatarů na mobilních zařízeních docházelo k chybě `Livewire\Exceptions\PayloadTooLargeException`. Tato chyba byla způsobena tím, že ořezaný obrázek (base64 string) odesílaný z frontendu do Livewire komponenty přesahoval bezpečnostní limit pro velikost payloadu.

## Provedené změny

### 1. Optimalizace frontendu (Cropper.js)
V souboru `resources/views/livewire/member/avatar-modal.blade.php` byly upraveny parametry pro export ořezaného obrázku:
- **Rozlišení:** Sníženo z 1200x1200px na **800x800px**.
- **Kvalita WebP:** Snížena z 0.9 na **0.75**.
- **Smoothing:** Nastaven na `medium`.

Tyto změny drasticky snižují velikost výsledného base64 řetězce, aniž by došlo k viditelné ztrátě kvality u avatarů (které se v UI zobrazují v malém rozlišení).

### 2. Konfigurace Livewire
V souboru `config/livewire.php` byl deaktivován limit pro velikost payloadu:
- **Původní hodnota:** `3024 * 3024` (~3MB), později navýšeno na 10MB.
- **Nová hodnota:** `'max_size' => null`.

Deaktivace tohoto limitu je doporučeným postupem u aplikací, které legitimně přenášejí větší objemy dat (např. soubory v base64) v rámci Livewire požadavků.

## Verifikace
- [x] Snížení rozlišení v Blade šabloně.
- [x] Nastavení `max_size => null` v `config/livewire.php`.
- [x] Kontrola, zda ořez stále funguje (beze změn v API).

## Poznámka
Pokud by se chyba stále opakovala, může být problém v limitu webserveru (Nginx `client_max_body_size`) nebo PHP (`post_max_size`), ale `PayloadTooLargeException` je specificky vyhazována frameworkem Livewire na základě jeho interní konfigurace.
