# Klubové soutěže (Club Competitions)

## Účel modulu
Modul umožňuje správu interních klubových soutěží a výzev, jako je např. "Lumír Trophy", "Střelec měsíce" nebo různé sportovní výzvy.
Soutěže mohou být dlouhodobé (přes celou sezónu) nebo krátkodobé.

## Technický popis
Modul se skládá ze dvou hlavních částí:
1.  **Soutěž (ClubCompetition):** Definice samotné soutěže, její název, popis, pravidla a sezóna.
2.  **Záznamy (ClubCompetitionEntry):** Jednotlivé body nebo výsledky připsané účastníkům.

### Lokalizace
Všechny textové pole (název, popis, pravidla, metrika) jsou plně bilingvní (CS/EN) a uloženy jako JSON v databázi pomocí balíčku `spatie/laravel-translatable`.

### Účastníci
Účastníkem soutěže může být:
-   **Člen klubu:** Registrovaný uživatel v databázi (vazba na `users`).
-   **Tým:** Vazba na konkrétní tým v klubu.
-   **Nečlen / Externí:** Osoba, která není v databázi. V takovém případě se vyplňuje pole "Jméno (Nečlen)".

## Způsob použití pro administrátory

### 1. Vytvoření soutěže
V administraci v sekci **Sportovní agenda -> Klubové soutěže** vytvořte novou soutěž.
-   Vyplňte název a popis v obou jazycích.
-   Vyberte sezónu a nastavte stav na "Probíhá".

### 2. Zadávání výsledků
V detailu (editaci) konkrétní soutěže přejděte na záložku **Výsledky / Leaderboard**.
-   Klikněte na "Přidat záznam".
-   Zadejte datum záznamu (kdy k výkonu došlo).
-   Vyberte hráče nebo zadejte jméno ručně.
-   Zadejte hodnotu (skóre/body).
-   Zvolte typ zápisu:
    -   **Přičíst k celku (Inkrementální):** Tato hodnota se přičte k celkovému skóre v leaderboardu.
    -   **Absolutní hodnota:** Tato hodnota je v rámci tohoto záznamu fixní (ale v leaderboardu se také sčítá s ostatními záznamy stejného účastníka).

## Frontend zobrazení
Soutěže se zobrazují na veřejném webu podle nastavení příznaku `is_public`. Leaderboard je automaticky generován na základě součtu všech zadaných záznamů pro každého účastníka.
Nad tabulkou záznamů v administraci je zobrazen widget "Průběžné pořadí (Leaderboard)", který automaticky sčítá body a zobrazuje je ve dvou sloupcích s vizuálním označením prvních tří míst (trofeje). Pokud mají účastníci stejný počet bodů, sdílejí stejnou pozici (sportovní ranking).
Tabulka záznamů pod leaderboardem slouží k přehledu jednotlivých přírůstků (zápisů) řazených primárně podle data (nejnovější nahoře).
