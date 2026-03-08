# Rozšířený branding týmu Kbelští sokoli C & E

Tento modul zajišťuje elegantní integraci týmového loga a loga hlavního oddílu TJ Sokol Kbely Basketball do celého webu.

## 1. Správa a nastavení v administraci

Všechna nastavení týmového loga najdete v sekci **Branding**.

### Možnosti nastavení:
- **Zobrazení:** Jednotlivé sekce (Hlavička, Hero, Karty týmů, Nábor, Patička, Zápasy, Page Headery) lze nezávisle zapínat/vypínat.
- **Velikosti:** Pro každé umístění lze definovat specifickou výšku v pixelech (např. 40px pro desktopovou hlavičku).
- **Hero sekce:** Podporuje zobrazení loga jako badge u nadpisu nebo jako jemný watermark (průhledné pozadí) přes celou sekci.
- **Vizuální styl:** Možnost nastavit zaoblení (border-radius) a stín (drop-shadow) u loga.

## 2. Logika přiřazování log (TeamBrandingResolver)

Projekt využívá centralizovanou službu `App\Services\TeamBrandingResolver`, která automaticky určuje, které logo se má v daném kontextu zobrazit.

### Pravidla:
1. **Interní týmy (C & E):** Pokud je týmu (podle slugu) rozpoznán jako náš interní tým (obsahuje `-c`, `-e`, nebo je to přímo `c` / `e`), zobrazuje se **týmové logo Kbelští sokoli**.
2. **Ostatní týmy / Hlavní oddíl:** U ostatních týmů nebo v kontextu celého oddílu se zobrazuje logo **TJ Sokol Kbely Basketball**.
3. **Zápasy:** Logo se u zápasu zobrazuje pouze u našeho týmu (domácí/hosté podle rozpisu).

## 3. Technické detaily

### Podpora formátů
- Primárně se používá formát **WebP** pro maximální výkon a kvalitu.
- Automatický fallback na **PNG** pro starší prohlížeče.
- Loga jsou uložena v `public/assets/img/loga/`.

### Výchozí cesty (v config/branding.php):
- Týmové logo (mini): `logo_kbelsti_sokoli_mini.webp` / `.png`
- Týmové logo (velké): `logo_kbelsti_sokoli_velke.webp` / `.png`
- Parent logo: `tj_sokol_kbely_basketball_logo_mini.webp` / `.png`

## 4. Nasazení

Pro nastavení výchozích hodnot na produkci stačí spustit:
```bash
php artisan db:seed --class=BrandingSeeder
```
Seeder je idempotentní, takže jej lze spouštět opakovaně bez rizika smazání vlastních úprav v DB.
