# Deployment & Synchronizace (cz.basketball)

Tento dokument obsahuje přesný návod pro nasazení a první spuštění nové hloubkové (excesivní) synchronizace dat z `cz.basketball` na produkční prostředí.

## 1. Příprava (Lokálně)
Před samotným nasazením se ujistěte, že máte v pořádku lokální stav a provedený push do repozitáře.

```bash
git add .
git commit -m "feat: excesivní synchronizace dat a správa hal"
git push origin main
```

## 2. Nasazení na produkci (SSH)
Přihlaste se na produkční server a proveďte standardní aktualizační kolečko.

```bash
# 1. Stažení kódu
git pull origin main

# 2. Instalace závislostí
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 3. Migrace databáze (Kritické)
# Tato verze obsahuje nové tabulky (venues) a rozšíření statistik
php artisan migrate --force

# 4. Pročištění cache
php artisan cache:clear
php artisan view:clear
php artisan config:cache
```

## 3. První spuštění synchronizace
Nová synchronizace je náročná na počet požadavků, proto doporučujeme první "excesivní" běh spustit manuálně pro kontrolu.

### A) Synchronizace konkrétního hráče (Test)
Pro ověření funkčnosti na jednom hráči (např. ID 108):
```bash
php artisan stats:sync-players --user_id=108 --excesive
```

### B) Hromadná synchronizace všech hráčů
Tento příkaz stáhne kompletní historii všech hráčů, kteří mají propojený profil na `cz.basketball`.
```bash
php artisan stats:sync-players --excesive
```

## 4. Automatizace (Cron)
V souboru `routes/console.php` byly přidány nové naplánované úlohy. Ujistěte se, že na serveru běží standardní Laravel Task Scheduler (`* * * * * php /cesta/k/projektu/artisan schedule:run >> /dev/null 2>&1`).

**Nové naplánované úlohy:**
1. **Denní sync (04:00):** `stats:sync-players` - synchronizuje aktuální data a zápasy.
2. **Týdenní excesivní sync (Neděle 02:00):** `stats:sync-players --excesive` - provádí hloubkovou kontrolu historie a doplňuje chybějící detaily starších zápasů.

## 5. Kontrola výsledků
Po dokončení synchronizace můžete v databázi nebo v administraci zkontrolovat:
- Tabulku `venues` (měly by tam být automaticky vytvořené haly).
- Tabulku `external_player_matches` (měla by obsahovat detailní statistiky jako asistence, doskoky atd.).
- Tabulku `matches` (měly by tam být automaticky založené zápasy našich týmů).

---
*Vytvořeno: 14. 3. 2026*
