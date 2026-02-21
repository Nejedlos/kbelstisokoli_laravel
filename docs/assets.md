# Správa Assetů (Vite, CSS, JS)

Tento dokument popisuje, jak správně spravovat a nasazovat CSS a JavaScriptové assety v projektu Kbelští sokoli.

## 1. Vite Konfigurace
Všechny hlavní vstupní body pro CSS a JS musí být definovány v souboru `vite.config.js`. Pokud přidáte nový samostatný soubor, který chcete vkládat přes `@vite`, musíte ho přidat do pole `input`:

```javascript
// vite.config.js
laravel({
    input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/css/filament-auth.css', // Nový CSS soubor
        'resources/js/filament-error-handler.js' // Nový JS soubor
    ],
    refresh: true,
}),
```

## 2. Použití v šablonách (Blade)
Assety vkládáme standardně pomocí direktivy `@vite`:

```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
```

## 3. Assety v Administraci (Filament)
Pro robustní vložení vlastních assetů do Filament panelu (např. pro login nebo globální úpravy) používáme v `AdminPanelProvider.php` metodu `renderHook`. 

Vkládáme je do `panels::head.end`, aby byly dostupné v hlavičce HTML:

```php
// AdminPanelProvider.php
->renderHook(
    'panels::head.end',
    fn (): string => \Illuminate\Support\Facades\Blade::render(<<<'HTML'
        @isset($__vite_main) @else
            @vite(['resources/css/filament-auth.css', 'resources/js/filament-error-handler.js'])
            @php $__vite_main = true; @endphp
        @endisset
    HTML),
)
```
*Poznámka: Podmínka `@isset($__vite_main)` zabraňuje vícenásobnému vložení stejných assetů, pokud je hook volán vícekrát.*

## 4. Generování Manifestu (Kritické)
Když přidáte nový soubor do `vite.config.js`, **vždy** musíte spustit build, aby se aktualizoval `public/build/manifest.json`. Bez toho Laravel (Vite) vyhodí `ViteException: Unable to locate file in Vite manifest`.

```bash
npm run build
```

## 5. Časté problémy
- **ViteException (Manifest):** Znamená, že soubor v manifestu chybí. Spusťte `npm run build`.
- **Nefunkční styly/skripty v Adminu:** Zkontrolujte, zda není aktivní agresivní HTML minifikace, která může rozbít dynamické prvky (např. odstraňováním uvozovek). V administraci by měla být minifikace vypnutá.
- **Pořadí načítání:** Pokud váš skript závisí na jiných knihovnách (např. Font Awesome), ujistěte se, že jsou buď importovány v CSS/JS, nebo načteny dříve.
