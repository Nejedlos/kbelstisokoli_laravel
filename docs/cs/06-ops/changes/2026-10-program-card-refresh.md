# Obnova karet programu po změně docházky

Jednotlivá změna docházky vykreslí pouze vlastní kartu. Hromadná změna odesílá
události `attendanceUpdated.{type}.{eventId}` jen pro události vrácené serverem.
Karty `EventCard` používají Livewire `Isolate`, takže ani hromadná změna více
než 20 událostí nepřekročí limit komponent v jednom požadavku.
Výchozí bezpečnostní limit `payload.max_components` zůstává zachován.

Regresní test `EventCardRefreshTest` ověřuje 21 samostatných karet, konkrétní
posluchače, izolaci a uložení docházky bez plošného rozesílání obnovy.

Karta zápasu v programu upřednostňuje celý název soupeře a dovoluje jeho zalomení na více řádků. Štítek Doma/Venku vychází z uloženého `is_home`, který import určuje z domácí a hostující strany rozpisu ČBF/PBasket; neodhaduje se z názvu haly. Neurčená strana se výslovně označí. Na užších obrazovkách jsou ovládací prvky pod názvem zápasu.

Rozložení se řídí skutečnou šířkou karty (container queries), takže funguje i v úzkém panelu nástěnky. Místo konání se nezkracuje; tlačítka účasti mají text i na mobilu, dotykovou výšku nejméně 56 px a přehled zahrnuje i odpovědi Možná.
