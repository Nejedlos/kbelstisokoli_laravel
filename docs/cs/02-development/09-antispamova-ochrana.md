# Antispamová ochrana e-mailů (Mailto)

Tento dokument popisuje systém globální ochrany e-mailových adres na webu Kbelští sokoli, který zabraňuje automatizovaným botům v jejich sběru (scraping).

## 1. Princip fungování
Ochrana využívá kombinaci kódování na straně serveru (PHP) a dynamického dekódování na straně klienta (JavaScript):

1.  **Server (PHP):** E-mailová adresa je zakódována pomocí standardu Base64.
2.  **HTML Výstup:** Namísto adresy je do atributu `data-protected-email` vložen Base64 řetězec a atribut `href` je nastaven na `javascript:void(0)`.
3.  **Klient (JS):** Po načtení stránky skript vyhledá všechny chráněné elementy, dekóduje adresu a nastaví korektní `href="mailto:..."`.

## 2. Použití v kódu (Blade)
Pro vkládání e-mailových odkazů v šablonách **vždy** používejte komponentu `<x-mailto>`.

### Základní použití (e-mail jako text)
```blade
<x-mailto email="info@kbelstisokoli.cz" />
```
*Výsledek: Zobrazí dekódovaný e-mail, který je zároveň klikacím odkazem.*

### Použití s vlastním textem
```blade
<x-mailto email="info@kbelstisokoli.cz">
    Napište nám zprávu
</x-mailto>
```

### Použití s ikonou nebo HTML obsahem
```blade
<x-mailto email="info@kbelstisokoli.cz" class="btn btn-primary">
    <i class="fa-light fa-envelope mr-2"></i> Poslat e-mail
</x-mailto>
```

### Dynamické nahrazení v textu
Pokud potřebujete e-mail vložit doprostřed věty jako text, použijte zástupný symbol `[email]`:
```blade
<x-mailto email="info@kbelstisokoli.cz">
    Kontaktujte nás na [email] pro více informací.
</x-mailto>
```

## 3. Technické detaily

### Blade Komponenta
Umístění: `resources/views/components/mailto.blade.php`
Provádí `base64_encode()` e-mailové adresy a zajišťuje čistý HTML výstup.

### JavaScript Dekodér
Umístění: `resources/js/app.js` (funkce `window.initEmailProtection`)
Skript je inicializován při událostech:
- `DOMContentLoaded` (první načtení)
- `livewire:navigated` (navigace v členské sekci přes Livewire)

### Ukázka HTML pro boty (Scrapery)
Bot v HTML kódu uvidí pouze toto, což je pro něj bezvýznamné:
```html
<a href="javascript:void(0)" data-protected-email="aW5mb0BrYmVsc3Rpc29rb2xpLmN6">
    Napište nám zprávu
</a>
```

## 4. Důležité poznámky
- **Nefunkční JavaScript:** Pokud má uživatel vypnutý JavaScript, odkaz nebude funkční. V dnešní době a u tohoto typu webu (vyžadující JS pro členskou sekci) je to akceptovatelné riziko.
- **Build assetů:** Po jakékoliv úpravě JS logiky v `app.js` je nutné spustit `npm run build`.
- **Atributy:** Komponenta podporuje předávání libovolných HTML atributů (class, id, title atd.) přes `$attributes`.
