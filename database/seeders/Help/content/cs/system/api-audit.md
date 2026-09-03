# Historie změn a Audit logy

Změnil někdo barvu v nastavení nebo smazal hráče a vy nevíte kdo? Audit logy slouží ke sledování všech důležitých akcí v systému.

## Kde sekci najdete
V administraci v levém menu pod položkou **Systém > Historie změn**.

## Co logujeme
Systém automaticky zaznamenává:
- **Událost**: Vytvoření (Created), Úprava (Updated), Smazání (Deleted).
- **Model**: O jaký typ dat šlo (např. User, Post, Team).
- **Kdo**: Jméno a e-mail uživatele, který akci provedl.
- **Kdy**: Přesný čas a datum.

## Vyhledávání a filtrace
Pokud hledáte konkrétní událost:
1. Použijte vyhledávání v tabulce podle jména uživatele nebo e-mailu.
2. Filtrujte podle **Události** (např. chcete vidět jen smazané záznamy).
3. V detailu záznamu (ikonka oka) uvidíte, co přesně se změnilo – původní hodnota (Old) vs. nová hodnota (New).

## Důležité upozornění
- **Obnova smazaných dat**: Audit log slouží pro informaci o smazání, ale neobsahuje tlačítko pro automatickou obnovu smazaného záznamu. Obnova dat vyžaduje technický zásah administrátora databáze.
- **Retence dat**: Staré záznamy v logu mohou být po čase automaticky mazány, aby systém zůstal rychlý.

### Podrobnosti telefonu a prohlížeče

V detailu události, v metadatech pod `device`, najdete dostupný model telefonu, systém a jeho verzi, mobilní režim a hlavičky s úplnou verzí prohlížeče. Tyto údaje se zaznamenávají nově při návštěvě a přihlášení; starší záznamy je nemají. Pokud prohlížeč údaje neposkytuje, hodnota zůstane prázdná (`null`). Obecné označení `Android 10; K` není skutečný model telefonu. Údaje hlásí prohlížeč a mohou být změněné, proto neslouží k ověření identity uživatele.
