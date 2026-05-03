# Bezpečnostní pravidla pro správu .env (Produkce vs. Local)

Tento dokument slouží jako prevence proti nechtěnému přepsání produkčního nastavení lokálními daty.

## 1. Kritické rozdíly

| Proměnná | Lokální (Herd) | Produkce (Webglobe) |
| :--- | :--- | :--- |
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `DB_HOST` | `127.0.0.1` | `db.dw194.webglobe.com` |
| `DB_DATABASE` | `kbelstisokoli` | `kbelstisokolicz_kbelstisokoli` |
| `MAIL_HOST` | `127.0.0.1` | `mail.webglobe.cz` |

## 2. Postup při změně .env na produkci
1. **Nikdy** nepoužívejte `scp .env ...`, pokud si nejste 100% jisti obsahem.
2. Preferujte úpravu přímo na serveru přes `nano .env` nebo `vim .env`.
3. Před změnou si vytvořte zálohu: `cp .env .env.bak`.

## 3. Synchronizace parametrů
Pokud přidáváte nový klíč (např. pro novou službu), přidejte jej do:
- `.env` (lokálně)
- `.env.example` (pro git)
- `.env.production` (lokální šablona pro produkci)
- `.env` na produkčním serveru
