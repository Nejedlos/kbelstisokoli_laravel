# Reset hesla – podepsaná URL a stabilita odkazů

V administraci (Filament v5) jsou odkazy pro reset hesla generovány jako **podepsané URL**. To zvyšuje bezpečnost, ale přináší riziko, že jakékoliv dodatečné query parametry (např. UTM/trackovací parametry přidané e‑mailovým klientem) zneplatní podpis a uživatel se nedostane na formulář pro nové heslo.

## Problém
- Někteří e‑mailoví klienti a proxy systémy automaticky přidávají k odkazům parametry typu `utm_source`, `utm_medium`, `trk`, apod.
- Middleware `ValidateSignature` pak vyhodnotí URL jako neplatnou a Filament uživatele přesměruje zpět na žádost o reset hesla.

## Řešení v projektu
Přidali jsme middleware `App\Http\Middleware\NormalizeSignedUrlParameters`, který běží v rámci `web` skupiny **před** `ValidateSignature` a pouze pro trasu resetu hesla v administraci:

- Povolené parametry: `signature`, `expires`, `email`, `token`.
- Všechny ostatní query parametry jsou ze `Request` odstraněny pro účely validace podpisu, bez přesměrování.
- Loguje se, které klíče byly odfiltrovány (`NormalizeSignedUrlParameters.strip`).

## Důsledky a doporučení
- Uživatelé mohou bezpečně klikat na odkazy v e‑mailech i přes případné UTM parametry.
- Neměňte způsob generování URL ve Filamentu (ponechte podepsané URL) – fix funguje bez zásahu do vendor kódu.
- Pokud do budoucna zavedeme jakékoliv další parametry na reset stránkách, rozšiřte whitelist v middleware.

## Ověření
- Otestujte kliknutí na odkaz pro reset hesla, ke kterému ručně přidáte `?utm_source=test&utm_medium=email`. Formulář pro nastavení nového hesla se musí zobrazit a proces dokončit.
