# Rozsah a pravidla bezpečnostního auditu

## Cíl auditu
Provést autorizovaný bezpečnostní audit administrace a souvisejícího Laravel projektu pro web `new.kbelstisokoli.cz`.

## Rozsah (Scope)
- Celý Laravel projekt (zdrojový kód).
- Konfigurace (.env, config/*.php).
- Routy, Controller, Middleware, Policies, Gates.
- Autentizace a autorizace (Filament, Fortify, Spatie Permission).
- Databázové operace a Eloquent modely.
- API endpointy.
- Background joby a notifikace.
- Frontend (Blade, Livewire, Folio).

## Pravidla zapojení (Rules of Engagement)
1. **Obranný a nedestruktivní přístup:** Žádné mazání dat nebo destruktivní změny.
2. **Bezpečnost produkce:** Žádné testy, které by mohly shodit nebo zpomalit produkční server (DoS).
3. **Ochrana dat:** Žádná exfiltrace citlivých dat mimo lokální auditní výstupy.
4. **Transparentnost:** Všechny kroky a nálezy budou dokumentovány v `docs/security-audit/`.
5. **Opravy:** Implementace oprav až po schválení nálezu (v rámci tohoto procesu).

## Klasifikace závažnosti (Severity)
- **CRITICAL:** Okamžité riziko kompromitace celého systému nebo úniku všech dat.
- **HIGH:** Vysoké riziko zneužití, např. neautorizovaný přístup k adminu nebo manipulace s klíčovými daty.
- **MEDIUM:** Riziko vyžadující specifické podmínky k zneužití, únik méně citlivých dat.
- **LOW:** Malé riziko, technické nedokonalosti, info leamy.
- **INFO:** Doporučení pro "best practices", která nemají přímý bezpečnostní dopad.
