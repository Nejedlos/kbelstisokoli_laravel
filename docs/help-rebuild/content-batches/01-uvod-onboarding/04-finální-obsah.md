# Finální obsah: Batch 01 - Úvod a Onboarding

Tento dokument shrnuje finální podobu nápovědy pro první dávku (Onboarding), která byla implementována do systému.

## Seznam vytvořených článků

1.  **Vstup do kabiny (Přihlášení)** - `vstup-do-kabiny`
2.  **Zapomenutá nahrávka (Obnova hesla)** - `zapomenute-heslo`
3.  **Můj profil (Členská sekce)** - `muj-profil`
4.  **Neprůstřelná obrana (Zabezpečení a 2FA)** - `zabezpeceni-uctu`
5.  **Sokoli v mobilu (PWA aplikace)** - `sokoli-v-mobilu`
6.  **Kdo je kdo (Role v systému)** - `role-v-systemu`
7.  **První kroky: Často kladené otázky** - `onboarding-faq`

## Z čeho se vycházelo
- **UI Audit:** Průzkum reálných obrazovek `/admin/login`, `/member/profile`, `/member/2fa/setup`.
- **Terminologie:** Využití basketbalových metafor ("Kabina", "Nahrávka", "Obrana"), které jsou v systému již přítomny v překladech.
- **Technická realita:** Vypnutá registrace (řešeno adminem), vynucené 2FA pro adminy, readonly pole v profilu (data z matriky).

## Co je 100% potvrzené
- **Routy:** Všechny odkazy v Quick Actions odpovídají reálným cestám v `routes/*.php`.
- **Zabezpečení:** Postup nastavení 2FA odpovídá implementaci přes Laravel Fortify v členské sekci.
- **PWA:** Systém má platný `manifest.json`, postup instalace na plochu je tedy relevantní.

## Co je dobré ručně zkontrolovat
- **Vizuální konzistence:** Zda ikony Font Awesome 7 Light (`fa-light`) v nápovědě ladí s ikonami v hlavním menu.
- **Přepínání profilů:** Pokud bude role Rodič plně nasazena, prověřit, zda nápověda u "Můj profil" dostatečně srozumitelně vysvětluje přepínání mezi dětmi.

## Seznam vytvořených souborů

### Markdown obsah (CS)
- `database/seeders/Help/content/cs/uvod/vstup-do-kabiny.md`
- `database/seeders/Help/content/cs/uvod/zapomenute-heslo.md`
- `database/seeders/Help/content/cs/uvod/muj-profil.md`
- `database/seeders/Help/content/cs/uvod/zabezpeceni-uctu.md`
- `database/seeders/Help/content/cs/uvod/sokoli-v-mobilu.md`
- `database/seeders/Help/content/cs/uvod/role-v-systemu.md`
- `database/seeders/Help/content/cs/uvod/onboarding-faq.md`

### Markdown obsah (EN)
- `database/seeders/Help/content/en/uvod/vstup-do-kabiny.md`
- `database/seeders/Help/content/en/uvod/zapomenute-heslo.md`
- `database/seeders/Help/content/en/uvod/muj-profil.md`
- `database/seeders/Help/content/en/uvod/zabezpeceni-uctu.md`
- `database/seeders/Help/content/en/uvod/sokoli-v-mobilu.md`
- `database/seeders/Help/content/en/uvod/role-v-systemu.md`
- `database/seeders/Help/content/en/uvod/onboarding-faq.md`

### Seeder
- `database/seeders/Help/HelpArticleSeeder.php` (Aktualizováno o definice Batch 01)
