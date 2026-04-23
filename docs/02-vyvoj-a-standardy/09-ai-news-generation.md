# Automatické generování novinek pomocí AI

Tento dokument popisuje systém pro automatické týdenní generování aktualit (článků) na základě dat z klubového života (zápasy, výsledky, události).

## 1. Účel modulu
Cílem je udržovat web aktivní a informovat fanoušky o dění v klubu, i když redaktoři nemají čas psát podrobné reportáže ke každému zápasu. AI (OpenAI) shromáždí data za uplynulý týden a vytvoří poutavý souhrn v češtině i angličtině.

## 2. Technický popis
Systém se skládá ze tří částí:
1.  **Služba `App\Services\AiNewsService`**:
    - Shromažďuje data z modelů `BasketballMatch` a `ClubEvent` (posledních 7 dní a výhled na dalších 7 dní).
    - Komunikuje s OpenAI API (využívá model `gpt-4o` nebo `gpt-4o-mini` dle nastavení).
    - Formátuje prompt a parsuje JSON odpověď obsahující titulky, perexy a obsah v obou jazycích.
2.  **Artisan příkaz `app:news:generate-weekly`**:
    - Spouští proces generování.
    - Lze jej spustit i manuálně pro okamžité vytvoření článku.
3.  **Scheduler (Plánovač)**:
    - V `routes/console.php` je nastaveno spouštění každé pondělí v 8:00.

## 3. Konfigurace
Systém využívá globální AI nastavení projektu (viz `AiSettingsService` a tabulka `ai_settings`).
- Musí být zapnuto `AI_ENABLED`.
- Musí být vyplněn `OPENAI_API_KEY`.
- Služba se pokouší zařadit článek do kategorie se slugem `aktuality`. Pokud neexistuje, použije výchozí kategorii (ID 1).

## 4. Způsob použití

### Manuální spuštění
```bash
php artisan app:news:generate-weekly
```

### Automatizace
Příkaz je automaticky registrován v Laravel Scheduleru:
```php
Schedule::call(fn() => Artisan::call('app:news:generate-weekly'))->weeklyOn(1, '08:00');
```

## 5. Formát výstupu
AI generuje JSON s následující strukturou:
- `title_cs` / `title_en`
- `excerpt_cs` / `excerpt_en`
- `content_cs` / `content_en`

Výsledný příspěvek je uložen jako model `Post` se statusem `published` a nastavenou viditelností.
