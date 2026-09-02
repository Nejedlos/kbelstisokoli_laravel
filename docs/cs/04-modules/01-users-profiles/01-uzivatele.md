# Správa uživatelů a profilů

Tento modul zajišťuje správu uživatelských účtů (přihlášení, role) a hráčských profilů (sportovní údaje).

## 1. Oddělení User vs PlayerProfile
- **User:** Primární entita pro přihlášení a autorizaci (Fortify + Spatie Permission).
- **PlayerProfile:** Volitelný 1:1 profil navázaný na User. Slouží k evidenci sportovních údajů hráče.
- **Rozšiřitelnost:** Lze přidat další typy profilů (např. CoachProfile, GuardianProfile) bez zásahu do jádra.

## 2. Datový model
- **users:** Rozšířeno o `is_active`, `phone`, `last_login_at`, `admin_note`, `club_member_id` a `payment_vs`.
- **Typy členství:** `membership_types` je vícenásobný JSON seznam uložený v `LONGTEXT`. Původní `membership_type` zůstává jako kompatibilní hlavní hodnota pro starší integrace.
- **Automatické generování ID:** Při prvním přihlášení uživatele (pokud chybí) se automaticky vygeneruje:
    - **ID člena (`club_member_id`):** Formát `KS-RRXXXX` (např. KS-261234).
    - **Variabilní symbol (`payment_vs`):** Formát `RRMMXXXX` (např. 26021234).
    - Generování zajišťuje `ClubIdentifierService` volaný v `LoginResponse`. Obě ID jsou v databázi unikátní.
- **player_profiles:** `user_id` (unique), `jersey_number`, `position`, `public_bio`, `private_note`, `is_active`, `metadata` (JSON).
- **player_profile_team (pivot):** `player_profile_id`, `team_id`, `role_in_team`, `is_primary_team`, `active_from`, `active_to`.

## 3. Administrace (Filament)
- **UserResource:** Správa jména, e‑mailu, telefonu, rolí a aktivity. Obsahuje `PlayerProfileRelationManager`.
- **PlayerProfileResource:** Správa sportovních údajů, dresu, pozice a přiřazení k týmům.
- **Role & Permissions:** Read-only přehledy v administraci. Úpravy rolí jsou povoleny pouze pro `super-admin`.
- **Pozvánky:** Administrátor může poslat uživateli pozvánku do členské sekce pomocí akce „Poslat pozvánku“ na stránce úpravy uživatele. Tato akce vygeneruje bezpečný odkaz pro nastavení hesla a odešle jej na e-mail uživatele v graficky zpracované šabloně s basketbalovou tématikou.

### Typy členství a systémové role

- Jeden uživatel může mít více typů členství, například **Hráč + Trenér**.
- Typy `player`, `coach` a `parent` automaticky synchronizují stejnojmenné systémové role.
- Synchronizace probíhá při vytvoření účtu nebo změně typů členství. Přihlášení, změna hesla a nastavení 2FA nemění přidělené role, včetně rolí starších účtů bez vyplněného členství.
- Odebrání typu členství odebere pouze příslušnou automaticky řízenou roli.
- Ručně přidělené role `admin`, `super_admin`, `editor` a případné další privilegované role synchronizace nikdy nemaže.
- Typy `staff`, `fan` a `honorary` nemají vlastní přístupovou roli a samy o sobě neodemykají členskou sekci.
- Centrální logika je v `App\Services\Users\MembershipRoleSynchronizer`; formuláře ani importy nemají mapování rolí duplikovat.

## 4. Autorizace a bezpečnost
- **Policies:** Všechny operace chráněny oprávněním `manage_users`. Hráči vidí vlastní profil.
- **Deaktivace:** Fortify login hook a middleware `active` brání přístupu deaktivovaným uživatelům.
- **2FA:** Všichni uživatelé s přístupem do administrace musí dokončit dvoufázové ověření před pokračováním, a to i do členské sekce. Pro ostatní členy je 2FA dobrovolné.

## 5. První Administrátor
První superadmin uživatel byl vytvořen s údaji:
- **Email:** nejedlymi@gmail.com
- **Role:** `admin` (superadmin)
- **Stav:** `is_active = true`
