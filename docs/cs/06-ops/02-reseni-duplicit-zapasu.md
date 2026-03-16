# Řešení duplicit zápasů

Tento dokument popisuje mechanismy prevence a opravy duplicitních zápasů, které mohou vzniknout při importu dat z externích zdrojů (např. cz.basketball).

## 1. Příčina duplicit
Duplicity obvykle vznikají, když stejný zápas v importu změní některý z klíčových identifikátorů:
- **Název soupeře:** Např. "CTS Praha" vs "CTS Basket". Pokud nejsou tito soupeři v systému sloučeni, vygeneruje se pro ně jiný `MatchIdentityKey`.
- **Datum/Čas:** Pokud se čas zápasu posune o více než 120 minut a zápas nemá v metadatech `external_id`.
- **Chybějící metadata:** Pokud zápas v DB nemá `match_identity_key` ani `external_id` (např. ručně vytvořený zápas).

## 2. Prevence
Do systému byly implementovány následující ochranné prvky:
- **Normalizace identity klíče:** `MatchSyncService` nyní při generování `MatchIdentityKey` používá normalizovaný název ze synchronizovaného modelu `Opponent` místo surového názvu z importu. Pokud jsou dva názvy soupeře sloučeny pod jeden model, identifikační klíč zápasu bude stejný.
- **Detekce podle času:** Pokud nebyl zápas nalezen podle klíčů, systém hledá jakýkoliv zápas stejného týmu v okně +/- 120 minut ve stejný den.

## 3. Automatický úklid
Byl vytvořen Artisan příkaz pro hromadné čištění duplicit, který běží automaticky každý den ve 4:00.

```bash
php artisan stats:cleanup-duplicates
```

Příkaz vyhledá zápasy stejného týmu ve stejný den a čas (tolerance 120 min), sloučí jejich docházku, statistiky a metadata a nadbytečné záznamy odstraní.

## 4. Ruční oprava (Specifické IDs)
Pokud automatický úklid selže nebo je potřeba zasáhnout okamžitě u konkrétních ID, lze použít skript `fix_match_duplicity.php`.

**Příklad použití (pro IDs 1299 a 1847):**
1. Nahrajte skript na server (již je součástí projektu).
2. Spusťte přes SSH:
```bash
php fix_match_duplicity.php
```

Skript provede:
1. Přesun docházky (attendances) ze zdrojového ID (1299) do cílového (1847).
2. Sloučení metadat (zachování `legacy_z_id`).
3. Smazání nadbytečného zápasu (1299).
