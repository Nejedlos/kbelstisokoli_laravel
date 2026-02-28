# Fresh synchronizace ekonomiky

Tento dokument popisuje, jak provést čistou (fresh) synchronizaci finančních dat, pokud dojde k nesrovnalostem nebo duplikaci dlužných částek.

## Problém s duplikací
Při opakovaném spouštění migračních seederů bez promazání dat mohlo v minulosti docházet k přibývání dlužných částek. Tento problém byl technicky vyřešen v `FinanceMigrationSeeder` přidáním striktní filtrace podle typu předpisu (`membership_fee` vs `fine`).

## Jak provést Fresh Sync

Pokud přesto potřebujete začít s čistým štítem, máte dvě možnosti:

### 1. Přes administraci (System Console)
1. Přejděte do **Administrace -> Systém -> Systémová konzole**.
2. V sekci **🔄 Synchronizace dat** najděte **Finance: Sync**.
3. Zaškrtněte příznak `--fresh` (Fresh - vymaže stará data).
4. Klikněte na **Spustit**.

Tato akce provede:
- Smazání všech alokací plateb.
- Smazání všech plateb.
- Smazání všech předpisů (dlužných částek).
- Znovunaplnění dat z legacy databáze (přes `FinanceMigrationSeeder`).
- Následnou synchronizaci statusů.

*Poznámka: Pro import dat je nutné mít v `.env` správně nakonfigurované připojení `old_mysql`.*

### 2. Přes terminál (Artisan)

Pro úplné vyčištění a znovunaplnění dat (vyžaduje přístup k `old_mysql` DB):

```bash
# Vyčištění dat
php artisan finance:cleanup --force

# Znovunaplnění dat (pokud je dostupná stará DB)
php artisan db:seed --class=FinanceMigrationSeeder
```

Nebo v rámci celkové synchronizace:

```bash
php artisan app:sync --finance-fresh
```

## Důležité upozornění
Akce **Fresh Sync** je nevratná a smaže i data, která byla v novém systému zadána ručně (pokud nebyla součástí migračních seederů). Vždy doporučujeme provést zálohu databáze před spuštěním těchto příkazů na produkci.
