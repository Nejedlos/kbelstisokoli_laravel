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
1. **Pravidlo č. 1 (Kritické):** Produkční `.env` na serveru MUSÍ VŽDY obsahovat data odpovídající lokálnímu souboru `.env.production`.
2. **Synchronizace:** Při jakémkoliv nasazení nebo úpravě konfigurace se ujistěte, že `.env.production` je aktuální a v případě pochybností jej nahrajte na server jako `.env`.
3. **Zákaz míchání:** Nikdy nezkoušejte "rychlé fixy" na produkci ruční editací, které pak nezanesete do `.env.production`.
4. Před změnou si vytvořte zálohu přímo na serveru: `cp .env .env.bak`.

## 3. Synchronizace parametrů
Pokud přidáváte nový klíč (např. pro novou službu), přidejte jej do:
- `.env` (lokálně)
- `.env.example` (pro git)
- `.env.production` (lokální šablona pro produkci)
- `.env` na produkčním serveru
