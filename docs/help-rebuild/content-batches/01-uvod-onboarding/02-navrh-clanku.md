# Návrh článků: Batch 01 – Úvod a Onboarding

Tento dokument definuje seznam a strukturu help článků pro první vlnu obsahu. Návrh vychází z [UI auditu reality](01-ui-audit.md).

## Přehled navržených článků

| Článek | Slug | Typ | Role | Priorita |
| :--- | :--- | :--- | :--- | :--- |
| **Vstup do kabiny (Přihlášení)** | `vstup-do-kabiny` | Onboarding | Všichni | P1 |
| **Zapomenuté heslo a obnova přístupu** | `zapomenute-heslo` | Troubleshooting | Všichni | P1 |
| **Můj profil a členské údaje** | `muj-profil` | Detailní | Všichni | P2 |
| **Neprůstřelná obrana (Zabezpečení)** | `zabezpeceni-uctu` | Detailní | Všichni | P2 |
| **Kbelští sokoli v mobilu (PWA)** | `mobilni-aplikace` | Detailní | Všichni | P3 |
| **Role a oprávnění v systému** | `role-v-systemu` | Detailní | Všichni | P3 |
| **Často kladené dotazy (FAQ)** | `faq-novy-clen` | FAQ | Všichni | P2 |

---

### 1. Vstup do kabiny (Přihlášení)
- **Slug**: `vstup-do-kabiny`
- **Proč existuje**: První kontakt se systémem, vysvětlení basketbalové terminologie na přihlašovací stránce.
- **Reálné UI**: Stránka `/admin/login` (Auth UI).
- **Quick Actions**: 
    - [Otevřít přihlášení](/admin/login)
- **Related Sections**: `zapomenute-heslo`, `role-v-systemu`.
- **Pravděpodobné FAQ**:
    - "Kde získám přihlašovací údaje?" (Odpověď: Zasílá administrátor klubu).
    - "Nedaří se mi přihlásit, co mám dělat?" (Odkaz na troubleshooting).
- **Typ**: Onboarding článek.

### 2. Zapomenuté heslo a obnova přístupu
- **Slug**: `zapomenute-heslo`
- **Proč existuje**: Častý technický dotaz při ztrátě přístupu.
- **Reálné UI**: `/admin/forgot-password`, `/admin/reset-password`.
- **Quick Actions**:
    - [Obnovit heslo](/admin/forgot-password)
- **Related Sections**: `vstup-do-kabiny`, `zabezpeceni-uctu`.
- **Pravděpodobné FAQ**:
    - "Nepřišel mi e-mail pro obnovu hesla." (Kontrola spamu, správnost e-mailu).
    - "Odkaz v e-mailu již nefunguje." (Expirace tokenu).
- **Typ**: Troubleshooting článek.

### 3. Můj profil a členské údaje
- **Slug**: `muj-profil`
- **Proč existuje**: Klíčová sekce pro udržování aktuálních kontaktů (GDPR, komunikace trenérů).
- **Reálné UI**: Členská sekce -> Můj profil (`/member/profile`).
- **Quick Actions**:
    - [Upravit můj profil](/member/profile)
- **Related Sections**: `zabezpeceni-uctu`, `mobilni-aplikace`.
- **Pravděpodobné FAQ**:
    - "Jak změním profilovou fotku?" (Nahrání přes Cropper).
    - "Proč nemohu změnit své jméno nebo rodné číslo?" (Pole uzamčená pro administraci).
- **Typ**: Detailní sekční článek.

### 4. Neprůstřelná obrana (Zabezpečení a 2FA)
- **Slug**: `zabezpeceni-uctu`
- **Proč existuje**: Ochrana osobních údajů v klubu, vysvětlení dvoufázového ověření.
- **Reálné UI**: Nastavení profilu -> Zabezpečení (`/member/2fa/setup`).
- **Quick Actions**:
    - [Nastavit 2FA zabezpečení](/member/2fa/setup)
- **Related Sections**: `muj-profil`, `zapomenute-heslo`.
- **Pravděpodobné FAQ**:
    - "Co je to dvoufázové ověření (2FA)?"
    - "Ztratil jsem přístup k aplikaci pro 2FA, jak se přihlásím?" (Obnova přes admina).
- **Typ**: Detailní sekční článek.

### 5. Kbelští sokoli v mobilu (PWA)
- **Slug**: `mobilni-aplikace`
- **Proč existuje**: Usnadnění přístupu k docházce a zápasům přímo z plochy telefonu.
- **Reálné UI**: Prohlížeč (instalační výzva), `site.webmanifest`.
- **Quick Actions**: Žádné (instruktážní).
- **Related Sections**: `muj-profil`, `vstup-do-kabiny`.
- **Pravděpodobné FAQ**:
    - "Musím si stahovat aplikaci z App Store / Google Play?" (Ne, je to PWA).
    - "Jak si přidám ikonu na plochu v iPhonu?" (Menu Sdílet -> Přidat na plochu).
- **Typ**: Detailní sekční článek.

### 6. Role a oprávnění v systému
- **Slug**: `role-v-systemu`
- **Proč existuje**: Vysvětlení rozdílů mezi tím, co vidí hráč, trenér a administrátor.
- **Reálné UI**: Celý systém (podmíněné zobrazení menu a akcí).
- **Quick Actions**: Žádné.
- **Related Sections**: `vstup-do-kabiny`.
- **Pravděpodobné FAQ**:
    - "Proč nevidím finance ostatních hráčů?" (Omezení role).
    - "Může trenér měnit mé osobní údaje?" (Vysvětlení kompetencí).
- **Typ**: Detailní sekční článek.

### 7. Často kladené dotazy (FAQ)
- **Slug**: `faq-novy-clen`
- **Proč existuje**: Agregace drobných dotazů, které se nehodí do samostatného článku.
- **Reálné UI**: Různé.
- **Quick Actions**: Žádné.
- **Related Sections**: Všechny v Batch 01.
- **Pravděpodobné FAQ**:
    - "Kdo je můj trenér?"
    - "Kdy probíhají tréninky?"
    - "Kde najdu soupisku svého týmu?"
- **Typ**: FAQ článek.

## Poznámky k implementaci
- **Terminologie**: Články budou využívat klubovou hantýrku (Vstup do kabiny, Neprůstřelná obrana, Lavička atd.) pro zvýšení autenticity.
- **Lokalizace**: Všechny navržené články budou připraveny v `cs` i `en` verzi.
- **Ověření**: Před psaním finálního obsahu bude u každého článku proveden kontrolní checklist dle `docs/help-rebuild/27-checklist-analyzy-sekce.md`.
