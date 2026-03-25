# Správa Financí - Konzole

Tento dokument popisuje administrativní příkazy pro správu financí v systému Kbelští sokoli.

## 1. Hromadné označení minulých plateb jako zaplacené

Při přechodu na novou sezónu nebo při importu starých dat může být potřeba označit všechny neuhrazené předpisy z minulých let jako zaplacené, aby nezkreslovaly aktuální stav pohledávek.

K tomuto účelu slouží příkaz:

```bash
php artisan app:finance-mark-past-seasons-paid
```

### 1.1 Jak příkaz funguje

1.  **Identifikace aktivní sezóny:** Příkaz najde sezónu, která má v databázi příznak `is_active = true`.
2.  **Identifikace minulých sezón:** Za minulé sezóny jsou považovány všechny, které nejsou aktivní a jejichž název (např. `2023/2024`) je chronologicky před aktivní sezónou.
3.  **Vyhledání předpisů:**
    *   **Členské příspěvky:** Najde všechny `FinanceCharge` typu `membership_fee`, které jsou v `metadata` propojeny s konfigurací uživatele (`UserSeasonConfig`) patřící do minulé sezóny.
    *   **Ostatní předpisy (pokuty atd.):** Najde všechny ostatní předpisy, jejichž datum splatnosti (`due_date`) spadá do časového rámce minulých sezón (standardně od 1. srpna do 31. července následujícího roku).
4.  **Aktualizace stavu:** Všem nalezeným předpisům, které nejsou ve stavu `paid` nebo `cancelled`, nastaví stav na `paid`.

### 1.2 Dostupné volby

*   `--dry-run`: Pouze vypíše počet předpisů, které by byly aktualizovány, ale neprovede žádné změny v databázi. **Doporučeno spustit jako první.**
*   `--force`: Přeskočí potvrzovací dotaz (vhodné pro neinteraktivní prostředí nebo skripty).

### 1.3 Příklad použití

```bash
# Kontrola, kolik záznamů bude ovlivněno
php artisan app:finance-mark-past-seasons-paid --dry-run

# Skutečné provedení změn
php artisan app:finance-mark-past-seasons-paid
```
