# 23. Lokalizace menu administrace

Tento dokument popisuje implementaci bilingvního menu v administraci (Filament PHP) pro češtinu (`cs`) a angličtinu (`en`).

## Centralizace překladů

Všechny překlady související s navigací, názvy zdrojů (Resources), skupinami (Groups) a stránkami (Pages) jsou umístěny v dedikovaných souborech:
- `lang/cs/admin.php`
- `lang/en/admin.php`

### Struktura jazykového souboru
Soubor je rozdělen na tři hlavní sekce:
- `navigation.groups`: Názvy skupin v menu.
- `navigation.pages`: Názvy samostatných stránek (Dashboard, Branding atd.).
- `navigation.resources`: Názvy modelů a jejich množná čísla pro jednotlivé Filament Resources.

## Implementace v Resources

Aby bylo menu dynamicky přeloženo při změně jazyka, statické vlastnosti v třídách Resource byly nahrazeny metodami:

```php
public static function getNavigationGroup(): ?string
{
    return __('admin.navigation.groups.content');
}

public static function getModelLabel(): string
{
    return __('admin.navigation.resources.post.label');
}

public static function getPluralModelLabel(): string
{
    return __('admin.navigation.resources.post.plural_label');
}
```

## Lokalizace Dashboardu

Protože výchozí `Filament\Pages\Dashboard` neumožňuje snadnou lokalizaci titulku v menu bez přepsání, byla vytvořena vlastní třída `App\Filament\Pages\Dashboard`, která dědí z původní a implementuje metody `getNavigationLabel()` a `getTitle()`. Tato třída je následně zaregistrována v `AdminPanelProvider`.

## Uživatelské menu (Profile menu)

Statické prvky v uživatelském menu (např. odkaz na "Členskou sekci") jsou lokalizovány přímo v `AdminPanelProvider.php` pomocí anonymních funkcí:

```php
MenuItem::make()
    ->label(fn (): string => __('admin.navigation.pages.member_section'))
```

## Ikony a Font Awesome

V souladu s guidelines projektu jsou u vybraných Resources (např. `UserResource`) Heroicons nahrazeny ikonami **Font Awesome 7 Pro Light** (`fa-light`) pomocí `HtmlString` v metodě `getNavigationIcon()`.
