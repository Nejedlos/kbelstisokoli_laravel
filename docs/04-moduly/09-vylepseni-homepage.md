# Vylepšení hlavní stránky (Video pozadí a Fallback)

Tento dokument popisuje, jak implementovat a spravovat video pozadí v Hero sekci na hlavní stránce.

## 1. Požadavky na video soubor
Aby web zůstal rychlý a video se správně přehrávalo, musí soubor splňovat tyto parametry:

- **Umístění:** `public/assets/video/hero.mp4` (název souboru může být libovolný, cesta se nastavuje v administraci).
- **Formát:** MP4 s kodekem **H.264** (nejlepší kompatibilita).
- **Rozlišení:** Full HD (1920x1080) pro desktop, nebo HD (1280x720) pro lepší výkon.
- **Datový tok (Bitrate):** Nízký, ideálně kolem 2-3 Mbps.
- **Velikost souboru:** Doporučeno **pod 10 MB**.
- **Audio:** Video **nesmí obsahovat zvukovou stopu** (nebo musí být zcela ztlumeno), jinak jej prohlížeče automaticky nespustí (autoplay policy).
- **Délka:** Ideálně 10–20 sekund s plynulou smyčkou (loop).

## 2. Fallback (Záložní řešení)
Vždy je nutné mít nastavený **obrázek** v poli "Obrázek / Fallback". Tento obrázek slouží jako:
1. **Poster:** Zobrazí se okamžitě, než se video stáhne a spustí.
2. **Fallback:** Zobrazí se v případě, že prohlížeč video nepodporuje nebo je v úsporném režimu (časté u mobilů).

## 3. Správa v administraci
1. Přejděte do **Administrace -> Obsah -> Stránky**.
2. Upravte stránku **Home** (nebo vytvořte novou).
3. V bloku **Hero sekce** najdete sekci **Vzhled a styl**.
4. Do pole **Video pozadí (URL)** vložte cestu k souboru (např. `assets/video/hero.mp4`).
5. Ujistěte se, že máte vybraný i **Obrázek / Fallback**.

## 4. Technické detaily
- Implementace využívá tag `<video>` s atributy `autoplay`, `muted`, `loop` a `playsinline`.
- CSS třída `object-cover` zajišťuje, že video vždy vyplní celou plochu sekce bez deformace.
- Tmavý překryv (`overlay`) je aplikován i na video pro zajištění čitelnosti textu.
