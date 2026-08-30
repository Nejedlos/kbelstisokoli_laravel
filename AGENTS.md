# Codex project guide

## Project

- Laravel 13 / PHP 8.4 application with Filament 5, Livewire, Tailwind CSS 4, Fortify, and Spatie Permission.
- Keep the three application areas separate: public (`routes/public.php`, `App\Http\Controllers\Public`), member (`routes/member.php`, `App\Http\Controllers\Member`), and admin (Filament resources plus `routes/admin.php`).
- Read the relevant page in `docs/cs/` and the matching administrator help content in `database/seeders/Help/content/cs/` before changing established domain behavior.

## Domain boundaries

- `User` is the authentication, club-membership, and authorization entity.
- `PlayerProfile` is optional sports data. Team membership belongs to `player_profile_team`; `is_on_roster` is a public/official-roster flag, not a login role.
- System access is controlled by Spatie roles and permissions. Never infer authorization solely from a profile or team pivot.
- Preserve manually assigned privileged roles when synchronizing domain-derived roles.

## Implementation conventions

- Put reusable domain logic in `app/Services`; keep Filament schemas and page hooks thin.
- Use enum values instead of duplicated role or membership strings where practical.
- Filament user forms live under `app/Filament/Resources/Users/`; keep create and edit behavior consistent.
- User-facing strings must be localized in `lang/cs` and `lang/en` or use the established translated enum labels.
- Update both technical documentation in `docs/cs/` and administrator help content when workflows change.

## Database

- Migrations must be idempotent: guard tables/columns with `Schema::hasTable` / `Schema::hasColumn`.
- Production compatibility is critical. Use `longText` for JSON-encoded arrays and cast them in Eloquent; do not introduce a native JSON column without verifying the current production database policy.
- Preserve and backfill legacy data when replacing a scalar field with a multi-value field.
- Never put account- or person-specific repair logic into a general schema migration.

## Verification

- Run focused tests first: `php artisan test --filter=<name>`.
- Run the full PHP suite with `composer test` when the focused tests pass.
- Format changed PHP files with `vendor/bin/pint --dirty`.
- Run `npm run build` only when frontend assets or classes changed.
- Do not use the real `.env` database for tests; PHPUnit is configured for SQLite in memory.

## Safety

- Treat authentication, roles, permissions, member visibility, finance, and migrations as high-risk areas.
- Add regression tests for role synchronization, preservation of privileged roles, legacy data migration, and multi-role users.
- Do not expose secrets from `.env`, production logs, dumps, or historical fixture files.
