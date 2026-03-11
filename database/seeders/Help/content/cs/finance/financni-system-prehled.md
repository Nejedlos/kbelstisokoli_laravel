# Finanční systém klubu

Vítejte v přehledu finančního systému klubu Kbelští sokoli. Tento modul slouží k transparentní správě členských příspěvků, poplatků za soustředění, turnaje a další sportovní aktivity.

## Základní pojmy

Aby systém fungoval správně, rozlišujeme tři klíčové prvky:

1.  **Předpis platby (Dluh):** To je informace o tom, **kolik a za co** má člen zaplatit. Předpis má svůj název (např. "Příspěvky Jaro 2024"), částku a datum splatnosti.
2.  **Platba (Příjem):** To jsou reálné peníze, které dorazily na bankovní účet klubu nebo byly předány v hotovosti. Platba obsahuje částku, datum a variabilní symbol.
3.  **Alokace (Párování):** Proces, při kterém **propojíme platbu s předpisem**. Teprve v momentě alokace se předpis označí jako "Zaplacený".

## Jak to funguje v praxi?

Administrátor vytvoří **Předpis** (např. 2000 Kč za soustředění). Vy jako člen (nebo rodič) uvidíte tento předpis ve svém profilu spolu s platebními údaji.
Jakmile pošlete peníze, administrátor je v systému zaeviduje jako **Platbu** a provede **Alokaci** na váš předpis. Tím se váš dluh vynuluje.

## Časté dotazy

### Proč vidím v profilu "K úhradě", i když jsem již zaplatil?
Pravděpodobně ještě neproběhlo spárování (alokace) vaší platby administrátorem. Synchronizace s bankou nemusí být okamžitá. Pokud stav trvá déle než týden, kontaktujte hospodáře klubu.

### Co je to variabilní symbol (VS)?
VS je unikátní kód, podle kterého systém pozná, ke kterému členovi platba patří. VS naleznete u každého předpisu ve své členské sekci.

> [!TIP]
> Vždy uvádějte správný variabilní symbol. Urychlíte tím zpracování své platby a vyhnete se zbytečným upomínkám.
