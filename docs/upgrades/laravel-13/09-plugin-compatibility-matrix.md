# Plugin Compatibility Matrix - Laravel 13 Upgrade

| Package | Current Version | Target Version | Compatible | Action | Notes |
|---------|-----------------|----------------|------------|--------|-------|
| `filament/filament` | `v5.2.2` | `^5.4` | Yes | Updated | Upgraded to 5.4.4 in core upgrade step. |
| `bezhansalleh/filament-language-switch` | `^4.1` | `^5.0` | Yes | Updated | v5.0+ supports Filament v5 and L13. Updated to v5.0.0-beta.1. |
| `filament/spatie-laravel-media-library-plugin` | `^5.2` | `^5.4` | Yes | Updated | Upgraded to 5.4.x in core upgrade step. |
| `livewire/livewire` | `v4.1.4` | `^4.2` | Yes | Updated | Upgraded to 4.2.4 in core upgrade step. |
| `livewire/volt` | `^1.10.3` | `^1.10.4` | Yes | Updated | Upgraded to 1.10.4 in core upgrade step. |
| `spatie/laravel-medialibrary` | `^11.20` | `^11.20` | Yes | Yes | Compatible with L13. |
| `spatie/laravel-permission` | `^7.2` | `^7.2` | Yes | Yes | Compatible with L13. |
| `spatie/laravel-translatable` | `^6.13` | `^6.13` | Yes | Yes | Compatible with L13. |

## Actions
- [ ] Upgrade `bezhansalleh/filament-language-switch` to `^5.0`.
- [ ] Run `php artisan filament:upgrade`.
- [ ] Check `AppServiceProvider` or `AdminPanelProvider` for `LanguageSwitch` configuration and update to new API.
