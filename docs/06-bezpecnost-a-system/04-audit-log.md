# 21. Audit Log / Activity Log

Modul Audit Log slouží k zaznamenávání důležitých změn a akcí uživatelů v systému. Pomáhá při debugování, zajišťuje bezpečnost (sledování přihlášení) a poskytuje přehled o provozu klubu.

## Technická architektura

Systém je postaven na vlastním modelu `AuditLog` a centrální službě `AuditLogService`.

### Datový model (`AuditLog`)

Tabulka `audit_logs` obsahuje následující klíčová pole:

- `occurred_at`: Přesný čas události.
- `category`: Kategorie události (`auth`, `admin_crud`, `system`, `settings`, `content`).
- `event_key`: Unikátní identifikátor typu události (např. `auth.login_success`, `page.updated`).
- `action`: Provedená akce (`created`, `updated`, `deleted`, `login`, atd.).
- `severity`: Závažnost (`info`, `warning`, `critical`).
- `actor`: Polymorfní vazba na uživatele, který akci provedl (včetně `actor_type`).
- `subject`: Polymorfní vazba na entitu, které se akce týká (např. konkrétní `Page`).
- `subject_label`: Lidsky čitelný název entity v době zápisu.
- `context`: IP adresa (anonymizovaná), User Agent, URL, název cesty a `request_id` (pro korelaci v logách).
- `changes`: JSON diff změn (původní vs. nová hodnota).
- `metadata`: JSON pole pro doplňující kontextové informace.

## Centrální služba `AuditLogService`

Služba poskytuje API pro logování událostí odkudkoliv v aplikaci.

```php
// Základní logování
app(AuditLogService::class)->log(
    eventKey: 'custom.event',
    category: 'system',
    action: 'execute',
    subject: $model,
    metadata: ['foo' => 'bar']
);

// Helper pro CRUD
app(AuditLogService::class)->crud($model, 'updated', $changes);

// Helper pro bezpečnost
app(AuditLogService::class)->security('login_failed', 'login_attempt', ['email' => '...'], 'warning');
```

## Automatické logování (Trait `Auditable`)

Pro modely, u kterých chceme automaticky logovat CRUD operace (vytvoření, úprava, smazání), stačí použít trait `App\Traits\Auditable`.

Tento trait automaticky:
- Zaznamená `created`, `updated` a `deleted` události.
- U `updated` vytvoří diff změn (včetně starých a nových hodnot).
- **Respektuje soukromí:** Automaticky odfiltrovává citlivá pole jako `password`, `two_factor_secret`, `remember_token`.

## Bezpečnost a soukromí (Privacy-safe design)

1. **Anonymizace IP:** IP adresy jsou ukládány v anonymizované podobě (např. `192.168.1.0`). Pro unikátní identifikaci bez úniku osobních údajů se používá `ip_hash`.
2. **Citlivá data:** Do logů se nikdy neukládají hesla, 2FA tajné klíče, recovery kódy ani reset tokeny.
3. **Předmět logu:** Ukládá se `subject_label`, aby byl log čitelný i po smazání subjektu.

## Administrace (Filament)
    
Administrátoři mají k dispozici přehled v sekci **Admin nástroje > Audit Log**.
    
- **Umístění:** Modul je součástí sjednocených administrativních nástrojů na konci menu.
- **Ikona:** <i class="fa-light fa-clipboard-list"></i> (Font Awesome Light).
- **Přehled:** Tabulka s možností fulltextového vyhledávání a řazením.
- **Filtry:** Filtrování podle kategorie, závažnosti, zdroje (web/admin/console) a časového rozmezí.
- **Detail:** Zobrazení všech metadat a detailního diffu změn v čitelném JSON formátu.

## Retence logů

Pro čištění starých logů je připraven příkaz:

```bash
php artisan audit:cleanup --days=90
```

Tento příkaz by měl být naplánován v scheduleru pro automatické promazávání (výchozí retence je 90 dní).

## Implementované integrační body

- **Auth:** Login success/fail, logout, password reset, 2FA zapnutí/vypnutí.
- **CRUD:** Modely `Page`, `Post`, `User`, `Setting`, `ClubEvent`, `BasketballMatch`, `PageBlock`.
- **Systém:** Importy statistik (`StatsImportJob`).
