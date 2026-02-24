# Facelift vyhledávání a horní lišty (Modrá varianta)

Tento dokument popisuje změny provedené pro zlepšení viditelnosti vyhledávacích prvků a posílení modrého (accent) brandingu v členské sekci.

## Provedené změny

### 1. CSS (app.css)
- **Posílení gradientu:** V třídě `.member-topbar` byl navýšen kontrast modrého gradientu (`rgba(37, 99, 235, 0.3)`).
- **Brandová linka:** Spodní linka topbaru nyní plynule přechází z červené do modré.
- **Karty:** Decentní modrý nádech přidán i do `.sport-card-accent`.

### 2. Horní lišta (member.blade.php)
- **Vyhledávací pole:**
    - Zvýšena opacita pozadí (`bg-white/10`) pro lepší kontrast na tmavém navy podkladu.
    - Přidán vnitřní stín (`shadow-inner`) pro hloubku.
    - Fokus stav nyní používá modrou barvu (`focus:border-accent`).
- **AI Prvky:**
    - Ikony jisker a tlačítka AI vyhledávání nyní používají modrý akcent (`text-accent`, `bg-accent`).
    - Vylepšeny hover efekty (měřítko ikon, výraznější okraje).
- **Jazykový přepínač:** Aktivní jazyk je nyní modrý (`bg-accent`), což vyvažuje červenou barvu loga a ostatních prvků.
- **Notifikace:** Indikátor nepřečtených zpráv změněn na modrý.
- **Administrace:** Odkaz do adminu v uživatelském menu je nyní modrý.

## Technické poznámky
- Všechny změny využívají proměnnou `--color-accent`, která je mapována na `@color-brand-blue` (#2563eb).
- Pro projevení změn na produkci je nutný build assetů (`npm run build`).

## Ověření
1. Horní lišta by měla mít viditelný modrý nádech v pravé části.
2. Vyhledávací pole (AI i standardní) musí být jasně ohraničená a viditelná.
3. Při kliknutí do vyhledávání se musí zobrazit modrý okraj.
4. Jazykový přepínač a notifikace by měly svítit modře.
