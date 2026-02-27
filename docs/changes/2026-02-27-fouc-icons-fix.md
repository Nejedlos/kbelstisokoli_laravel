### Oprava problikávání velkých ikon (FOUC)

Byla provedena optimalizace načítání ikon Font Awesome pro zamezení efektu, kdy se při pomalém načítání stránky (zejména na mobilech) na zlomek sekundy zobrazí ikony jako obří textové znaky nebo glyfy.

#### Provedené změny:

1.  **Prioritizace načítání CSS:**
    - Import Font Awesome v `app.css` byl přesunut na začátek souboru, aby prohlížeč začal stahovat styly ikon co nejdříve.

2.  **Kritické inline CSS (Stabilizace):**
    - Do všech hlavních layoutů (`public`, `member`, `auth` a `admin`) byl přidán malý blok inline CSS přímo do `<head>`.
    - Tento blok zajišťuje, že elementy s třídou `fa-` mají okamžitě nastavené vlastnosti `display: inline-block`, `width: 1.25em` a `height: 1em`.
    - Ikony jsou dočasně skryty (`opacity: 0`), dokud nejsou plně načteny jejich styly.

3.  **Zviditelnění po načtení:**
    - Do hlavních CSS souborů (`app.css`, `filament-admin.css`, `filament-auth.css`) bylo přidáno pravidlo, které ikony zviditelní, jakmile je CSS balík připraven.

#### Výsledek:
Uživatelé již neuvidí "rozbitý" layout s obřími znaky během načítání. Ikony se buď plynule objeví ve správné velikosti, nebo zůstanou skryté po dobu několika milisekund, než je font připraven.
