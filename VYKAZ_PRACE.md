# Výkaz práce - Kbelští sokoli

**Celkový čas:** 223h 16m

---

### 2026-05-06 (2h 28m)
- feat(ui): update dashboard design for improved readability
- feat(performance, cache): enhance Full Page Cache mechanism
- perf(cache): optimalizace full page cache - zkrácení intervalu na 30 min a přidání in-place update
- cleanup: remove accidental re-diagnose.php
- fix(cache): zajistit správné generování URL pro EN verzi při primingu
- docs: aktualizace informací o bilingvním primingu cache
- fix(locale): oprava pořadí middleware pro správné přepínání jazyka při zapnuté cache
- fix(frontend): lokalizace odkazů v gridu karet na homepage
- feat(analytics): add internal analytics module for traffic monitoring
- chore(analytics): remove unused ActionSize enum import
- feat(security): handle UTM parameters in password reset links
- fix(cache, auth): zabránění cachování stránek s CSRF tokeny a auth cest (oprava 419/403 chyb)

### 2026-05-05 (4h 25m)
- feat(logging): enhance email debugging and optimize log processing
- feat(ui, nav): add "Contact Us" link and improve mobile navigation
- feat(ops, queue): fix stats synchronization and automate queue processing
- feat(performance, stats): optimize caching and fix statistics visibility issues
- feat(sync, scoring): fix missing match results and improve sync logic
- feat(ops, scheduler): fix queue execution and remove duplicate tasks
- feat(security, ux): enforce 2FA for admins and add 2FA nudges for users
- feat(i18n): improve 2FA mandatory message translations
- feat(security, ux): implement password validation and improve profile UX

### 2026-05-04 (5h 46m)
- feat(cache, maintenance): enhance caching logic and improve cleanup processes
- feat(auth, 2fa): improve 2FA cookie handling and session management
- feat(dmarc, ui): enhance table actions and improve email debug form
- feat(events, ui): add event attendance stats and improve email debugging
- feat(ui, widgets): add Cron Heartbeat widget for scheduler monitoring
- feat(ui): improve widget icons with HtmlString and add synchronous queue fallback
- feat(auth, ui): add user activation checks and improve error handling
- feat(audit, auth): log failed logins and password resets in audit log
- feat(events, media): add media and attachments support for club events
- feat(search, articles): enhance search scoring and improve article metadata handling
- feat(help, search): improve indexing and search logic for help articles
- feat(logging, resources): remove TestResource and improve logging mechanisms
- feat(logging): enhance email debugging and refine logging mechanisms
- feat(logging): standardize DEBUG_MAIL log tags for consistency

### 2026-05-03 (5h 17m)
- feat(dmarc): implement DMARC monitoring and reporting functionality
- feat(policies, ops): add DMARC policies and update environment management docs
- feat(cleanup): remove unused JS bundle from public assets
- feat(ui, config): update styling and add mail configuration support
- feat(docs, config): update production environment and public path setup
- feat(docs, ops): document resolution for .env permission issues on production
- feat(middleware): improve full-page cache handling for guest requests
- feat(performance): optimize settings initialization and production behavior
- feat(performance, middleware): optimize performance defaults and limit input lengths
- feat(dmarc, docs): add DMARC testing and improve monitoring workflows
- feat(dmarc): enhance DMARC test command with batch support and improved SMTP fallback
- feat(mail): add debug mail command and improve SMTP config handling
- feat(dmarc): improve filename parsing and add production SMTP option
- feat(dmarc): add test notification script and improve incident forms
- feat(forms): replace DateTimePicker with Placeholder in log forms
- feat(dmarc): implement DMARC analysis, alerting, and resource management
- feat(tests, ui): improve SQLite in-memory testing and Livewire stability
- feat(dmarc, docs): add advanced DMARC monitoring documentation and related updates
- feat(ui): fix expanded state management in standings table

### 2026-05-02 (7h 52m)
- feat(database): standardize users table query in UsersTable
- feat(auth): improve rate limits and reset URL generation
- feat(auth): refactor password reset handling and improve security
- feat(auth): enhance password reset throttling and error handling
- feat(auth): improve email handling and reset logic for better security
- feat(auth): enforce stricter password requirements and update placeholders
- feat(logging): improve logging for password reset and login flows
- feat(auth): enhance password handling and debug logging
- feat(css): add Font Awesome fix and integrate into build
- feat(docs, css): improve deployment docs and fix Font Awesome integration
- feat(logging): add detailed debug logging to FullPageCache middleware
- feat(logging): remove debug logs from FullPageCache middleware
- fix(domain): update URLs to use the correct production domain
- feat(migrations): add upcoming events toggle to homepage hero content
- feat(ui, cookies): implement cookie consent banner with settings
- feat(ui, analytics): improve cookie consent UI and integrate analytics configs
- feat(middleware): enhance feedback widget injection logic
- feat(ai-index): improve multilingual handling and data consistency
- feat(search): enhance multilingual support and search functionality
- feat(models, search): add robust localization handling for document fields
- feat(translations, ui): enhance AI search localization and UI consistency
- feat(search, translations): extend global search and update translations

### 2026-05-01 (3h 38m)
- feat(ui): add basketball match detail page template
- feat(output): remove legacy output logging file for cleanup
- feat(debug): improve email debugging information and logging
- feat(debug): enhance email debug info and add test email action
- feat(auth): refactor password reset flow and enhance environment setup
- feat(database): simplify table queries in UsersTable
- feat(sync): add season format validation and UI improvements
- feat(debug): add database diagnostic script and enhance logging
- chore: vylepšení logování a dynamické názvy tabulek
- chore: aktualizace .gitignore pro ochranu citlivých dat
- style: opravy formátování (Pint)
- feat(debug): enhance logging and simplify table queries
- feat(debug): update logging and comment out unused relation managers
- feat(database): standardize users table query in UsersTable

### 2026-04-30 (1h 18m)
- feat: přidání konfigurací a přesměrování ze starého webu
- feat: logování e-mailových událostí pro ladění
- feat: improve layout responsiveness and grid structures
- feat: improve header responsiveness and navigation overflow handling

### 2026-04-23 (5h 15m)
- feat(search): add resource type labels and localization
- feat(public): add team roster display with sorting and localization
- feat(public): refine team roster UI and layout
- feat(player): enhance player photo handling and caching
- feat(diagnose): add media diagnostics script for player photos
- feat(scripts): add test script for downloading player photos and update diagnostics
- feat(sync): enhance player photo sync with improved logging and path handling
- feat(sync): add file existence check for player photo sync
- feat(scripts): remove obsolete diagnostic and test scripts
- style(roster): zlepšení zarovnání fotek a výšky karet v soupisce
- docs: dokumentace robustního postupu nasazení (rsync + ssh reset)
- feat(localization): update translations and references for main club naming
- feat(cms): add Basketball News module with seeder and frontend integration
- feat(seeder): add Kbelští sokoli website launch news
- feat(seeder): replace BasketballNewsSeeder with updated PostSeeder
- feat(ui): improve partner section alignment in footer
- feat(ai-news): add AI-powered weekly news generation system
- feat(ui): add data attributes for footer partner section elements
- feat(cms): enhance Posts module with localization and image optimization
- feat(cms): improve file upload handling and image processing
- feat: vylepšení plynulosti mizejícího globálního loaderu pro uploady
- fix: oprava zobrazení náhledových obrázků u novinek (přechod na Media Library)
- fix: vylepšení detekce dokončení uploadu a uložení pro globální loader
- fix: robustnější detekce konce uploadu a uložení pro globální loader
- chore: návrat unhandledrejection listeneru do globálního loaderu
- fix: refaktoring globálního loaderu na Alpine Store pro lepší stabilitu
- feat: odstranění globálního loaderu z nahrávání souborů pro lepší stabilitu
- fix: oprava zpracování absolutních URL v x-picture komponentě
- feat: použití 'large' konverze pro obrázky novinek (WebP podpora)
- fix: ošetření existence Alpine store v globálním loaderu
- Oprava limitů pro média a stabilizace nahrávání v administraci

### 2026-04-22 (1h 7m)
- feat(public): add team rosters page with dynamic data rendering
- feat(migrations): improve legacy database migrations and attendance handling
- feat(sync): improve legacy system sync logic and scheduling
- feat(search): add resource type labels and localization

### 2026-04-11 (1h 0m)
- feat(admin): add cancel and mark-as-stuck actions for external imports
- feat(sync): enhance player photo sync command and handling logic

### 2026-04-09 (1h 38m)
- feat(sync): enhance retry logic and update nullable column
- feat(sync): enhance handling of best player photos and local user matching
- feat(sync): add player photo sync command and update photo handling
- feat(sync): improve best player mapping and ghost user handling
- feat(sync): improve player photo synchronization and fallback mechanisms
- feat(admin): restructure admin competition standings UI
- feat(admin): fix namespace for `Tab` in competition standings
- feat(ops): add migration to bulk mark finances as paid

### 2026-04-06 (1h 25m)
- feat(fetcher): improve timeout settings and error handling in CzBasketball fetcher
- chore: create laravel 13 upgrade baseline documentation
- chore: add laravel 13 execution plan and validation checklist
- chore: upgrade project to laravel 13 core and stabilize tests
- feat: restore custom language switcher in admin panel for visual consistency

### 2026-03-30 (30m)
- feat(fetcher): update timeout and retry settings, enhance database handling

### 2026-03-29 (1h 0m)
- feat(email-protection): restructure email links to enhance user privacy
- feat(fetcher): enhance czbasketball fetcher configuration flexibility

### 2026-03-28 (32m)
- feat(purifier): integrate HTMLPurifier for robust content sanitization
- feat(sync): add retry handling for query exceptions during delete operations

### 2026-03-27 (4h 6m)
- feat(diag): remove obsolete diagnostic scripts and update documentation
- feat(security-audit): add comprehensive documentation for security audit process
- feat(docs): enhance static findings documentation in security audit
- feat(schedule): refactor scheduler to use `call` with inline closures
- feat(security): enhance security measures for HTTP headers and DOM sanitization
- feat(security): refine admin authorization tests and UI match details
- feat(security): implement policies and refactor admin permissions

### 2026-03-26 (6h 25m)
- feat(ai): add AI indexing command and improve search service stability
- feat: replace "Kbely Falcons" with "Sokol Kbely" across project
- feat(screenshot): introduce remote screenshot service integration
- feat(auth): restrict feedback widget to authenticated users
- feat(screenshot): introduce global screenshot system for headless browsers
- feat(screenshot): secure and enhance screenshot mode with impersonation
- feat(locale): fix locale handling during cache priming and suppress Livewire error
- feat(screenshot): add support for local screenshot rendering via Browsershot
- feat(screenshot): refactor header preparation and URL modification logic
- feat(diag): add screenshot system diagnosis script
- feat(feedback): implement comprehensive feedback reporting system
- feat(feedback): remove FeedbackController and related logic
- feat(screenshot): add local wkhtmltoimage fallback for screenshots

### 2026-03-25 (3h 45m)
- feat(finance): rename and enhance command for archiving old charges
- feat(diagnostic): add production diagnostic script and enhance path handling
- feat(hero): add upcoming events section for active teams
- feat(blocks): remove unused public block templates
- feat(blocks): add new public block components and update layouts
- feat(recruitment): streamline recruitment form handling and add lead management
- feat(ui): remove obsolete Filament resources and enhance help page layout
- feat(member): add sticky detection and filters toggle on my statistics page
- feat(help): enhance metadata handling in HelpArticleSeeder
- feat(seo): enhance SEO metadata handling and improve layouts
- feat(locale): add bypass for caching and API requests in SetLocaleMiddleware
- feat(search): fix ViewException and improve UX for search results

### 2026-03-20 (1h 6m)
- feat(html): remove unused HTML template for "Muži - Přebor B"
- fix(member): improve payment widget layout and download functionality
- feat(html): add basketball match detail page template
- feat(stats): improve score formatting and handling across services

### 2026-03-19 (8h 38m)
- SAVEPOINT: Current state with JSON migration (causing performance issues)
- feat(help): implement new help system views and configuration
- feat(loader): refactor and relocate loader components
- feat(loader): clean up loader references and remove test route
- feat(deployment): remove outdated scripts and optimize production tasks
- feat(icons): refactor for dynamic icon handling and expand icon library
- feat(core): remove deprecated scripts and improve JSON migration handling
- feat(profiling): refine performance profiling and enhance caching
- feat(livewire): implement dynamic polling intervals and refactor console logic
- feat(console): refactor output handling and remove direct writes to properties
- feat(performance): optimize local development and enhance database migrations
- feat(ui): adjust spacing in attendance tracker layout
- feat(club-events): set default visibility of new events to private
- feat(club-competitions): enhance management and extend widget functionality
- feat(sync): enhance match synchronization logic and fresh seeding
- feat(stats): add duplicate matches cleanup command and improve merge logic
- feat(stats): optimize opponent matching and simplify metadata queries
- feat(ui): add HTML template for basketball match detail page
- feat(stats): improve JSON field handling and legacy DB compatibility
- feat(feedback): enhance feedback pipeline with Playwright improvements and fallbacks
- feat(feedback): add diagnostic command and enhance screenshot handling
- feat(feedback): add client-side screenshot strategy and enhance fallback logic

### 2026-03-17 (2h 17m)
- feat(stats): add career statistics and improve team summary calculations
- feat(stats): enhance career and personal stats handling
- feat(attendance): add real attendance tracking for events
- feat(finance): add tariff-based fines and prepaid event handling
- feat(core): update method signatures for navigation icon handling
- feat(translations): localize strings throughout admin and UI components
- feat: localize views and UI components with translation keys

### 2026-03-16 (12h 7m)
- feat(sync): add intelligent skipping and improve detailed sync reporting
- feat(sync): add progress bars to improve sync visualization
- feat(sync): enhance synchronization and robustness features
- feat(sync): improve sync process handling and UI enhancements
- feat(sync): improve progress bar handling and error resilience
- feat(help): enhance content filtering with role and section support
- feat(sync): enhance player synchronization and season data handling
- feat(stats): simplify player and team data rendering in `MyStatistics`
- feat(sync): improve match synchronization with season-specific checks
- feat(sync): enhance progress bar handling and fix conditional logic in views
- feat(feedback): enhance feedback handling and screenshot capabilities
- feat(feedback): improve feedback widget and handling logic
- feat(sync): enhance player sync and UI with mapping and search options
- feat(feedback): implement multi-layer screenshot pipeline with Playwright integration
- feat(feedback): enhance screenshot generation and feedback widget sanitization
- feat(feedback): optimize feedback widget and system cleanup logic
- feat(stats): add duplicate matches cleanup and prevention mechanisms
- feat(sync): improve player sync and season-specific data handling
- feat(sync): refactor season handling and add enhanced date validation
- feat(sync): improve player sync timeout handling and add memory optimizations
- feat(sync): add skip functionality and enhance duplicate handling
- feat(sync): improve match synchronization and table parsing
- feat(ui): add consistency column and standings filters
- feat(ui): remove `soutez_4999.html` obsolete file
- feat(stats): improve score parsing and handling of special characters
- feat(sync): enhance event detection and metadata merging
- feat(stats): improve handling of statistics deletion and aggregation

### 2026-03-15 (2h 2m)
- feat(stats): improve statistics view and backend calculations
- feat(finance): add command for archiving old charges and improve PaymentWidget
- feat(finance): add finance archive command to system console
- feat(sync): improve sync process and Feedback reporting with new features

### 2026-03-14 (4h 38m)
- feat(help): restructure help content and improve metadata handling
- feat(payment): enhance QR code display and localization updates
- feat(sync): implement detailed data synchronization for players and matches
- feat(telescope): add custom pruning and scheduling for Telescope entries
- fix(telescope): simplify condition for pruning check
- feat(sync): add support for excessive data synchronization and progress tracking
- feat(sync): add real-time sync status bar with progress animations
- feat(sync): enhance sync-status-bar security and visibility logic
- feat(sync): enhance sync-status-bar and system console functionality
- feat(sync): enhance synchronization logic, UI, and system console commands
- feat(sync): refactor SyncStatusBar to use Livewire component
- feat(sync): add cancellation functionality to imports, sync, and batches
- feat(stats): enhance impersonation access tests and improve stat calculations

### 2026-03-12 (1h 35m)
- feat(help): refine Help Center layout and role translations
- feat(help): add metadata handling and enhance navigation structure

### 2026-03-11 (9h 10m)
- feat(locale): improve locale handling and synchronization
- feat(cleanup): remove legacy branding and deprecated files
- feat(navigation): reorganize navigation groups and improve descriptions
- feat(docs): remove outdated documentation files
- feat(docs, season): add documentation for match prediction, stats extractors, and season renewal
- feat(docs): add help documentation and search functionality
- feat(docs, ui): enhance help center navigation and category handling
- feat(docs): add comprehensive Czech help documentation
- feat(docs): migrate help system from Blade to Markdown and database
- feat(memory): fix memory leak on `/admin/help` and optimize breadcrumbs logic
- feat(help): enhance translation handling and optimize category detail UI
- feat(help): improve help article and category handling
- feat(bootstrap): add pre-boot error detection and logging improvements
- feat(performance): refine and enhance profiling middleware
- feat(help): remove legacy Blade-based help system
- feat(help): implement new help center with enhanced functionality
- feat(help): revamp help center with subcategories and contact shortcuts
- feat(help): remove debug panel for test user

### 2026-03-10 (8h 46m)
- feat(matches, ui): enhance match prediction display with dynamic styling
- feat(auth): update 2FA timeout and logout behavior
- feat(photo-pools): enhance file upload tracking and form actions
- feat(console): add legacy photo import command
- feat(console): improve legacy photo import logic and CLI behavior
- feat(app): enhance environment detection and asset import stability
- feat(console): enhance performance diagnostics for branding settings
- feat(performance): optimize settings retrieval and error handling
- feat(branding): improve settings caching and retrieval logic
- feat(branding): remove redundant branding logic and cleanup
- feat(ui, attendance): add bulk attendance actions with improved notifications
- feat(console): improve legacy photo import and cleanup logic
- feat(config): enhance public path handling for production environments
- feat(bootstrap): comment out unused booting logic in app configuration

### 2026-03-09 (9h 4m)
- feat(match-detail, i18n): add fallback messages and improve layout
- feat(feedback-widget, layout): update design and improve resilience
- feat(feedback-widget, assets): improve resilience and update assets
- feat(feedback-widget): implement full redesign and tracking enhancements
- feat(sync-services): enhance handling of player profiles and app synchronization
- feat(feedback-widget): enhance initialization and environment handling
- feat(predictions, elo): implement Elo service and enhance prediction mechanics
- feat(zapas-analysis): add HTML structure for Czech basketball match analysis
- feat(match-details): add last and mutual matches display to match prediction modal
- feat(prediction, stats): enhance predictions with mutual match history and fallback logic
- feat(predictions, stats): enhance metadata syncing and prediction UI
- feat(stats): integrate external player stats and match history from cz.basketball
- feat(stats): improve date parsing and time zone handling for Czech basketball matches
- feat(predictions): introduce mutual match analysis and enhance prediction logic
- feat(attendance): add bulk RSVP actions and improve attendance management
- feat(ui): integrate Floating UI and enhance dropdown behavior
- feat(lang,ui): improve localization and dynamic UI interactions
- feat(feedback, deploy): add admin/coach contact forms and assets deploy command
- feat(deploy, ui): enhance asset deployment and UI behaviors
- feat(ui, matches): enhance match detail page with dynamic features and styling
- feat(events): add public events pages with filters and dynamic views
- feat(nav, lang): add "Program" section and navigation updates
- feat(events): enhance events localization and dynamic UI behavior
- feat(auth): enhance redirection logic and two-factor handling
- feat(events): improve RSVP logic with time-based condition
- feat(ui, localization): enhance match detail page with dynamic quotes and localization

### 2026-03-08 (16h 2m)
- feat(documentation): add internal documentation system to admin panel
- feat(docs): add module for internal documentation in admin panel
- feat(search, docs): extend AI search and improve documentation indexing
- feat(docs, ui): enhance documentation navigation and icon rendering
- feat(docs): remove outdated documentation files
- feat(docs): remove legacy and irrelevant documentation files
- feat(errors): add shot clock violation error view and handling
- feat(sync): improve SSH handling and error feedback in app:sync
- feat(feedback): implement feedback reporting system
- feat(feedback): implement comprehensive feedback management system
- feat(feedback): add advanced debug options and middleware for feedback system
- feat(emails): reorganize email templates and enhance branding
- feat(emails): add email design system and preview functionality
- feat(feedback): refactor feedback widget and allow testing environment
- feat(emails, feedback): update branding and enhance feedback system
- feat(auth, logging): enhance 2FA middleware and logging system
- feat(assets): add hero and recruitment images
- feat(feedback): improve widget injection and add dynamic loader
- feat(feedback, ui): enhance feedback widget logic and UI interactions
- feat(statistics, ui): enhance team and season handling, add UI improvements
- feat(database): add `team_name_in_source` to season configs
- feat(feedback, ui): enhance feedback widget transitions and update script injection
- feat(forms): update components namespace to use `Filament\Schemas`
- feat(feedback, ui): add view feedback report functionality
- feat(opponent-merge, ui): improve opponent merge actions and UI components
- feat(import-runs, ui): add view page and enhance logs schema
- feat(debug-operations, ui): enhance season handling and add bulk actions
- feat(import-runs, ui): simplify resource configuration and actions
- feat(stats:recompute): enhance flexibility for team and season selection
- feat(sync, debug-operations): add stop flag for synchronizations
- feat(debug-operations): restructure and enhance sync and recompute actions
- feat(jobs, sync): improve job handling and add retries for stability
- feat(stats, ui): add matches view and cron logs table widget
- feat(stats): add team view to MyStatistics component
- feat(stats, ui): enhance logging and UI interactions in MyStatistics
- feat(feedback-widget): refine data attributes and update tests
- feat(sync, logging): enhance synchronization workflows and add detailed logging
- feat(debug-operations): add clear team errors action
- feat(debug-operations): add season selection to sync and recompute actions
- feat(debug-operations): improve unmatched players query and boxscore set handling
- feat(debug-operations): enhance season context and unmatched players UI
- feat(external-mappings): add info widget for unmatched players guidance
- feat(sync, ui): improve player mapping and add bulk actions
- feat(user-management, ui): add user merge functionality and improve duplicate handling
- feat(user-management): add ghost user merging functionality
- feat(users-table, user-merge): enhance duplicate detection and user actions
- feat(users-table, user-merge): add ghost profile bulk merge and enhance duplicate detection
- feat(searchable-columns, ghost-handling): enhance searchable columns and refine ghost user detection
- feat(user-merge, tests): add unit tests for `UserMergeService` and fix relationship conflicts
- fix(user-merge): refine coding style and optimize profile merge logic
- feat(sync, extractors): enhance player profile sync and metadata extraction
- feat(sync, config): enhance match synchronization and adjust sync limits
- feat(sync): enhance match and opponent synchronization logic
- feat(sync): implement match cleanup for duplicate handling and fix related issues
- feat(sync, ui): refine match status handling and improve match card UI
- feat(matches): add `finished` status and update related logic
- feat(sync, ui): improve match status handling and UI logic
- feat(matches): unify match statuses and remove deprecated ones
- feat(match-details, sync): implement match detail view and player synchronization
- feat(users-table, sync): add player sync actions and improve photo handling
- feat(sync, players): implement deep player sync job and enhance sync logic
- feat(debug-tools): refine player sync query in debug operations
- feat(statistics, match-detail): enhance boxscore display and parsing logic
- feat(tests): remove large match detail fixture for CZ Basketball
- feat(statistics): add opponent support in statistic rows
- feat(tests): add large match detail fixture for CZ Basketball
- feat(matches, statistics): update period handling and translations
- feat(statistics, boxscore): enhance boxscore parsing and opponent display
- feat(statistics, match-detail): add team comparison and enhance best players display
- feat(tests): remove large match fixture for CZ Basketball
- feat(statistics, sorting): add dynamic sorting to match data tables
- feat(statistics, match-detail): improve layout and styling for match details
- feat(statistics, match-detail): further refine layout and responsiveness
- feat(matches, design): introduce victory and loss messages, advanced styling updates
- feat(statistics, match-detail): improve layout, responsiveness, and team logo handling
- feat(statistics, match-detail): enhance layout and visual hierarchy
- feat(match-details, design): improve layout, translations, and past match handling

### 2026-03-07 (46m)
- feat(recruitment): localize recruitment form and improve translations
- feat(ui, branding): restructure team logo display and improve icon styles
- feat(ui): enhance hover effects and opacity transitions in recruitment section
- feat(localization): standardize non-breaking spaces in Czech translations

### 2026-03-06 (4h 39m)
- feat(sync): enhance link handling and logging in ExternalStatsSyncService
- feat(partners, branding): add partner management and team branding settings
- feat(partners): implement partner management system and frontend integration
- feat(branding, partners): improve settings fallback and icon validation
- feat(partners): update column types for translations in partners table
- feat(partners): add partner logo upload and WebP conversion
- feat(ui, partners): improve image styling and update partner data
- feat(branding, ui): add team logo fields and enhance UI image effects
- feat(ui, middleware): enhance logo fallbacks and add new middleware alias
- feat(settings, ui): enhance settings logic and improve footer layout

### 2026-03-05 (9h 32m)
- feat(docs): add production rollout process and reporting documentation
- feat(seeder): add new cron tasks for team stats sync and cleanup
- feat(database): update `metadata` column type in migration files
- feat(external-sync): add support for external stat sources and mappings
- feat(external-stat-sources): enhance external stat sources and mappings management
- feat(deployment): remove deprecated Envoy tasks
- feat(matches): refactor "games" terminology to "matches"
- feat(forms): set default active season in forms and simplify team selection
- feat(events): improve event type handling and migration
- feat(pages): add `canAccess` permission check for advanced settings pages
- feat(metadata): replace JSON accessors with `LIKE` queries for better compatibility
- feat(seasons): add normalization and deduplication for seasons
- feat(logging): implement ConsoleService and enhance logging in sync processes
- feat(logging): improve diagnostics and logging for scheduler and proxy
- feat(team): simplify active players query and enhance avatar modal functionality
- feat(stats): refactor query naming and integrate TeamStatsService in MyStatistics
- feat(sync): introduce "fresh" mode and enhance logging in sync processes
- feat(logging): add external import logs and enhance debug operations UI
- feat(debug-operations): add form toggles and enhance sync functionality
- feat(jobs): add `DiscoverSeasonsJob` for season discovery
- feat(logging): enhance SeasonDiscoveryService and diagnostic logs
- chore: remove unused HTML file
- feat(tests): add feature tests for sync and last_synced_at updates
- feat(stats): enhance stats import command and scheduling
- feat(sync): enhance match and opponent detection logic, bulk actions
- feat(merge-suggestions): introduce opponent merge suggestions and duplicate detection
- feat(opponents): add OpponentResource import in ListOpponents
- feat(widgets): add OpponentMergeSuggestionsWidget to OpponentResource
- feat(sync, ai): add AI-based sync mode and improve sync options
- feat(sync): add AI fallback for older matches and missing stats
- feat(sync): fix namespace for Section component in DebugOperations
- feat(sync, search): improve searchable queries and add external mapping support
- feat(sync, ui): enhance sync logic and improve UI components
- feat(sync): improve metadata handling and query logic in sync services
- feat(filament): update namespaces for Section and Actions components
- feat(filament): set key-value fields and inputs to read-only across forms
- feat(sync, ai): enhance logging and OpenAI configuration in sync services
- feat(env): reduce OpenAI timeout in example configuration
- feat(svg, table): update SVG paths and improve table headers
- feat(svg, content): remove unused SVG paths and update headers
- feat(filament): enhance copy action and feedback in ExternalImportRunForm
- feat(sync, debug): improve error debugging and metadata logging in sync services
- feat(sync, ui): enhance metadata logging, HTML sanitization, and error debugging
- feat(sync, ai): introduce AI clipping pipeline for CZ.BASKETBALL sync
- feat(extractors): add DOM extractors for CZ.BASKETBALL stats processing
- feat(sync, ai): implement CNH pipeline and AI context links for CZ.BASKETBALL
- feat(links, clippers): improve link processing and URL handling for CZ.BASKETBALL

### 2026-03-03 (2h 43m)
- feat(docs): add extensive documentation on external imports and synchronization
- feat(stats & seo): enhance data processing, SEO, and scheduler functionality
- feat(stats): add player and team stats services for enhanced data processing
- feat(stats-visuals): add player and team statistics visualizations
- feat(imports): enhance external import robustness and monitoring
- feat(imports): add legacy stats import module for historical data processing
- feat(admin): add Debug & Operations Panel for system monitoring
- feat(cli): add Artisan commands for external stats management
- feat(stats-ux): add detailed documentation and enhance stats visualizations
- feat(stats-automation): add season discovery and backfill module
- feat(qa): add QA Master Plan and initial test coverage
- feat(qa): add QA tools, reports, and smoke test command
- feat(imports): enhance legacy stats import with encoding detection and multi-table support
- feat(config): add support for user seeding toggle
- feat(sync): enhance external stats sync and legacy imports

### 2026-03-02 (8h 25m)
- feat(error reporting): implement error mail deduplication and throttling
- feat(ui): enhance session status messages with transitions and close buttons
- feat(ui): introduce reusable auth alert component
- feat(performance): optimize app performance and middleware logging
- feat(auth): remove unused register page and improve auth-related features
- feat(performance): improve logging and diagnostic headers
- feat(middleware & localization): enhance profiling middleware and extend translations
- feat(ui): add custom 500 error page with detailed diagnostics
- feat(localization & team resources): update translations and admin configurations
- feat(docs & deployment): introduce dashboard nudges documentation and Envoy deployment script
- feat(ui): redesign dashboard nudge components
- feat(avatar): improve avatar handling and dashboard nudges responsiveness
- feat(branding): separate web footer and admin contact settings
- feat(seeder & avatars): update team references and improve avatar duplication handling
- feat(avatars): update avatar processing dimensions and defaults
- feat(avatars): add new default avatars and thumbnails
- feat(teams & branding): update team naming conventions and improve related assets
- feat(avatars): fix PayloadTooLargeException for mobile avatar uploads
- feat(seo & branding): enhance metadata handling and footer branding integration

### 2026-03-01 (5h 15m)
- feat(loader, photo pools): enhance loader behavior and update query logic
- feat(seeders): update image URLs to .webp format for team recruitment
- feat(sync): replace legacy user migration and enhance sync commands
- Merge remote-tracking branch 'origin/main'
- feat(member attendance): enhance attendance UI for improved user experience
- feat(envoy): improve Node.js and npm version detection logic
- feat(binary): add BinaryHelper for managing PHP, Node.js, and npm binaries
- feat(app, UI): improve configuration handling and enhance UI responsiveness
- feat(notifications): implement dynamic notification dropdown and external public path support
- feat(notifications): refactor notification system for better flexibility and channel handling
- feat(notifications): refactor event listeners and add notification redirect handling
- feat(notifications): improve localization and add database indexes
- feat(performance): optimize queries, caching, and attendances management
- feat(member attendance): improve UX with dynamic scrolling and focus management
- feat(member statistics): add statistics management and navigation
- feat(member team switcher): add team selector component and improve contextual filtering
- feat: update team references and implement global loader
- feat(member team selector): improve active team context and UI enhancements
- feat(loader & localization): enhance global loader and loading messages
- feat(member attendance): add advanced filtering and improve UI

### 2026-02-28 (9h 52m)
- feat(avatars, ui): introduce avatar management and app-level sync commands
- feat(commands, styles): remove unused commands and refine design tokens
- feat(impersonation, roles): add user impersonation and advanced role management
- fix(language-switch): resolve issue with `LanguageSwitchServiceProvider` booting
- feat(impersonation, photo uploads): enhance impersonation UX and streamline photo upload process
- feat(media manager, uploads): improve media relation handling and upload structure
- feat(photo uploads): optimize bulk photo upload process
- feat(performance, payments): implement caching strategies and payment widget
- feat(payment widget): enhance PaymentWidget functionality and integrate dynamic features
- feat(avatars, docs): add sync command documentation and import default avatars
- feat(avatars, sync commands): enhance avatar gallery management and synchronization
- feat(system-console, search): expand system console features and enhance search functionality
- feat(sync commands): remove default avatar sync and switch to FTP-based solution
- feat(ai indexing, search): enhance indexing logic and refine search capabilities
- feat(commands, branding): add finance cleanup and seeder enhancements
- feat(system-console): improve flags support and error handling
- feat(performance): optimize caching and reduce database queries
- feat(logging): remove excessive logging from middleware and responses
- feat(user identifiers): prevent manual overwriting and improve handling logic
- feat(photo import, database): implement batch photo import with enhanced processing and performance fixes
- feat(galleries, caching): improve date formatting, cache handling, and URL generation
- feat(livewire, photo upload): fix batch upload timeout and improve user feedback
- feat(photo pools, import): enhance UI, improve cache handling, and optimize batch processing
- feat(seeders, email): enhance global seeder and improve email obfuscation
- feat(style, logic): fix spacing inconsistencies and enhance loader design
- feat(users, UI): add indices for performance and enhance loader design
- feat(import, database): enhance photo import UI and add new DB features
- feat(photo import): enhance cancellation flow and improve queue management

### 2026-02-27 (7h 24m)
- feat(dashboard, user-security): enhance dashboard usability and security updates
- feat(migrations, models, seeders): introduce fine templates and trophies
- feat(news, back-to-top): improve news listing and add back-to-top button
- feat(matches, SEO, performance): enhance match data, filters, SEO, and performance tools
- feat(performance, media, UI): enhance caching, media conversions, and admin features
- feat(performance): introduce full-page caching and performance enhancements
- feat(matches): add multi-team support and improve match display logic
- feat(performance, migrations): add performance testing and not found logging setup
- feat(performance): enhance profiling and scenario configuration
- feat(logging): add NotFoundLoggerMiddleware and enhance NotFoundLog model
- feat(logging, admin): introduce NotFoundLogs resource with CRUD and UI integration
- feat(performance): add PerformanceComparisonWidget and NOT_FOUND icon
- feat(logging, performance, admin): enhance 404 detection and performance comparison
- feat(audit-logs, icons, translations): refactor audit logs and enhance UI with translations
- feat(language-switch, audit-logs): enhance UI and localization consistency
- feat(navigation, matches): reorganize navigation and fix match display logic
- feat(matches): enhance match display logic and admin status handling
- feat(ui, icons): fix FOUC issues and enhance icon stability
- feat(trainings): calculate total expected count for training sessions
- feat(migrations, docs): ensure JSON compatibility for production environment
- feat(migrations, filtering, progress): introduce pivot tables and enhance team-related filtering
- feat(migrations, ui): enhance migration checks and refactor loader component usage
- feat(seo, forms): add support for SEO metadata in photo pools
- feat(filesystems, uploads): migrate uploads to public path with new disk configuration
- feat(upload handling): standardize file upload paths and configurations

### 2026-02-26 (5h 57m)
- feat(photo-pools, search, migrations): enhance photo pool features, AI search, and database schema
- feat(photo-pools, media, ai): enhance photo pool forms, media handling, and AI integration
- feat(controller, migrations, media): fix authorization, improve AI schema, and add media path generation
- feat(photo-pools, admin-console): improve photo uploads and refine system console commands
- feat(photo-pools, galleries): add team-specific filters and enhance views
- feat(galleries): integrate Spotlight.js for improved gallery viewing
- feat(teams): add coaches and players relation managers, seeders, and frontend updates
- feat(phone-validation, teams): add phone validation and update team-related data
- feat(recruitment-form): add recruitment form with email notifications and reCAPTCHA

### 2026-02-25 (8h 39m)
- feat(docs): add error reporting guidelines for Laravel application
- feat(deploy): enhance production commands with step summary and cleanup .env.example
- feat(homepage): optimize performance, routing, and accessibility
- feat(images): add WebP support with automatic fallback and lazy loading
- feat(sync): enhance production sync with advanced .env handling and validation
- feat(images): implement reusable `<x-picture>` component for optimized image handling
- feat: add recruitment and email protection features
- feat: implement contact form with reCAPTCHA and error page enhancements
- feat(command): enhance `app:seed` with new options and frontend-specific seeding
- chore: remove unnecessary whitespace in code
- feat(homepage): add localized home page blocks and optimize hero video loading
- feat(error-reporting, photo-pools, email-debug): implement error handling and photo pool management features

### 2026-02-24 (2h 24m)
- feat(ui): redesign Filament system console and admin theme integration
- feat(docs): add documentation for loaders, dashboards, and AI features
- feat(i18n): localize member and team views with translation keys
- feat(member): add feedback module for contacting admins and coaches
- feat(ui): improve responsiveness and UI components for member and admin sections
- feat(ui): improve widget responsiveness and search UI adjustments
- feat(ui): enhance welcome and contact widgets on admin dashboard

### 2026-02-23 (5h 14m)
- feat(ui): enhance typography and update responsive utilities
- feat(i18n): update language switcher and locale handling
- feat(ui): introduce localized line-height for improved readability
- feat(i18n): enhance localization and typography in member section
- feat(seeder): update CMS content localization and typography
- feat(ui): update styles and typography in cards and seeder content
- feat(search): add tests and enhance localization in search functionality
- feat(ui): enhance breadcrumbs and integrate across templates
- feat(cli): add production deployment and setup commands
- feat(cli): enhance production setup and deployment commands
- feat(cli): add `app:sync` command for fast production updates
- feat(cli): enhance production deployment setup and asset building
- feat(seeder): add data seeders and documentation for streamlined setup
- feat(docs): add guidelines for idempotent migrations
- feat(migrations): ensure idempotency for tables and columns in migrations
- feat(migrations): remove conditional checks for column existence
- feat(migrations): replace `json` columns with `longText` for compatibility
- feat(docs): update Node.js requirements and deployment steps
- feat(cli): standardize Node.js and NPM usage in deployment scripts
- feat(cli): enhance production setup with auto-discovery and validation
- feat(docs, cli): improve deployment workflow and troubleshooting
- feat(cli): add `app:local:prepare` command for streamlined local setup
- feat(deployment): add support for custom public path in deployment
- fix(bootstrap): resolve compatibility issue with usePublicPath in app configuration
- feat(deployment): improve cache handling and robust path patching
- feat(auth): enhance authentication redirects and UI consistency

### 2026-02-22 (10h 50m)
- feat(auth): update auth views for improved consistency and branding
- feat(auth): add custom branding to login view
- feat(auth): enhance login header and animations
- feat(auth): refactor auth views and improve layout consistency
- feat(auth): add debug indicators to auth views and Filament hooks
- fix(auth): remove debug headers from auth views
- fix(auth): improve login form styles and remove debug hook
- fix(auth): update reset-password view input styles for accessibility
- feat(auth): unify visual design with Glassmorphism
- feat(auth): implement custom auth views with unified design
- fix(auth): replace `getFormContentComponent` with `content` in auth views
- feat(auth): refactor auth views and enhance layout consistency
- fix(branding): improve resilience of `getDbSettings` method
- feat(auth): enhance branding and update forgot-password flow
- feat(auth): implement custom auth layout for all views
- feat(auth): remove unused registration flow and refine auth text
- feat(auth): enhance auth layout and improve responsiveness
- feat(auth): improve form button styling for larger screens
- feat(auth): enhance login UI with microinteractions and updated branding
- feat(docs): add guide for rendering static HTML snapshots
- feat(docs): expand guidelines with custom auth UI rules for Tailwind v4
- feat(auth): enhance auth layout with updated visuals
- feat(auth): refine auth layout and improve responsiveness
- feat(auth): enhance auth views with localized headings, subheadings, and icons
- feat(css): prevent duplicate icons in error messages
- feat(auth): improve validation, password strength, and auth layout
- feat(auth): add rate-limiting notifications and refine messaging
- feat(auth): add custom password and email notifications
- feat(forms): migrate Filament components to new namespace
- feat(database): add new fields, enums, and relation managers for users and player profiles
- feat(actions): update namespace for Filament actions and components
- feat(auth): implement 2FA timeout and "remember device" functionality
- feat(auth): refine 2FA timeout and admin redirection logic
- feat(auth): improve admin redirection logic in 2FA flow
- feat(navigation): implement search, breadcrumbs, and hierarchical navigation
- feat(admin-navigation): localize resource labels and groups in admin panel
- feat(admin-navigation): add navigation sorting and groups to resources
- feat(audit-log): implement audit log system for tracking system events and changes
- feat(audit): integrate advanced audit logging and AI-powered search
- feat(media): integrate media library support and improve file handling
- feat(media): implement custom path and URL generators for media files
- feat(admin-icons): replace default icons with Font Awesome 7 Light icons in admin panel
- feat(admin-icons): update navigation icons to use new `fal_*` aliases
- feat(admin-icons): fix `fal_*` icon aliases and register missing icons
- feat(icons): unify icon management and enhance usage across admin resources
- feat(leads): implement lead management and contact/recruitment forms
- feat(icons): refactor icon management and enhance consistency
- feat(icons): refactor icon management and add enhanced tools
- feat(content): enhance data handling and improve Blade templates
- feat(ui): redesign block components for dynamic styling and improved UX
- feat(ui): integrate AOS animations and improve hero block styling
- feat(ui): enhance hero block media handling and add assets
- feat(ui): redesign homepage blocks and enhance content handling
- feat(ui): redesign footer with modern layout, dynamic menus, and localization
- feat(ui): improve footer layout and translations
- feat(ui): update footer layout and translations
- feat(ui): add light style to CTA block
- feat(ui): enhance block styles and improve responsiveness
- feat(ui): enhance tertiary CTA styles in hero block
- feat(ui): refine styles for CTA, hero, and footer blocks

### 2026-02-21 (8h 39m)
- Initial commit: Laravel 12, Filament 5, localization, guidelines and docs
- Update docs and Envoy with new GitHub repository URL
- Configure environment variables, update .env.example and documentation
- Update PHP version to 8.4
- Aktualizace README.md pro projekt Kbelští sokoli
- Initialize project structure, authentication, roles, and permissions system with customized layouts and routing. Add database migrations, seeders, and documentation.
- feat: add CMS backend structure for posts, pages, menus, and SEO
- feat: add `composer.lock` to lock dependencies
- feat: add directory setup in lint workflow
- feat: add form requests for CMS validation
- feat: add dynamic page blocks system
- feat: enhance form schemas and block registry in CMS
- feat: add resources for basketball matches, seasons, events, and opponents
- feat: refactor events and add club event management with RSVP
- feat: add error pages, migrations, middleware, policies, and KPIs
- feat: introduce branding system with dynamic theming support
- feat: add dynamic Blade components for public frontend
- feat: add maintenance mode with custom under-construction page
- feat: integrate Laravel Telescope for debugging and monitoring
- feat(public): redesign and enhance under-construction page
- feat(branding): implement dynamic branding placeholders and admin bypass for maintenance mode
- feat(member): add economy, teams, and attendance management modules
- feat(media): implement galleries and media management system
- feat(announcements): add announcements and notifications system
- feat(cron): add cron management and task automation system
- feat(finance): add financial management system
- feat(public): improve responsiveness and styling for under-construction page
- feat(auth): enhance authentication UX and add security features
- feat(fontawesome): integrate Font Awesome Pro with enhanced styles
- feat(auth, public): enhance visuals and animations for auth and under-construction pages
- feat(middleware): add HTML minification middleware
- feat(middleware): enhance HTML minification and usage in admin panel
- feat(middleware, auth): improve HTML minification and update auth page design
- feat(ui, loading): integrate NProgress and Spin.js for enhanced UX
- feat(fontawesome): switch to local Font Awesome Pro assets
- feat(fontawesome): add local Font Awesome Pro assets
- feat(lang, ui): add localization files and enhance auth page styling
- feat(i18n, db): implement multilingual database support and enhance localization
- feat(docs): remove outdated administration and architecture documentation
- feat(docs, middleware): update documentation and middleware for role-based access control
- feat(docs): add documentation for sports module and statistics system
- feat(docs): add documentation for media, notifications, and automation systems
- feat(docs): update public frontend and CMS documentation
- feat(docs): restructure project introduction and table of contents
- feat(docs): remove unused `.output.txt` architecture documentation
- feat(seo): enhance SEO metadata management and add localization support
- feat(auth, middleware): enhance login response and 2FA validation logic
- feat(auth): add 2FA setup and password confirmation views
- feat(users): improve 2FA management and table filters
- feat(users): enhance 2FA filters and add bulk reset action
- feat(auth): enhance 2FA setup and improve admin route security
- feat(auth): simplify authentication views by removing floating elements
- feat(auth): enhance styles and improve 2FA setup help section
