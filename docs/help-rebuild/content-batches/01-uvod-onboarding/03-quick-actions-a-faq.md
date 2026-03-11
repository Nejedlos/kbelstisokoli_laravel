# Návrh Quick Actions a FAQ: Batch 01 – Úvod a Onboarding

Tento dokument definuje konkrétní interaktivní prvky a odpovědi na časté dotazy pro články nápovědy v první vlně obsahu. Všechny návrhy vycházejí z [UI auditu](01-ui-audit.md) a reálné implementace systému.

---

## 1. Vstup do kabiny (Přihlášení)
*Slug: `vstup-do-kabiny`*

### Quick Actions
| Label | Target Route / URL | Audience | Účel | Proč je to v Quick Actions? |
| :--- | :--- | :--- | :--- | :--- |
| **Přejít k přihlášení** | `/admin/login` | Všichni | Otevře přihlašovací formulář. | Základní akce pro každého uživatele. |
| **Nefunguje mi heslo** | `/admin/password-reset/request` | Všichni | Rychlá cesta k obnově přístupu. | Nejčastější problém při pokusu o přihlášení. |

### FAQ
- **Otázka:** Kde získám své přihlašovací údaje?
  - **Odpověď:** Přístup do systému vám vytvoří administrátor klubu (zpravidla po schválení přihlášky). Údaje obdržíte v uvítacím e-mailu.
  - **Potvrzeno:** Ano, registrace ve Fortify je vypnuta, uživatele zakládá admin.
  - **Umístění:** Detail článku i FAQ sekce.
- **Otázka:** Mohu se přihlásit přes Google nebo Facebook?
  - **Odpověď:** Aktuálně systém podporuje pouze přihlášení pomocí e-mailu a hesla pro maximální bezpečnost vašich dat.
  - **Potvrzeno:** Ano, v kódu není implementován Socialite.
  - **Umístění:** FAQ sekce.

---

## 2. Zapomenuté heslo a obnova přístupu
*Slug: `zapomenute-heslo`*

### Quick Actions
| Label | Target Route / URL | Audience | Účel | Proč je to v Quick Actions? |
| :--- | :--- | :--- | :--- | :--- |
| **Poslat žádost o nové heslo** | `/admin/password-reset/request` | Všichni | Spustí proces obnovy hesla. | Hlavní cíl uživatele na této stránce. |

### FAQ
- **Otázka:** Jak dlouho platí odkaz pro obnovu hesla v e-mailu?
  - **Odpověď:** Odkaz je z bezpečnostních důvodů platný 60 minut. Pokud vyprší, musíte o nové heslo požádat znovu.
  - **Potvrzeno:** Ano, standardní nastavení Laravelu (`config/auth.php`).
  - **Umístění:** Detail článku.
- **Otázka:** E-mail pro obnovu hesla mi nedorazil. Co teď?
  - **Odpověď:** Zkontrolujte složku Spam nebo Hromadné zprávy. Pokud e-mail nedorazí do 5 minut, ověřte u trenéra, zda máte v systému správnou e-mailovou adresu.
  - **Potvrzeno:** Logika doručování mailů.
  - **Umístění:** Troubleshooting sekce v článku.

---

## 3. Můj profil a členské údaje
*Slug: `muj-profil`*

### Quick Actions
| Label | Target Route / URL | Audience | Účel | Proč je to v Quick Actions? |
| :--- | :--- | :--- | :--- | :--- |
| **Upravit osobní údaje** | `/member/profile` | Všichni | Přejde přímo do editace profilu. | Nejčastější důvod návštěvy sekce profilu. |
| **Změnit profilovou fotku** | `/member/profile` | Všichni | Otevře rozhraní pro nahrání fotky. | Uživatelé chtějí mít v systému svou aktuální tvář. |

### FAQ
- **Otázka:** Proč nemohu změnit své jméno nebo rodné číslo?
  - **Odpověď:** Tyto údaje jsou z důvodu integrity matriky klubu uzamčeny. Pokud v nich máte chybu, kontaktujte administrátora klubu.
  - **Potvrzeno:** Ano, pole jsou v profilu člena pouze pro čtení (readonly).
  - **Umístění:** Detail článku.
- **Otázka:** Kdo všechno vidí moje Bio a telefonní číslo?
  - **Odpověď:** Vaše údaje vidí trenéři vašeho týmu a administrátoři klubu pro potřeby organizace. Ostatní hráči vidí pouze vaše jméno a číslo dresu.
  - **Potvrzeno:** Logika oprávnění v systému.
  - **Umístění:** Detail článku.

---

## 4. Neprůstřelná obrana (Zabezpečení)
*Slug: `zabezpeceni-uctu`*

### Quick Actions
| Label | Target Route / URL | Audience | Účel | Proč je to v Quick Actions? |
| :--- | :--- | :--- | :--- | :--- |
| **Aktivovat 2FA ochranu** | `/member/2fa/setup` | Všichni | Spustí průvodce nastavením dvoufázového ověření. | Klíčový bezpečnostní prvek. |
| **Zobrazit záchranné kódy** | `/member/2fa/setup` | Všichni | Zobrazí kódy pro případ ztráty telefonu. | Kritické pro obnovu přístupu bez admina. |

### FAQ
- **Otázka:** Je dvoufázové ověření (2FA) povinné?
  - **Odpověď:** Pro trenéry a administrátory je 2FA povinné z důvodu ochrany klubových dat. Pro hráče a rodiče je vysoce doporučené.
  - **Potvrzeno:** Ano, middleware vynucuje 2FA pro admin role.
  - **Umístění:** Detail článku.
- **Otázka:** Co se stane, když ztratím telefon s aplikací Google Authenticator?
  - **Odpověď:** Můžete použít jeden z osmi záchranných kódů, které jste si vygenerovali při nastavení. Pokud nemáte ani ty, musí váš 2FA resetovat administrátor klubu.
  - **Potvrzeno:** Logika Fortify 2FA.
  - **Umístění:** Detail článku.

---

## 5. Kbelští sokoli v mobilu (PWA)
*Slug: `mobilni-aplikace`*

### Quick Actions
| Label | Target Route / URL | Audience | Účel | Proč je to v Quick Actions? |
| :--- | :--- | :--- | :--- | :--- |
| **Zpět na Dashboard** | `/admin` | Všichni | Návrat na hlavní plochu pro test instalace. | Uživatel chce aplikaci přidat z hlavní stránky. |

### FAQ
- **Otázka:** Najdu aplikaci v App Store nebo Google Play?
  - **Odpověď:** Ne, využíváme moderní technologii PWA. Aplikaci si přidáte na plochu přímo z internetového prohlížeče v mobilu.
  - **Potvrzeno:** Ano, v projektu je `manifest.json`.
  - **Umístění:** Detail článku.
- **Otázka:** Funguje aplikace i bez internetu?
  - **Odpověď:** Částečně ano – můžete si prohlížet dříve načtená data, ale pro omlouvání z tréninků nebo zápis docházky je nutné připojení.
  - **Potvrzeno:** Service worker v PWA.
  - **Umístění:** Detail článku.

---

## 6. Role a oprávnění v systému
*Slug: `role-v-systemu`*

### Quick Actions
*U tohoto článku nejsou přímé akce relevantní, slouží k vysvětlení hierarchie.*

### FAQ
- **Otázka:** Mám v klubu více dětí, uvidím je všechny pod jedním účtem?
  - **Odpověď:** Ano, role "Rodič" vám umožňuje spravovat profily všech vašich dětí registrovaných v klubu pod jedním přihlášením.
  - **Potvrzeno:** Ano, relace `User::children()` v DB.
  - **Umístění:** Detail článku.
- **Otázka:** Proč jako hráč nemohu upravovat docházku u zápasů?
  - **Odpověď:** Úpravu docházky a nominace mají v kompetenci pouze trenéři a administrátoři. Hráč se může z akce pouze omluvit s uvedením důvodu.
  - **Potvrzeno:** Oprávnění v modulu Sport.
  - **Umístění:** Detail článku.

---

## 7. Často kladené dotazy (FAQ)
*Slug: `faq-novy-clen`*

### Quick Actions
*Agregační článek, akce jsou v jednotlivých sekcích.*

### FAQ
- **Otázka:** Co mám dělat, když vidím chybu "Špatná nahrávka!" při přihlášení?
  - **Odpověď:** Znamená to, že jste zadali nesprávné heslo nebo e-mail. Zkuste to znovu, nebo využijte funkci "Zapomenuté heslo".
  - **Potvrzeno:** Lokalizační řetězec v `lang/cs.json`.
  - **Umístění:** FAQ článek.
- **Otázka:** Jak si nastavím, který tým chci vidět jako první?
  - **Odpověď:** V "Mém profilu" si můžete v sekci "Nastavení zobrazení" vybrat svůj výchozí tým. Systém pak bude automaticky filtrovat data pro tento tým.
  - **Potvrzeno:** Pole `member_default_team_id` v profilu.
  - **Umístění:** FAQ článek.
