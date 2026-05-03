# DMARC Monitor - Pokročilá Analýza

Tento dokument popisuje rozšířený systém pro monitorování a analýzu DMARC reportů.

## 1. Jak systém funguje

Systém automaticky stahuje DMARC aggregate reporty z nakonfigurovaných IMAP schránek (přes `DmarcIngestCommand`). Po stažení probíhá několik fází zpracování:

1.  **Parsování:** XML report je převeden na strukturovaná data.
2.  **IP Enrichment:** Pro každou zdrojovou IP adresu se zjišťuje reverzní DNS záznam.
3.  **Identifikace odesílatele:** Systém porovnává IP a domény se seznamem **Legitimních odesílatelů**.
4.  **Analýza:** Vyhodnocuje se shoda (alignment) SPF a DKIM, určuje se typ události, závažnost (severity) a rizikové skóre.
5.  **Doporučení:** Na základě výsledků se generují konkrétní technické i netechnické kroky pro administrátora.
6.  **Alerty:** Pokud je zjištěna kritická událost, je odeslána notifikace technickému kontaktu.

## 2. Legitimní odesílatelé

V administraci (sekce DMARC Monitor -> Legitimní odesílatelé) můžete definovat služby, které mají oprávnění odesílat e-maily za vaši doménu.

Při přidávání odesílatele definujte:
- **Povolené IP/CIDR:** Konkrétní adresy serverů.
- **Povolené SPF domény:** Např. `_spf.google.com`.
- **Povolené DKIM domény/selektory.**

Pokud systém identifikuje odesílatele jako legitimního, ale autentizace selže, sníží se rizikové skóre (jde o chybu konfigurace, nikoliv o útok), ale severity zůstane na úrovni varování.

## 3. Alerty a notifikace

Alerty jsou řízeny konfigurací v `.env` a `config/dmarc.php`.

### Konfigurace v .env:
```env
TECHNICAL_CONTACT_EMAIL=admin@kbelstisokoli.cz
DMARC_ALERTS_ENABLED=true
DMARC_ALERTS_CRITICAL_ONLY=true
DMARC_ALERTS_MIN_SEVERITY=critical
DMARC_ALERTS_RATE_LIMIT_HOURS=12
```

Systém používá deduplikaci (fingerprint), takže pro stejnou chybu z jedné IP nebudete dostávat e-mail častěji než jednou za 12 hodin.

## 4. Interpretace výsledků

- **Severity Critical (Červená):** E-mail neprošel SPF ani DKIM a odesílatel je neznámý. Vysoké riziko spoofingu.
- **Severity High (Oranžová):** Podobné jako critical, ale s nižším počtem zpráv nebo u méně podezřelé infrastruktury.
- **Severity Medium (Žlutá):** Problém u známého odesílatele (špatná konfigurace).
- **Severity Low/Info (Modrá/Zelená):** DMARC pass, nebo jen drobná upozornění na alignment.

## 5. Příkazy (CLI)

### Ruční analýza historických dat
Pokud přidáte nového legitimního odesílatele a chcete přepočítat starší záznamy:
```bash
php artisan dmarc:reanalyze --domain=kbelstisokoli.cz
```
Možné parametry:
- `--domain`: Filtrovat podle domény.
- `--from` / `--to`: Časové rozmezí.
- `--send-alerts`: Pokud chcete znovu odeslat alerty (nedoporučeno pro velká data).

## 6. Postup pro zpřísnění DMARC politiky

1.  **Monitoring (p=none):** Sledujte reporty alespoň 14-30 dní.
2.  **Identifikace:** Označte všechny své služby jako legitimní odesílatele.
3.  **Oprava:** U legitimních služeb zajistěte, aby SPF i DKIM procházely (PASS a Aligned).
4.  **Kontrola readiness:** V dashboardu sledujte "Readiness Score". Pokud je u domény 100%, je bezpečné postoupit.
5.  **Zpřísnění (p=quarantine):** Změňte DNS záznam na `p=quarantine; pct=25`.
6.  **Finální stav (p=reject):** Po další době stability zvyšte `pct=100` nebo přejděte na `p=reject`.
