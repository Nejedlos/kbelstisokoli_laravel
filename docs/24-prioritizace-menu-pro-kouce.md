# 24. Prioritizace menu pro kouče

Tento dokument popisuje novou strukturu a pořadí menu v administraci (Filament PHP), které bylo optimalizováno pro potřeby koučů jako primárních uživatelů systému.

## Logika řazení skupin

Navigační skupiny jsou v `app/Providers/Filament/AdminPanelProvider.php` seřazeny tak, aby nejdůležitější pracovní nástroje byly umístěny v horní části menu.

### Pořadí skupin:
1.  **Sportovní agenda** (`sports_agenda`) – Hlavní pracovní nástroj kouče (tréninky, zápasy, týmy).
2.  **Komunikace** (`communication`) – Oznámení pro členy.
3.  **Správa uživatelů** (`user_management`) – Přístup k hráčským profilům a uživatelům.
4.  **Statistiky** (`statistics`) – Výsledky a klubové soutěže.
5.  **Obsah** (`content`) – Správa novinek a webových stránek.
6.  **Média** (`media`) – Správa fotografií a assetů.
7.  **Finance** (`finance`) – Platební předpisy a platby.
8.  **Nastavení** (`settings`) – Branding a konfigurace cronu.
9.  **Nastavení systému** (`system_settings`) – Pokročilá technická nastavení (přesměrování, logy).

## Pořadí prvků uvnitř skupin (Resources)

Jednotlivé Resource třídy mají implementovánu metodu `getNavigationSort()`, která zajišťuje logické pořadí v rámci dané skupiny.

### Skupina: Sportovní agenda
1.  **Tréninky** (1)
2.  **Zápasy** (2)
3.  **Týmy** (3)
4.  **Klubové akce** (4)
5.  **Sezóny** (5)
6.  **Soupeři** (6)

### Skupina: Správa uživatelů
1.  **Uživatelé** (1)
2.  **Hráčské profily** (2)
3.  **Role** (3)
4.  **Oprávnění** (4)

### Skupina: Statistiky
1.  **Klubové soutěže** (1)
2.  **Sady statistik** (2)
3.  **Externí zdroje** (3)

### Skupina: Obsah
1.  **Novinky** (1)
2.  **Kategorie novinek** (2)
3.  **Stránky** (3)
4.  **Menu** (4)

### Skupina: Média
1.  **Knihovna médií** (1)
2.  **Galerie** (2)

### Skupina: Finance
1.  **Předpisy plateb** (1)
2.  **Platby** (2)

## Implementace

Pořadí skupin je definováno v `AdminPanelProvider.php` metodou `navigationGroups()`:

```php
->navigationGroups([
    \Filament\Navigation\NavigationGroup::make()
         ->label(fn (): string => __('admin.navigation.groups.sports_agenda')),
    // ... další skupiny
])
```

Pořadí v rámci skupin je definováno v jednotlivých Resources:

```php
public static function getNavigationSort(): ?int
{
    return 1;
}
```

Tímto je zajištěno, že i při přidání nových Resource bude menu přehledné a prioritní pro uživatele.
