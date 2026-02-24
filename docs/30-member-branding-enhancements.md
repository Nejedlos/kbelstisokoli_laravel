# Členská sekce – brandové vylepšení (barvy, basketball vibe)

Tento dokument popisuje vizuální vylepšení členské sekce s důrazem na klubový branding a basketbalovou identitu.

## Co se změnilo
- Přidány nové CSS utility třídy v `resources/css/app.css`:
  - `member-topbar` – gradientní horní lišta s jemným basketbalovým vzorem a akcentovou čárou.
  - `brand-stripe` – krátká horizontální čára s přechodem v klubových barvách (pro oddělení sekcí / navigace).
  - `sport-card-accent` – decentní pozadí karet s jemným „basketball court“ vzorem a červeným/blue světelným nádechem.
  - `kpi-icon-ring` – kruhový akcent kolem ikony na KPI kartách (ring + glow).
- Aplikace do UI:
  - `resources/views/layouts/member.blade.php`: horní bar přepnut na `member-topbar`; spodní mobilní navigace dostala `brand-stripe`.
  - `resources/views/components/member/kpi-card.blade.php`: KPI dlaždice nyní používají `sport-card-accent` a `kpi-icon-ring`, navíc obsahují chevron indikující akci.
  - `resources/views/member/dashboard.blade.php`: hlavní profilová karta na nástěnce má `sport-card-accent`.

## Důvody a zásady
- Vizuál je výrazněji klubový (navy + red) a „basketbalový“, ale stále decentní, aby nezasahoval do čitelnosti a použitelnosti.
- Změny jsou neinvazivní (nepřepisují globálně všechny `.card`), aplikují se cíleně pouze tam, kde je to žádoucí.

## Jak ověřit
1. Otevřete členskou sekci (Dashboard) a zkontrolujte:
   - Gradientní horní lištu s jemným vzorem a spodní akcentovou čarou.
   - KPI dlaždice s akcentovaným pozadím a zvýrazněným kruhem kolem ikony.
   - Profilovou kartu s jemným „basketball“ vzorem.
2. Zkontrolujte EN i CS lokalizaci (texty jsou beze změn, šlo o styl).
3. Ověřte mobilní zobrazení – spodní navigace má nahoře brandový proužek.

## Nasazení
- Nezapomeňte po změnách v CSS spustit build, aby se aktualizoval Vite manifest (produkce):

```
npm run build
```

> Pozn.: Na produkci dle `Envoy.blade.php` nebo přes System Console (příkaz „npm run build“). 

## Další rozšíření (volitelné)
- Postupná aplikace `sport-card-accent` do dalších karet (Docházka, Ekonomika, Notifikace) dle potřeb a preferencí.
- Možnost vázat intenzitu akcentů na hodnoty v `BrandingSettings` (např. „brand intensity“ slider).
