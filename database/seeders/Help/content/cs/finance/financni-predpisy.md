# Předpisy a tarify

Předpisy v systému KS určují, kolik má který člen zaplatit za dané období nebo akci. Jsou protipólem k platbám a jejich správné nastavení je základem pro sledování salda (dluhu/přeplatku).

### Tarify (Ceníky)
Tarify jsou šablony pro předpisy. Místo abyste každému hráči zadávali částku ručně, vytvoříte tarif (např. "Členský příspěvek U11 - podzim").
- **Název:** Srozumitelný název (vidí ho i člen).
- **Částka:** Kolik se má platit.
- **Sezóna:** Ke které sezóně tarif patří.
- **Kategorie:** Pro které typy členů je tarif určen.

### Hromadné generování předpisů
V sekci **Předpisy** můžete využít akci **Generovat předpisy**.
- Vyberete tarif a sezónu.
- Systém projde všechny aktivní členy odpovídající tarifu a automaticky jim vystaví předpis.
- Každý předpis dostane unikátní **Variabilní symbol (VS)**, který je svázán s profilem člena.

### Ruční úpravy a slevy
Někdy je potřeba předpis upravit individuálně:
- **Slevy:** Můžete upravit částku u konkrétního předpisu (např. sourozenecká sleva, sociální úleva).
- **Storno:** Pokud byl předpis vystaven chybně, lze jej stornovat. Systém pak částku po členovi nebude vymáhat.

### Statusy předpisů
- **Nezaplaceno:** Člen zatím nic neuhradil.
- **Částečně zaplaceno:** Byla provedena alokace platby, ale nepokryla celou sumu.
- **Zaplaceno:** Předpis je plně pokryt jednou nebo více platbami.
- **Přeplatek:** Na předpis bylo alokováno více peněz, než byla původní suma (systém toto hlídá).

### Doporučený postup
1. Nejdříve vytvořte **Tarify** pro celou sezónu.
2. Na začátku pololetí proveďte **Hromadné generování**.
3. Průběžně sledujte **Saldo** členů, které systém počítá jako `Suma plateb - Suma předpisů`.
