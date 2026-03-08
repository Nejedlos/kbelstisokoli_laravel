# Produkční Rollout (Postup a Ověření)

Tento dokument popisuje proces bezpečného nasazení (rolloutu) statistik a importů na produkční prostředí Webglobe.

## 1. Příprava (Pre-flight)
Před nahráním kódu ověřte stav prostředí:
```bash
php artisan qa:preflight --prod
```
Tento příkaz kontroluje:
- `APP_ENV=production` a `APP_DEBUG=false`.
- Připojení k DB a stav migrací.
- Práva k zápisu do storage.
- Dostupnost scheduleru (heartbeat).
- Konfiguraci týmů pro aktivní sezónu.

## 2. Nasazení kódu (FTP/Deploy)
1. Nahrajte kód do složky `secret`.
2. Spusťte migrace: `php artisan migrate --force`.
3. Vyčistěte cache: `php artisan optimize:clear`.

## 3. Synchronizace dat
Po nasazení kódu spusťte synchronizaci aktuální sezóny pro oba hlavní týmy:
```bash
php artisan stats:sync-team-season muzi-c 2025/2026 --force
php artisan stats:sync-team-season muzi-e 2025/2026 --force
```

Pokud chybí konfigurace pro starší sezóny, spusťte discovery:
```bash
php artisan stats:season-discover --sync
```

## 4. Ověření stavu (Smoke Test)
Pro rychlou kontrolu, zda web běží a data jsou přítomna:
```bash
php artisan qa:smoke --prod
```
Kontroluje:
- Dostupnost URL (200 OK).
- Přítomnost zápasů a statistik v DB pro aktivní sezónu.
- Status posledních importů (zda nejsou chyby).

## 5. Legacy Import (Manuální)
1. Nahrajte HTML soubory v administraci přes **Legacy Stats Import**.
2. Spusťte dávkové zpracování.
3. Systém automaticky přidá varování, pokud pro danou sezónu již existují oficiální data z `czbasketball`.

## 6. Finální QA Run
Pro celkové ověření integrity a vygenerování reportu:
```bash
php artisan qa:run --prod --full
```
Tento příkaz na produkci **neresetuje** databázi, ale provede hloubkovou kontrolu invariantů a vygeneruje soubor `docs/prod-rollout-report.md`.

## 7. Řešení problémů
- Pokud `qa:smoke` hlásí chyby v importech, podívejte se do **External Import Runs** v administraci.
- Pokud selhává parser, zkontrolujte snapshoty v `storage/app/external/czbasketball/...`.
- V případě kritické chyby zastavte rollout a proveďte rollback kódu.
