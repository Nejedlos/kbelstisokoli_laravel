# Detekce 404 a správa přesměrování

Tento modul slouží k automatickému zachytávání požadavků, které skončily chybou 404 (Stránka nenalezena), a umožňuje jejich snadné přesměrování na správné cíle přímo z administrace.

## 1. Jak to funguje

1.  **Sledování (Logger):** Každý požadavek na web, který skončí chybovým kódem 404, je zaznamenán do tabulky `not_found_logs`. Logger ukládá URL, referer (odkud uživatel přišel), IP adresu a prohlížeč.
2.  **Agregace:** Pokud se stejná URL objeví vícekrát, logger pouze zvýší počítadlo (`hits_count`) a aktualizuje čas posledního výskytu.
3.  **Administrace:** V sekci **Admin nástroje > Detekce 404** vidí správce seznam všech nenalezených stránek seřazený podle počtu výskytů.

## 2. Správa nenalezených stránek

V tabulce detekce 404 máte u každého záznamu k dispozici tyto akce:

### Vytvořit přesměrování
Tato akce otevře formulář pro vytvoření nového pravidla přesměrování.
-   **Původní cesta:** Předvyplněna z logu.
-   **Cílová cesta (Návrh):** Systém se pokusí automaticky navrhnout nejvhodnější existující stránku, novinku nebo tým na základě shody v URL. Návrh můžete libovolně změnit.
-   **Kód:** Výchozí je 301 (Trvalé přesměrování).

Po vytvoření přesměrování se stav logu změní na **Přesměrováno**.

### Ignorovat
Pokud se jedná o neexistující soubor, který nechcete řešit (např. útoky botů na `wp-admin.php`, neexistující scany atd.), můžete záznam ignorovat. Tím se skryje z výchozího pohledu a nebude vás dále obtěžovat.

### Obnovit
Pokud jste záznam omylem ignorovali nebo přesměrovali, můžete jej vrátit do stavu "Čeká".

## 3. Systém přesměrování (Redirects)

Vlastní přesměrování probíhá pomocí `RedirectMiddleware`. Systém podporuje:
-   **Exact match:** Přesná shoda URL.
-   **Prefix match:** Přesměruje vše, co začíná daným řetězcem.
-   **Interní cíle:** Cesty v rámci webu (např. `/tymy/u11`).
-   **Externí cíle:** Kompletní URL na jiné weby (např. `https://facebook.com/...`).

Statistiky použití jednotlivých přesměrování jsou sledovány v sekci **Admin nástroje > Přesměrování**.

## 4. Technické detaily

-   **Model:** `App\Models\NotFoundLog`
-   **Middleware:** `App\Http\Middleware\NotFoundLoggerMiddleware`
-   **Suggester:** `App\Support\RedirectSuggester` (logika pro návrhy cílových URL)
-   **Tabulka v DB:** `not_found_logs`
