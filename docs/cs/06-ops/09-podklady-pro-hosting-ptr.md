# Podklady pro hosting - Diagnostika PTR záznamů

Tento dokument slouží jako podklad pro komunikaci s podporou Webglobe ohledně problémů s doručitelností e-mailů.

## 1. Metodika diagnostiky (Jak jsme na to přišli)

K identifikaci chybějícího PTR záznamu byly použity standardní síťové nástroje `host` a `dig` spouštěné přímo v prostředí projektu.

### Krok 0: Přístup k prostředí a výchozí stav
Diagnostika byla prováděna v rámci vývojového a operačního cyklu projektu. K projektu přistupujeme prostřednictvím **vzdáleného přístupu přes SSH (Secure Shell)** přímo na produkční server hostingu Webglobe.

**Detaily spojení:**
- **Server:** `ksokoli.cz` (alias pro produkční IP serveru)
- **Způsob připojení:** Standardní SSH klient
- **Příkaz pro připojení:** `ssh [uživatel]@ksokoli.cz` (uživatelské jméno je specifické pro náš hostingový účet)
- **Pracovní adresář na serveru:** `/www/kbelstisokoli` (kořenový adresář aplikace)

Tento způsob přístupu přes SSH konzoli nám umožňuje:
1. Pracovat přímo v prostředí, kde je aplikace nasazena (cesta `/www/kbelstisokoli`).
2. Spouštět síťové diagnostické příkazy z totožné síťové infrastruktury, kterou využívá samotná aplikace pro odesílání e-mailů.
3. Simulovat síťové požadavky přesně tak, jak je vidí operační systém serveru.

**Konkrétní sekvence provedených příkazů:**
Přímo v terminálu po přihlášení a vstupu do adresáře projektu byly spuštěny tyto příkazy:
```bash
# 1. Ověření aktuální cesty
pwd
# Výstup: /www/kbelstisokoli

# 2. Zjištění IP adresy nastaveného SMTP hostitele
host mail.webglobe.cz

# 3. Diagnostika chybějícího PTR záznamu (hlavní důkaz)
host 62.109.151.105
host 2001:1ab0:7e1e:151:62:109:151:105

# 4. Srovnávací test s funkčním serverem (pro potvrzení metodiky)
host 62.109.154.92
```

Při testování odesílání e-mailů z aplikace jsme narazili na chyby doručení (zejména u Centrum.cz). Proto jsme v této SSH konzoli přistoupili k prověření DNS konfigurace SMTP serverů, které máme nastaveny v `.env` souboru (proměnná `MAIL_HOST=mail.webglobe.cz`).

### Krok A: Identifikace SMTP hostitele
Aplikace Laravel je konfigurována na odesílání přes: `mail.webglobe.cz`.
Příkazem `host mail.webglobe.cz` jsme zjistili, že jde o alias pro `mailproxy.webglobe.cz`, který směřuje na:
- IPv4: `62.109.151.105`
- IPv6: `2001:1ab0:7e1e:151:62:109:151:105`

### Krok B: Kontrola reverzního DNS (PTR)
Následně jsme provedli zpětný dotaz na tyto IP adresy pomocí příkazu `host [IP]`.

**Výsledek pro IPv4:**
```bash
$ host 62.109.151.105
Host 105.151.109.62.in-addr.arpa. not found: 3(NXDOMAIN)
```
*Vysvětlení: Výsledek `NXDOMAIN` znamená, že v DNS neexistuje žádný záznam, který by k této IP adrese přiřadil jméno.*

**Výsledek pro IPv6:**
```bash
$ host 2001:1ab0:7e1e:151:62:109:151:105
Host 5.0.1.0.1.5.1.0.2.e.1.7.0.b.a.1.1.0.0.2.ip6.arpa. not found: 3(NXDOMAIN)
```
*Vysvětlení: Stejný výsledek pro IPv6 adresu.*

### Krok C: Srovnání s funkčním serverem
Pro ověření funkčnosti nástroje jsme otestovali IP adresu `62.109.154.92` (která je v MX záznamech domény):
```bash
$ host 62.109.154.92
92.154.109.62.in-addr.arpa domain name pointer mail075.webglobe.com.
```
*Zde je vidět, že PTR záznam je v pořádku nastaven na `mail075.webglobe.com`.*

---

## 2. Text e-mailu pro podporu

**Předmět:** Re: Nastavení DNS a doručitelnost - kbelstisokoli.cz (Diagnostika PTR)

Dobrý den,

děkuji za reakci. K vaší otázce ohledně diagnostiky chybějícího PTR záznamu uvádím podrobný postup, kterým jsme k tomuto závěru došli:

1. **Konfigurace aplikace:** Naše aplikace odesílá poštu přes hostitele **`mail.webglobe.cz`**.
2. **Rozklad adresy:** Tento hostitel směřuje na IP adresu **`62.109.151.105`** (resp. IPv6 `2001:1ab0:7e1e:151:62:109:151:105`).
3. **Zjištění:** Při provedení reverzního dotazu (PTR) na tyto konkrétní adresy vrací DNS servery chybu **`NXDOMAIN`** (záznam neexistuje).

**Konkrétní výstup z terminálu:**
`host 62.109.151.105` -> `Host 105.151.109.62.in-addr.arpa. not found: 3(NXDOMAIN)`

Chápeme, že zprávy mohou být dále distribuovány na jiné SMTP servery, nicméně k selhání doručení na servery jako Centrum.cz dochází již v momentě, kdy se naše aplikace (nebo vaše proxy) pokouší navázat spojení a cílový server prověřuje integritu odesílací IP/hostitele. Bez validního PTR pro `62.109.151.105` je doručitelnost na české freemaily kriticky ohrožena.

**Prosba:** Můžete prosím nastavit PTR záznamy pro IP `62.109.151.105` a IPv6 `2001:1ab0:7e1e:151:62:109:151:105` tak, aby ukazovaly na `mailproxy.webglobe.cz`?

Děkuji,
[Vaše Jméno]
