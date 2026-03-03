# QA Jak na to (How-to) – Spouštění testů a QA nástrojů

Tento dokument popisuje, jak používat sadu QA nástrojů pro ověření funkčnosti systému statistik.

## 1. Rychlá kontrola prostředí (Preflight)
Před spuštěním jakýchkoliv testů nebo po novém nasazení na server použijte tento příkaz k ověření DB konektivity, čitelnosti souborů a přítomnosti klíčových tříd.

```bash
php artisan qa:preflight
```

## 2. Spuštění automatizovaných testů
Projekt využívá standardní Laravel testovací framework (Pest/PHPUnit). Hlavní QA sada se nachází v `tests/Feature/QA`.

```bash
# Spuštění kompletní QA sady
php artisan test tests/Feature/QA/QAMasterTest.php

# Spuštění všech testů v projektu
php artisan test
```

## 3. Brutální Smoke Run (QA:RUN)
Tento příkaz provede end-to-end simulaci reálného provozu. **Varování:** Při použití přepínače `--full` dojde ke kompletnímu vymazání databáze a novým migracím.

```bash
# Provede smoke test na aktuálních datech (bezpečné)
php artisan qa:run

# Provede kompletní reset DB a plný smoke run (vhodné pro CI/dev)
php artisan qa:run --full
```

## 4. Testování s reálnými daty (Legacy Stats)
Pokud chcete otestovat import historických dat, umístěte HTML soubory do složky:
`storage/app/legacystats`

Následně spusťte `qa:run` nebo použijte admin rozhraní v sekci "Legacy Stats Import".

## 5. Troubleshooting
Pokud testy selhávají:
1. Zkontrolujte `storage/logs/laravel.log`.
2. Ověřte, zda existují HTML fixtures v `tests/Fixtures/Stats/CzBasketball/`.
3. V případě 302 redirectů u UI testů ověřte nastavení 2FA u testovacích uživatelů (middleware `EnsureTwoFactorEnabled`).
