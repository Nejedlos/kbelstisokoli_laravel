# DMARC Monitor

Tento modul slouží k automatickému sledování a vyhodnocování DMARC (Domain-based Message Authentication, Reporting, and Conformance) reportů pro doménu `kbelstisokoli.cz`. Pomáhá identifikovat legitimní odesílatele a detekovat pokusy o podvržení e-mailů nebo chyby v konfiguraci SPF/DKIM.

## Jak to funguje

1.  **Sběr reportů:** Systém se pravidelně připojuje k dedikovanému mailboxu (`dmarc@kbelstisokoli.cz`) přes IMAP.
2.  **Zpracování:** Stáhne e-maily, dekomprimuje přílohy (ZIP/GZIP), vyparsuje XML reporty a uloží je do databáze.
3.  **Analýza:** Každý záznam v reportu je automaticky klasifikován (OK, Varování, Kritické) na základě zarovnání (alignment) SPF a DKIM.
4.  **Incidenty:** Při zjištění kritického selhání (např. odesílání z neautorizované IP adresy) je vytvořen **Incident** a odeslána notifikace na technický kontakt (`ERROR_REPORT_EMAIL`).

## Administrace

### 1. DMARC Mailboxy
Zde se konfiguruje přístup k e-mailové schránce, kam chodí reporty.
- **Doporučené nastavení:** IMAP, Host: `mail.webglobe.cz`, Port: `993`, Šifrování: `SSL`.
- **Akce:** Tlačítko "Importovat reporty" spustí okamžitý sběr dat.

### 2. DMARC Reporty
Seznam všech přijatých reportů od různých organizací (Google, Microsoft, Seznam atd.).
- **Detail reportu:** Zobrazuje "lidsky čitelnou" tabulku všech odesílajících IP adres, jejich status a **doporučenou akci** pro nápravu.
- **Ke stažení:** Možnost stáhnout původní XML report nebo lidsky čitelný textový souhrn.

### 3. DMARC Incidenty
Sledování otevřených problémů. Incident seskupuje opakovaná selhání ze stejné IP adresy.
- **Stavy:** Otevřeno, V řešení, Vyřešeno.
- **Notifikace:** Systém hlídá, aby neposílal příliš mnoho notifikací pro stejný incident (limit 1x za 12 hodin).

## Technické informace

- **Artisan příkaz:** `php artisan dmarc:ingest` (doporučeno spouštět cronem 1x denně).
- **Úložiště:** XML soubory jsou ukládány do `storage/app/dmarc/reports/`.
- **Zabezpečení:** Hesla k mailboxům jsou v databázi uložena v šifrované podobě.

## Řešení problémů (DMARC Faily)

- **FAIL u SPF:** IP adresa odesílatele není v SPF záznamu domény.
- **FAIL u DKIM:** E-mail není podepsán nebo podpis neodpovídá doméně v `From` hlavičce.
- **Alignment (Zarovnání):** I když SPF/DKIM projdou, mohou selhat na zarovnání, pokud odesílatel používá jinou doménu pro podpis/obálku než pro hlavičku `From`.
