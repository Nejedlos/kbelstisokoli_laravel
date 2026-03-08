# E-mailová bezpečnost: SPF a DMARC – Kbelští sokoli

Tento dokument obsahuje podklady pro nastavení DNS záznamů pro zajištění doručitelnosti e-mailů a ochranu proti spamu (spoofing).

## 1. SPF (Sender Policy Framework)
Záznam SPF informuje přijímající servery o tom, které IP adresy/servery smí odesílat poštu jménem domény `kbelstisokoli.cz`.

### Návrh TXT záznamu
V závislosti na tom, co projekt aktuálně používá (viz `.env` nebo `config/mail.php`):

**Varianta A: Webglobe Hosting (Výchozí)**
```text
v=spf1 include:_spf.webglobe.com ~all
```

**Varianta B: Google Workspace (Pokud jsou na Google)**
```text
v=spf1 include:_spf.google.com ~all
```

**Varianta C: Kombinace (Hosting + MailerSend / SendGrid)**
```text
v=spf1 include:_spf.webglobe.com include:mailersend.com ~all
```

*Doporučení:* Pokud nevíte, začněte se `~all` (soft fail), což nezahodí legitimní poštu při chybném nastavení.

## 2. DKIM (DomainKeys Identified Mail)
DKIM přidává do e-mailu digitální podpis.
- DKIM klíče generuje váš poskytovatel e-mailu (Webglobe, Google, MailerSend).
- Je nutné zkopírovat vygenerovaný veřejný klíč do DNS jako TXT záznam pro subdoménu typu `default._domainkey`.

## 3. DMARC (Domain-based Message Authentication, Reporting, and Conformance)
DMARC říká serverům, co mají dělat, pokud SPF nebo DKIM selžou.

### Fáze 1: Monitoring (Bezpečné p=none)
Tento záznam neovlivní doručitelnost, ale začne sbírat reporty o tom, kdo posílá poštu za vaši doménu.
```text
v=DMARC1; p=none; rua=mailto:admin@kbelstisokoli.cz
```

### Fáze 2: Karanténa (p=quarantine)
Po několika týdnech monitoringu a ověření všech odesílatelů.
```text
v=DMARC1; p=quarantine; pct=100; rua=mailto:admin@kbelstisokoli.cz
```

### Fáze 3: Striktní (p=reject)
Maximální ochrana domény.
```text
v=DMARC1; p=reject; rua=mailto:admin@kbelstisokoli.cz
```

## 4. Postup verifikace
1. Nastavte SPF a DKIM v DNS.
2. Počkejte 24h na propagaci.
3. Použijte nástroje jako [mail-tester.com](https://www.mail-tester.com/) nebo [mxtoolbox.com](https://mxtoolbox.com/dmarc.aspx) k ověření validity.
4. Sledujte RUA reporty pro odhalení falešných odesílatelů.
