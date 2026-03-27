# Bezpečnostní regresní plán - Kbelští sokoli

Tento dokument definuje strategii pro udržování vysoké úrovně bezpečnosti projektu a prevenci regresí (znovuzavedení již opravených chyb).

## 1. Automatizované bezpečnostní testy

V rámci auditu byla vytvořena sada automatizovaných testů v adresáři `tests/Feature/Security/`. Tyto testy musí být součástí CI/CD pipeline a musí být spouštěny před každým nasazením.

### Seznam testovacích tříd:
- **AdminAccessTest.php:** Ověřuje, že přístup do administrace je striktně omezen na aktivní uživatele s odpovídajícími rolemi (Admin, Editor, Coach). Testuje také bypass 2FA (např. přes screenshot mode).
- **CsrfProtectionTest.php:** Kontroluje, že všechny state-changing operace (POST, PUT, DELETE) na kritických endpointech vyžadují CSRF token.
- **IdorProtectionTest.php:** Testuje ochranu proti neoprávněnému přístupu k cizím datům (IDOR), zejména u stahování médií a avatarů.
- **SecurityHeadersTest.php:** Verifikuje přítomnost a správné nastavení HTTP bezpečnostních hlaviček (`X-Frame-Options`, `X-Content-Type-Options`, atd.) a příznaky session cookies (`Secure`, `HttpOnly`, `SameSite`).
- **UploadSecurityTest.php:** Kontroluje validaci nahrávaných souborů (MIME typy, přípony) a autorizaci u nahrávání avatarů.
- **XssSanitizationTest.php:** Testuje účinnost sanitizace HTML v systému zpětné vazby a dalších částech aplikace, kde se zobrazuje uživatelský obsah.

### Spouštění testů:
```bash
php artisan test tests/Feature/Security/
```

## 2. Pravidelná údržba a aktualizace

- **Aktualizace závislostí:** Minimálně jednou měsíčně provádět `composer update` pro získání bezpečnostních záplat.
- **Audit logy:** Pravidelně kontrolovat auditní logy v administraci (AuditLogs) pro detekci podezřelé aktivity.
- **Nové funkce:** Při implementaci každé nové funkce, která přijímá uživatelský vstup nebo pracuje se soubory, musí být doplněn odpovídající test do `tests/Feature/Security/`.

## 3. Kritické oblasti pro monitoring (Watchlist)

Při vývoji věnujte zvýšenou pozornost těmto komponentám:
1. **Feedback System:** Zpracovává screenshoty a DOM snapshoty (riziko XSS a SSRF).
2. **Screenshot Pipeline:** Mechanismus pro dočasnou impersonifikaci (riziko zneužití pro neoprávněný přístup).
3. **Media Library:** Veřejné vs. soukromé disky a jejich autorizace v `MediaDownloadController`.
4. **Livewire Komponenty s uploady:** Vždy vyžadují explicitní validaci MIME typů a velikosti.

## 4. Postup při zjištění zranitelnosti

1. **Reprodukce:** Vytvořit nový test v `tests/Feature/Security/`, který zranitelnost prokazuje (failuje).
2. **Oprava:** Implementovat nápravu v kódu.
3. **Verifikace:** Ověřit, že test nyní prochází a ostatní bezpečnostní testy nebyly ovlivněny.
4. **Dokumentace:** Zaznamenat nález a opravu do interní bezpečnostní dokumentace.

---
*Vytvořeno v březnu 2026 jako součást bezpečnostního auditu.*
