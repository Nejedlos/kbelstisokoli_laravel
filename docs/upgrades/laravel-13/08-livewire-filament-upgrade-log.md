# Livewire & Filament Upgrade Log - Laravel 13 Upgrade

## 1. Filament Plugins Revision
- `bezhansalleh/filament-language-switch`: Updated to `^5.0`.
    - BREAKING: API changed from static configuration to `configureUsing` in `AppServiceProvider`.
    - BREAKING: Custom inclusion via render hook in `AdminPanelProvider` was updated to use the native v5 rendering.
- `filament/spatie-laravel-media-library-plugin`: Updated to `^5.4` (handled in core upgrade).

## 2. Livewire Components Modernization
- `App\Livewire\ContactForm`:
    - Migrated from `rules()` method to `#[Validate]` attribute on properties.
- `App\Livewire\RecruitmentForm`:
    - Migrated from `rules()` method to `#[Validate]` attribute.
- `App\Livewire\Member\AvatarModal`:
    - Migrated from `$listeners` array to `#[On]` attribute on methods.
- General: Verified `@entangle` and `wire:model` usage. Livewire 4 handles these efficiently.

## 3. Filament Resources & Pages
- Verified and enforced the use of `Filament\Schemas\Schema` as per project guidelines (Section 12.1).
- Updated `PostsTable.php`:
    - Renamed `recordActions()` to `actions()`.
    - Renamed `toolbarActions()` to `bulkActions()`.
    - Fixed namespace for `BulkActionGroup`, `DeleteBulkAction`, and `EditAction` to `Filament\Tables\Actions`.

## 4. Known Risks & Issues
- **Custom Schemas:** The project uses a non-standard `Filament\Schemas\Schema` namespace (standard is `Filament\Forms\Form`). While this is according to project guidelines, it may cause confusion with standard Filament documentation.
- **Translatable Fields:** Global search is disabled for resources with translatable fields to avoid issues with `json_unquote` on specific hosting environments (Webglobe).
- **Language Switcher (Fix):** The automatic plugin rendering in the User Menu was disabled in favor of the custom Blade component `filament.components.language-switch`. This ensures visual consistency across the frontend, member section, and admin panel, as requested by the user.

## 5. Verification
- `php artisan filament:upgrade` executed successfully.
- `php artisan about` confirms Filament v5.4.4 and Livewire v4.2.4.
- Basic smoke tests for admin resources (PostResource) are passing.
- Custom language switcher is manually included in `AdminPanelProvider.php` via `panels::global-search.after` hook.
