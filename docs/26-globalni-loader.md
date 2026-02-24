# Globální basketbalový loader (KS)

Tento dokument popisuje použití globálního overlay loaderu s basketbalovým míčem pro delší operace (AI vyhledávání, importy, generování výstupů).

## 1. Přehled
- Loader je implementován kombinací CSS a Font Awesome ikony (Light varianta).
- Vzhled a animace jsou součástí `resources/css/filament-admin.css`.
- Komponenta se vykresluje přes Blade partial: `resources/views/components/loader/basketball.blade.php`.
- Jako vizuální prvek používá ikonu `fa-basketball` (míč), která je v projektu standardem pro zápasy.
- Zobrazení je řízeno Livewire direktivami – `wire:loading.delay` a `wire:loading.class.remove="hidden"`.

## 2. Použití
Vložte komponentu do Livewire komponenty (např. Filament Page, Resource page):

```blade
<x-loader.basketball />
```

Loader je výchozím stavem skrytý (`hidden`) a objeví se automaticky při probíhající Livewire akci na dané komponentě.

### 2.1 Použití s Alpine.js (např. při redirectu)
Pokud potřebujete loader zobrazit manuálně (např. při odeslání formuláře, který dělá redirect), můžete použít `x-show`:

```blade
<div x-data="{ loading: false }">
    <x-loader.basketball x-show="loading" x-cloak />
    <form @submit="loading = true">...</form>
</div>
```

Komponenta automaticky detekuje přítomnost `x-show` a v takovém případě nepoužije `wire:loading` (aby nedocházelo ke konfliktům).

### 2.2 Cílení na konkrétní akci
Potřebujete‑li loader vázat pouze na jednu akci/metodu, použijte kontejner s `wire:target`:

```blade
<div wire:loading.delay wire:target="generate" wire:loading.class.remove="hidden" class="hidden">
    <x-loader.basketball />
</div>
```

## 3. Styling a přizpůsobení
- Overlay třída: `.ks-loader-overlay` – plnoobrazovkové překrytí s garantovaným centrováním (využívá `width: 100vw` a `height: 100dvh`) a vysokým z-indexem.
- Obsah: `.ks-loader-content` (obsahuje kontejner pro míč, text a animace).
- Vyvážení: `.ks-ball-container` – fixní kontejner, který zajišťuje, že se míč a stín pohybují v definovaném prostoru a nepřenášejí posun na okolní elementy.
- Animovaný prvek: `.ks-basketball-icon` – minimalistická Font Awesome ikona s plynulou animací odrazu (bounce) v rozsahu +/- 25px pro vizuální stabilitu.
- Popisek: `.ks-loader-text` – animovaný text s pulzující průhledností.
- Dynamický stín: Realizován přes pseudo-element `::after` u `.ks-ball-container`.

Barvy používají primární brand odstín přes CSS variables injektované v `AdminPanelProvider`.

## 4. Best practices
- Na Auth stránkách zachovejte pravidlo: nedefinovat loader pro `.fi-processing`; používejte `.is-loading` nebo globální overlay dle potřeby.
- Loader by neměl blokovat kritické interakce déle, než je nezbytné – používejte `wire:loading.delay`, aby se zobrazoval až po krátkém zpoždění a nepřeskakoval při rychlých odpovědích.
- Po každé změně stylů spusťte `npm run build` (viz `docs/06-sprava-assetu.md`).

## 5. Příklady nasazení
- AI Search page (`App\Filament\Pages\AiSearch`) má loader vložený přímo ve view `resources/views/filament/pages/ai-search.blade.php`.
- Obecné dlouhé akce v Resource akcích (např. Action::make('reindex')->action(...)) mohou použít wrapper s `wire:target`.
