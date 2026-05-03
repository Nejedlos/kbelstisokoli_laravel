# Stav doručitelnosti e-mailů a DNS (Aktualizováno 3. 5. 2026)

Tento dokument shrnuje aktuální stav DNS záznamů a doručitelnosti po zásahu podpory Webglobe.

## 1. Aktuální stav (Verified 3. 5. 2026)

| Komponenta | Stav | Hodnota / Zjištění |
| :--- | :--- | :--- |
| **SPF** | ✅ OK | `v=spf1 a mx include:_spf.webglobe.cz -all` (Striktní politika nastavena) |
| **DMARC** | ✅ OK | `v=DMARC1; p=quarantine; rua=mailto:dmarc@kbelstisokoli.cz` (Aktivní karanténa a reporting) |
| **DKIM** | ✅ OK | `default._domainkey` je správně nastaven. |
| **MX** | ✅ OK | Ukazuje na standardní klastr Webglobe (`email.webglobe.cz` atd.) |
| **PTR (Reverzní DNS)** | ⚠️ Částečné | IP `62.109.154.92` (mail075) má PTR. IP `62.109.151.105` (mailproxy) **nemá PTR**. |

## 2. Odpověď pro podporu Webglobe

Podpora se dotazovala na způsob odesílání. Zde je přesná technická specifikace pro komunikaci s nimi:

---
**Předmět:** Re: Nastavení DNS a doručitelnost - kbelstisokoli.cz

Dobrý den,

děkuji za provedený reset a úpravu záznamů. Zkontroloval jsem aktuální stav a mám k němu doplňující informace a jednu prosbu:

1. **Způsob odesílání:** Potvrzuji, že e-maily odesíláme **výhradně** přes vaše SMTP servery. Aplikace (Laravel) je na produkci konfigurována na hostitele `mail.webglobe.cz`. Nevyužíváme žádné služby třetích stran (jako Mailchimp, SendGrid apod.).
2. **Diagnostika:** Hlavním důvodem penalizace na serverech `@centrum.cz` (a dříve i jinde) je chybějící PTR záznam u jednoho z odesílacích serverů.
    - IP adresa `62.109.151.105` (na kterou směřuje `mail.webglobe.cz`) aktuálně vrací `NXDOMAIN` (nemá PTR).
    - IPv6 adresa `2001:1ab0:7e1e:151:62:109:151:105` (pro stejný host) rovněž postrádá PTR záznam.
    - IP adresa `62.109.154.92` (která je v MX/A záznamech) PTR má v pořádku (`mail075.webglobe.com`).

**Prosba:** Mohli byste prosím nastavit/prověřit PTR záznamy pro IP `62.109.151.105` a IPv6 `2001:1ab0:7e1e:151:62:109:151:105` tak, aby ukazovaly na `mail.webglobe.cz` (nebo odpovídající validní hostname)?

Bez validního PTR u všech odesílacích IP adres bude doručitelnost na české freemaily (Centrum, Seznam) vždy problematická.

Děkuji,
[Vaše Jméno]
---

## 3. Technické detaily pro kontrolu (Interní)

Pokud chcete sami ověřit stav, můžete použít terminál:

- **Kontrola SPF/DMARC:** `dig kbelstisokoli.cz TXT +short` a `dig _dmarc.kbelstisokoli.cz TXT +short`
- **Kontrola PTR (problémová IP):** `host 62.109.151.105` (mělo by vrátit název, nyní vrací chybu)
- **Kontrola PTR (v pořádku IP):** `host 62.109.154.92` (vrací `mail075.webglobe.com`)

## 4. Další kroky
1. **Odeslat odpověď podpoře** (bod 2 tohoto dokumentu).
2. **Sledovat DMARC monitor** v administraci aplikace. Pokud jsou záznamy v pořádku doručovány, monitor v sekci "DMARC Monitor" začne zobrazovat statistiky úspěšnosti (SPF/DKIM alignment).
3. Jakmile bude doručitelnost stabilní, lze DMARC politiku změnit z `p=quarantine` na `p=reject`.
