# Audit pro migraci na nativní JSON (MySQL 8)

Tento dokument slouží jako podrobný seznam míst, která je nutné upravit při přechodu z `LONGTEXT` (emulovaný JSON) na nativní `JSON` typ v MySQL 8. Přechod umožní přesnější vyhledávání (pouze v konkrétním jazyce) a lepší výkon.

## 1. Konfigurace prostředí (Lokální .env.production)

Změnit parametry pro správnou detekci MySQL 8 a vypnutí MariaDB režimu v lokálním souboru `.env.production` (soubor je spravován offline a na produkci se nepřenáší):

- [ ] `DB_VERSION=8.0` (místo `10.3.0`)
- [ ] `DB_MARIADB=false` (místo `true`)

## 2. Databázové tabulky a sloupce (Migrace)

Vytvořit souhrnnou migraci, která změní typy těchto sloupců z `longText`/`text` na `json`.

### Tabulky s překlady ($translatable)
- [ ] `announcements`: `title`, `message`, `cta_label`
- [ ] `matches`: `notes_public`
- [ ] `club_competitions`: `name`, `description`, `metric_description`, `rules`
- [ ] `club_events`: `title`, `description`
- [ ] `fine_templates`: `name`, `description`
- [ ] `galleries`: `title`, `description`
- [ ] `menu_items`: `label`
- [ ] `pages`: `title`, `content`
- [ ] `partners`: `label`, `description`
- [ ] `permissions`: `display_name`
- [ ] `photo_pools`: `title`, `description`
- [ ] `posts`: `title`, `excerpt`, `content`
- [ ] `post_categories`: `name`, `description`
- [ ] `roles`: `display_name`
- [ ] `seo_metadatas`: `title`, `description`, `og_title`, `og_description`, `keywords`
- [ ] `settings`: `value`
- [ ] `statistic_sets`: `name`, `description`
- [ ] `teams`: `name`, `description`

### Tabulky s JSON daty (metadata, casts)
- [ ] `users`: `notification_preferences`, `metadata`
- [ ] `club_events`: `metadata`
- [ ] `feedback_reports`: `viewport`, `screen`, `meta`
- [ ] `statistic_sets`: `scope`, `column_config`, `settings`
- [ ] `ai_documents`: `keywords`, `metadata`, `summary`
- [ ] `ai_request_logs`: `token_usage`, `metadata`
- [ ] `audit_logs`: `metadata`, `changes`
- [ ] `external_import_logs`: `old_values`, `new_values`
- [ ] `external_stat_sources`: `extractor_config`, `mapping_config`
- [ ] `financial_tariffs`: `installment_plan`, `metadata`
- [ ] `match_predictions`: `factors`, `explanation_points`
- [ ] `page_blocks`: `data`, `custom_attributes`
- [ ] `statistic_rows`: `values`, `source_metadata`
- [ ] `ai_settings`: `model_presets`
- [ ] `attendances`: `metadata`
- [ ] `basketball_matches`: `metadata`
- [ ] `club_competition_entries`: `metadata`
- [ ] `competition_standings`: `metadata`
- [ ] `external_entity_mappings`: `metadata`
- [ ] `external_import_runs`: `metadata`
- [ ] `external_player_matches`: `metadata`
- [ ] `external_team_mappings`: `metadata`
- [ ] `external_team_season_configs`: `metadata`
- [ ] `finance_charges`: `metadata`
- [ ] `finance_payments`: `metadata`
- [ ] `leads`: `payload`
- [ ] `legacy_import_batches`: `metadata`
- [ ] `opponents`: `metadata`
- [ ] `player_profiles`: `metadata`
- [ ] `trainings`: `metadata`
- [ ] `user_season_configs`: `metadata`
- [ ] `venues`: `metadata`

## 3. Vyhledávání ve Filamentu (Resources)

Aktualizovat metodu `searchable()` u translatable polí tak, aby se dotazovala přímo na klíč v JSONu (aktuální jazyk), místo celého textu.

### Lokace k opravě:
- [ ] `app/Filament/Resources/Announcements/Tables/AnnouncementsTable.php` (řádky 29, 35)
- [ ] `app/Filament/Resources/ClubEvents/Tables/ClubEventsTable.php` (řádky 23, 50)
- [ ] `app/Filament/Resources/PhotoPools/PhotoPoolResource.php` (řádky 414, 440)
- [ ] `app/Filament/Resources/Posts/Tables/PostsTable.php` (řádky 27, 34)
- [ ] `app/Filament/Resources/BasketballMatches/Tables/BasketballMatchesTable.php` (řádek 37)
- [ ] `app/Filament/Resources/ClubCompetitions/Tables/ClubCompetitionsTable.php` (řádek 21)
- [ ] `app/Filament/Resources/Galleries/Tables/GalleriesTable.php` (řádek 21)
- [ ] `app/Filament/Resources/HelpArticles/RelationManagers/FaqsRelationManager.php` (řádek 62)
- [ ] `app/Filament/Resources/HelpArticles/RelationManagers/QuickActionsRelationManager.php` (řádek 74)
- [ ] `app/Filament/Resources/HelpArticles/RelationManagers/RelatedArticlesRelationManager.php` (řádek 34)
- [ ] `app/Filament/Resources/HelpArticles/Tables/HelpArticlesTable.php` (řádek 22)
- [ ] `app/Filament/Resources/HelpCategories/Tables/HelpCategoriesTable.php` (řádek 24)
- [ ] `app/Filament/Resources/Menus/RelationManagers/ItemsRelationManager.php` (řádek 68)
- [ ] `app/Filament/Resources/Pages/Tables/PagesTable.php` (řádek 21)
- [ ] `app/Filament/Resources/PostCategories/PostCategoryResource.php` (řádek 78)
- [ ] `app/Filament/Resources/StatisticSets/Tables/StatisticSetsTable.php` (řádek 21)
- [ ] `app/Filament/Resources/Teams/Tables/TeamsTable.php` (řádek 26)

## 4. Modely (Kontrola)

Ponechat `$casts = ['column' => 'array']`. Laravel automaticky rozpozná nativní JSON a bude s ním pracovat správně, pokud je typ v DB nastaven na `JSON`.

## 5. Deployment a Ostatní
- [ ] Spustit `php artisan migrate` na produkci po úpravě `.env`.
- [ ] Volitelně: Přidat indexy pro nejčastěji vyhledávaná pole (např. `title->cs` v `posts`).
