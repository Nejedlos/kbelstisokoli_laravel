# Oprava doručitelnosti e-mailů (DNS nastavení)

Tento dokument obsahuje přesné instrukce pro úpravu DNS záznamů domény `kbelstisokoli.cz` u poskytovatele Webglobe za účelem vyřešení problémů s doručováním e-mailů (zejména na servery typu `@centrum.cz`, `@seznam.cz`, `@gmail.com`).

## 1. Úprava SPF záznamu (Sender Policy Framework)

SPF určuje, které servery smí odesílat e-maily jménem vaší domény. Aktuálně máte nastaveno "SoftFail" (`~all`), což některé servery penalizují.

*   **Typ:** TXT
*   **Název (Host):** `@` (v některých administracích se nechává prázdné nebo se píše název domény `kbelstisokoli.cz.`)
*   **Původní hodnota:** `v=spf1 a mx include:_spf.webglobe.cz ~all`
*   **Nová hodnota:** `v=spf1 a mx include:_spf.webglobe.cz -all`
*   **Změna:** Nahraďte vlnovku `~` před slovem `all` pomlčkou `-`.

## 2. Úprava DMARC záznamu

DMARC propojuje SPF a DKIM a říká přijímací straně, co má dělat, pokud e-mail neprojde kontrolou. Také umožňuje zasílání reportů o doručování na váš nový e-mail.

*   **Typ:** TXT
*   **Název (Host):** `_dmarc` (výsledný záznam je `_dmarc.kbelstisokoli.cz.`)
*   **Původní hodnota:** `v=DMARC1; p=none;`
*   **Nová hodnota:** `v=DMARC1; p=none; rua=mailto:dmarc@kbelstisokoli.cz`
*   **Změna:** Původní krátký záznam nahraďte tímto delším, který obsahuje instrukci pro zasílání reportů (`rua`).

## 3. Žádost na podporu Webglobe (Klíčové!)

Nejdůležitější chybou jsou chybějící **PTR záznamy** (reverzní DNS). Tyto záznamy nemůžete v DNS panelu změnit sami, musí je nastavit správce sítě (Webglobe) pro své IP adresy. Bez nich vás servery jako `@centrum.cz` budou vždy považovat za podezřelé.

Pošlete na podporu Webglobe (např. přes klientský panel nebo e-mail) následující žádost:

---
**Předmět:** Žádost o nastavení PTR záznamů pro IP adresy odesílacích serverů

Dobrý den,

u naší domény `kbelstisokoli.cz` řešíme problémy s doručitelností e-mailů (odmítání ze strany Centrum.cz/Seznam.cz). Diagnostika odhalila, že naše odesílací servery postrádají validní reverzní DNS (PTR) záznamy.

Prosím o nastavení PTR záznamů pro následující IP adresy:

1.  **IP:** `62.109.151.105` (mail.webglobe.cz) -> nastavit PTR na: `mail.webglobe.cz`
2.  **IP:** `62.109.154.160` (smtp.kbelstisokoli.cz) -> nastavit PTR na: `smtp.kbelstisokoli.cz`

Prosím také o kontrolu, zda mají odpovídající PTR záznamy i IPv6 adresy těchto serverů, pokud jsou využívány pro odchozí SMTP provoz.

Děkuji,
[Vaše Jméno]
---

## 4. Přehled změn (Tabulka)

| Záznam | Co najít a odstranit (Původní) | Co zadat místo toho (Nové) |
| :--- | :--- | :--- |
| **SPF** (TXT @) | `v=spf1 a mx include:_spf.webglobe.cz ~all` | `v=spf1 a mx include:_spf.webglobe.cz -all` |
| **DMARC** (TXT _dmarc) | `v=DMARC1; p=none;` | `v=DMARC1; p=none; rua=mailto:dmarc@kbelstisokoli.cz` |
| **PTR** | (Nelze v DNS panelu měnit) | Vyžaduje zásah podpory (viz bod 3) |

## 5. Co dál?
1. **Změňte DNS:** Proveďte změny SPF a DMARC v administraci Webglobe.
2. **Napište podpoře:** Odešlete žádost o PTR záznamy.
3. **Sledujte e-mail:** Po cca 24-48 hodinách začnou na adresu `dmarc@kbelstisokoli.cz` chodit XML reporty. Ty nám v dalším kroku pomohou ověřit, že je vše v pořádku a můžeme DMARC politiku zpřísnit na `p=quarantine`.
