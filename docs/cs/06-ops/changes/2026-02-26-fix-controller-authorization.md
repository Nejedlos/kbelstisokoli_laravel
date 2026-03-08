# Změna: Oprava autorizace v kontrolerech

## Popis problému
V prostředí produkce byla hlášena chyba `Call to undefined method App\Http\Controllers\Member\TeamController::authorize()`. Tato chyba vznikla v důsledku přechodu na Laravel 12, kde základní třída `Controller` již ve výchozím stavu neobsahuje trait `AuthorizesRequests`.

## Provedené změny
- Do souboru `app/Http/Controllers/Controller.php` byl přidán trait `Illuminate\Foundation\Auth\Access\AuthorizesRequests`.
- Tím byla obnovena dostupnost metody `$this->authorize()` ve všech kontrolerech dědících od základního kontroleru.

## Verifikace
- Byl vytvořen test `tests/Feature/TeamControllerTest.php`, který ověřuje funkčnost autorizace na routě `/clenska-sekce/tymove-prehledy`.
- Testy potvrdily, že autorizace nyní probíhá správně (vratí 403 pro nepovolený přístup a 200 pro povolený).

## Datum
26. 02. 2026
