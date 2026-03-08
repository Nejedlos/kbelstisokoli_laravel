# Návrh Hero obrázků pro frontend

Tento dokument definuje seznam obrázků pro záhlaví (Hero sekce) jednotlivých stránek webu. Všechny obrázky by měly být dostupné ve formátu **WebP** s fallbackem na **JPG**.

| Stránka | Routa | Anglický vyhledávací dotaz (pro stock fotky) | Název souboru (webp/jpg) |
| :--- | :--- | :--- | :--- |
| **Novinky** | `public.news.index` | basketball player taking a break, sports news background | `hero-news.webp` |
| **Zápasy** | `public.matches.index` | professional basketball arena interior, match day atmosphere | `hero-matches.webp` |
| **Týmy** | `public.teams.index` | basketball team huddle, group spirit, sports team photo | `hero-teams.webp` |
| **Tréninky** | `public.trainings.index` | basketball training session, ball on court, drills session | `hero-trainings.webp` |
| **Galerie** | `public.galleries.index` | sports photography action, camera on court sideline | `hero-gallery.webp` |
| **Historie** | `public.history.index` | vintage basketball, old retro sports heritage, nostalgia | `hero-history.webp` |
| **Kontakt** | `public.contact.index` | basketball stadium entrance, sports office, communication | `hero-contact.webp` |
| **Vyhledávání** | `public.search` | sports data search, professional information hub, basketball info | `hero-search.webp` |
| **Detail zápasu** | `public.matches.show` | basketball match action close-up, arena scoreboard | `hero-match-detail.webp` |

## Implementace v kódu
Všechny tyto stránky nyní využívají komponentu `<x-page-header>` s atributem `image="assets/img/hero/[nazev-souboru].webp"`.

## Technické požadavky
- **Formát:** WebP (hlavní), JPG (fallback v `<picture>` tagu).
- **Rozlišení:** Minimálně 1920x600 px (optimální poměr stran pro široké hero).
- **Umístění:** `public/assets/img/hero/`.
