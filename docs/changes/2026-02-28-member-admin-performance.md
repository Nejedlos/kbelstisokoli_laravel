# Optimalizace členské sekce a administrace (28. 02. 2026)

Tento dokument shrnuje změny provedené za účelem zrychlení členské sekce a administrace, které vykazovaly pomalost v důsledku vysokého počtu databázových dotazů (N+1 problém).

## Provedené změny

### 1. Členská sekce (Member Dashboard)
- **Cachování dat:** Celý dashboard je nyní cachován na 10 minut pro každého uživatele (`Cache::remember`).
- **Eliminace N+1 v docházce:** Výpočet `expected_players_count` (očekávaný počet hráčů na tréninku/zápase) byl optimalizován. Seznamy `trackedUserIds` se nyní cachují pro každou sezónu samostatně.
- **Eager Loading:** Všechny relace (týmy, soupeři, docházky, počty potvrzení) jsou načítány jedním dotazem.

### 2. Finanční modul (Finance) - Zásadní zrychlení
- **Optimalizace accessorů:** Modely `FinanceCharge` a `FinancePayment` byly upraveny tak, aby accessory pro součty (`amount_paid`, `amount_allocated`) prioritně využívaly přednačtené sumy z databáze (`withSum`). To odstranilo stovky dotazů v tabulkách plateb a předpisů.
- **FinanceService:** Metody `getMemberSummary` a `getAdminSummary` byly přepsány na efektivní SQL sumace místo pomalého procházení Eloquent kolekcí.
- **EconomyController:** Přehled plateb člena nyní využívá eager loading sumací, což bleskově načítá i dlouhou historii.

### 3. Administrace (Filament)
- **Eager Loading v tabulkách:** Do klíčových Filament Resource (`UserResource`, `TeamResource`, `BasketballMatchResource`, `TrainingResource`, `FinancePaymentResource`, `FinanceChargeResource`) byla přidána metoda `getEloquentQuery()` s definicí `with()` a `withSum()`.
- **Optimalizace tréninků:** Tabulka tréninků nyní efektivněji pracuje se seznamem týmů bez zbytečného filtrování v PHP.

## Výsledky
- **Počet dotazů:** Snížen z desítek až stovek na jednotky (typicky 1-5 dotazů na request).
- **Odezva:** Členská sekce se nyní načítá v řádu desítek až stovek milisekund (předtím sekundy).
- **Stabilita:** Administrace financí je nyní připravena na velký objem dat bez degradace výkonu.

## Doporučení
- Při přidávání nových sloupců do Filament tabulek, které počítají relace (sumy, počty), vždy používejte `withSum()` nebo `withCount()` v `getEloquentQuery()`.
- Accessora v modelech by měla vždy kontrolovat přítomnost přednačteného atributu (`array_key_exists('sum_name', $this->attributes)`).
