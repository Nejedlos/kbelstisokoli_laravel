# Prevence duplicit zápasů

Ruční vytvoření, ruční editace a import používají společnou službu
`App\Services\Matches\MatchWriteService`. Zápas se zapisuje společně s vazbami
na týmy v jedné transakci. Kontrola zahrnuje legacy `team_id` i vazbu
`basketball_match_team`.

## Ruční zadávání

Při přesné shodě sezóny, některého vybraného týmu, termínu, soupeře a směru
domácí/venku se uložení odmítne. Administrace zobrazí odkaz na existující zápas.
Editace vylučuje vlastní ID; změna týmů nesmí vytvořit kolizi s jiným záznamem.
Typ zápasu není způsob, jak ochranu obejít. Odlišný čas, soupeř nebo tým může
představovat samostatné utkání, například turnajový dvojzápas.
Pro stejného soupeře a den s jiným časem formulář zobrazí upozornění s odkazy
na podobná utkání; samo upozornění uložení neblokuje.

Názvy soupeřů se porovnávají konzervativně: diakritika, mezery, běžný úvodní
prefix TJ/SK/BK/BC a slovo Praha nemění shodu. Identita klubu, písmeno týmu
a věková kategorie zůstávají zachované; dříve zamítnuté sloučení se respektuje.

## Automatický import

Stabilní externí ID se hledá pro danou sezónu a tým. Změna termínu aktualizuje
stávající záznam a zachová jeho ID a URL. Ručně založený zápas bez externího ID
lze převzít pouze při jednoznačné přesné shodě. Nejednoznačnost nebo konflikt
externích ID zastaví zápis k prověření. Běžný import zápasy neslučuje ani nemaže.

Párování nepoužívá toleranci ±120 minut ani obecné pravidlo „jeden zápas denně“.
`MatchIdentityKey` s datem bez času není unikátní databázovou identitou.

## Souběh a zachování dat

Před kontrolou se zamykají existující řádky dotčených týmů ve stabilním pořadí.
Editace zahrne původní i nově vybrané týmy. Kandidáti se znovu načtou až po
získání zámků, takže import a ruční uložení používají stejnou ochranu i při
souběhu. Uložení týmových vazeb mimo tuto službu nesmí ochranu obejít.

Odmítnutí duplicity nepřesouvá docházku, statistiky ani poznámky. Aktualizace
známého importovaného zápasu zachová původní záznam a jeho navázaná data.
Existující podezřelé duplicity se automaticky neopravují slučováním.

## Pravidelný audit

Plánovač ve 04:00 spouští pouze kontrolu bez zápisu:

```bash
php artisan stats:cleanup-duplicates --dry-run
```

Starší příkazy pro skutečné slučování jsou samostatná údržba; běžná synchronizace
ani pravidelný audit je nevolají. Hlášený kandidát sám o sobě nepotvrzuje duplicitu.
