# Normalizace a deduplikace sezón

Tento dokument popisuje proces čištění a sjednocování dat v tabulce `seasons`.

## Problém
V průběhu vývoje a importů dat (např. z Legacy DB nebo při ručním zadávání) docházelo k nekonzistentnímu pojmenovávání sezón. 
Příklady nekonzistencí:
- Různé oddělovače: `2024/2025`, `2024 - 2025`, `2024-2025`
- Zkrácené formáty: `2024-25`, `2024/25`
- Nechtěné mezery: ` 2024/2025 `
- Samostatné roky: `2024` (což v basketbalu znamená sezónu 2024/2025)

Tyto nekonzistence vedly k duplicitním záznamům v tabulce `seasons`, což komplikovalo statistiky a automatické discovery externích dat.

## Řešení

### 1. Normalizační logika
V modelu `App\Models\Season` byla implementována metoda `normalizeName(string $name)`, která převádí různé vstupy na jednotný formát `YYYY/YYYY`.

Příklady transformace:
- ` 2024 - 2025 ` -> `2024/2025`
- `2024-25` -> `2024/2025`
- `2024` -> `2024/2025`

### 2. Artisan Command
Byl vytvořen příkaz `php artisan app:seasons-normalize`, který:
1. Projde všechny sezóny v databázi.
2. Navrhne normalizovaný název.
3. Pokud normalizovaný název odpovídá jiné existující sezóně, provede **sloučení (merge)**.

**Proces sloučení:**
- Identifikuje všechny záznamy v závislých tabulkách (zápasy, statistiky, konfigurace), které se odkazují na "špatnou" (duplicitní) sezónu.
- Aktualizuje tyto cizí klíče (`season_id`) na ID "správné" (cílové) sezóny.
- Odstraní duplicitní záznam z tabulky `seasons`.

### 3. Discovery Service
Služba `SeasonDiscoveryService` nyní interně využívá normalizaci názvů, aby spolehlivěji určila parametr `y` pro externí importy z cz.basketball (např. i když je sezóna v DB pojmenována nestandardně).

## Použití
Příkaz pro normalizaci je bezpečný a lze jej spustit v dry-run režimu:
```bash
php artisan app:seasons-normalize --dry-run
```

Pro ostré provedení změn:
```bash
php artisan app:seasons-normalize
```
