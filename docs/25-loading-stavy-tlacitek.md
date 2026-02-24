# Loading stavy tlačítek (Loadery)

Tento dokument popisuje způsob implementace a používání loading indikátorů (točítek) u tlačítek v projektu, zejména v kontextu administrace a autentizačních stránek.

## 1. Architektura
V projektu existují dva hlavní mechanismy pro zobrazování loaderů:

### A. Nativní Filament/Livewire loader (`.fi-processing`)
- **Třída:** `.fi-processing`
- **Zdroj:** Přidáváno automaticky frameworkem Livewire při probíhajícím požadavku na prvky s atributem `wire:loading` nebo přímo na submit tlačítka formulářů.
- **Využití:** Standardní Filament resources, tabulky a formuláře v administraci.

### B. Projektový loader (`.is-loading`)
- **Třída:** `.is-loading`
- **Zdroj:** `resources/css/filament-auth.css` a `resources/js/filament-auth.js`.
- **Využití:** Vlastní (custom) layouty a stránky, zejména **Auth stránky** (Login, Reset hesla atd.).
- **Princip:** Loader je realizován jako pseudo-element `::after` u tlačítka s animací `spin`.

## 2. Důležité pravidlo (Prevence duplicity)
Na **auth stránkách** (které používají `filament-auth.css`) je **zakázáno** definovat styly pro loader u třídy `.fi-processing`.

Pokud by obě třídy (`.is-loading` i `.fi-processing`) měly v CSS definován stejný vizuální styl (např. přes `::after`), při odeslání formuláře by se na tlačítku zobrazily **dva loadery současně**, protože Livewire přidá `.fi-processing` a náš skript přidá `.is-loading`.

### Správný postup:
- Pro vlastní (projektové) komponenty, kde chceme mít plnou kontrolu nad vzhledem a plynulostí (bez layout shiftu), používáme výhradně třídu `.is-loading`.
- V souboru `resources/css/filament-auth.css` udržujeme definici loaderu pouze pro `.is-loading`.

## 3. Ukázka implementace
V souboru `resources/js/filament-auth.js` je implementován hook, který zajišťuje přidávání a odebírání třídy:

```javascript
function attachSubmitLoading(root) {
    const form = Q('form[wire\\:submit], form[wire\\:submit\\.prevent]', root);
    const submit = Q('button[type="submit"]', form);

    const start = () => submit.classList.add('is-loading');
    const stop = () => submit.classList.remove('is-loading');

    form.addEventListener('submit', start);

    if (window.Livewire) {
        Livewire.hook('request', ({ succeed, fail }) => {
            succeed(() => stop());
            fail(() => stop());
        });
    }
}
```

## 4. CSS definice
V `filament-auth.css` vypadá definice následovně:

```css
.fi-btn.is-loading {
    position: relative !important;
    pointer-events: none !important;
    opacity: 0.8 !important;
}

.fi-btn.is-loading::after {
    content: '';
    width: 1.1rem;
    height: 1.1rem;
    border: 2px solid rgba(255,255,255,0.6);
    border-top-color: white;
    border-radius: 9999px;
    animation: spin 0.8s linear infinite;
}
```

## 5. Globální basketbalový loader (KS)

Pro dlouhotrvající operace (AI odpovědi, importy, generování reportů) je k dispozici globální overlay loader s moderním, minimalistickým vzhledem v duchu Glassmorphismu.

- **Design:** Transparentní navy pozadí s výrazným rozmazáním (`blur(12px)`), minimalistická ikona v branding barvách a pulzující text.
- **CSS:** Součást `resources/css/filament-admin.css` (sekce „Globální basketbalový loader (KS)“).
- **Implementace:** Blade komponenta `resources/views/components/loader/basketball.blade.php`.
- **Chování:** Využívá `wire:loading.delay` pro zobrazení až po krátké prodlevě (prevence "problikávání").

### 5.1 Použití na stránce

Vložte komponentu uvnitř Livewire komponenty (např. Filament Page):

```blade
<x-loader.basketball />
```

Loader je standardně skrytý (`hidden`) a automaticky se zobrazí při probíhající Livewire akci. Pokud potřebujete loader vázat jen na konkrétní akci, použijte `wire:target` na obalový element:

```blade
<div wire:loading.delay wire:target="generateReport" wire:loading.class.remove="hidden" class="hidden">
    <x-loader.basketball />
</div>
```

### 5.2 Pravidla a doporučení
- Na Auth stránkách nadále NEstylingujeme `.fi-processing`. Loader řešíme projektově přes `.is-loading` nebo přes globální overlay podle potřeby.
- Loader je navržen tak, aby nezpůsoboval layout shift tlačítek a obsahu (overlay).
- Po změně vzhledu vždy spusťte `npm run build` (viz Správa assetů).
