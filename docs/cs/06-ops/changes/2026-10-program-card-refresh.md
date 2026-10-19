# Obnova karet programu po změně docházky

Jednotlivá změna docházky vykreslí pouze vlastní kartu. Hromadná změna odesílá
události `attendanceUpdated.{type}.{eventId}` jen pro události vrácené serverem.
Karty `EventCard` používají Livewire `Isolate`, takže ani hromadná změna více
než 20 událostí nepřekročí limit komponent v jednom požadavku.
Výchozí bezpečnostní limit `payload.max_components` zůstává zachován.

Regresní test `EventCardRefreshTest` ověřuje 21 samostatných karet, konkrétní
posluchače, izolaci a uložení docházky bez plošného rozesílání obnovy.
