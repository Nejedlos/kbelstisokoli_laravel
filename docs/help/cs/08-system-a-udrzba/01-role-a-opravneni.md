# Role a oprávnění

Správa uživatelských rolí a jejich konkrétních pravomocí v systému.

Nápověda > Systém a údržba > Role a oprávnění

### Pro koho je sekce určena
- Administrátor

### Vysvětlení sekce
Systém používá "Role" (např. Trenér, Editor), které v sobě sdružují jednotlivá "Oprávnění" (např. "vytvořit článek", "smazat zápas"). Přiřazením role uživateli mu udělujete všechna oprávnění, která tato role obsahuje.

### Nejčastější akce

#### Vytvoření nové role
1. Klikněte na **Vytvořit roli**.
2. Zadejte **Název** role (např. "Vedoucí klubu").
3. V seznamu **Oprávnění** zaškrtněte vše, co má tato role umět.
4. Klikněte na **Vytvořit**.

#### Úprava oprávnění u stávající role
1. Vyhledejte roli v seznamu a otevřete ji.
2. Zaškrtněte nebo odškrtněte požadovaná oprávnění.
3. Klikněte na **Uložit**. Změna se projeví u všech uživatelů s touto rolí okamžitě.

### Popis obrazovky
- **Tabulka rolí:** Seznam definovaných rolí v systému.
- **Seznam oprávnění:** Dlouhý seznam všech technických akcí, které lze povolit/zakázat.

### Vysvětlení polí
- **Název role:** Pojmenování role (např. Super Admin).
- **Oprávnění:** Jednotlivé technické klíče (např. `view_users`, `create_articles`).

### Časté chyby a upozornění
- **Příliš silná oprávnění:** Nedávejte uživatelům více práv, než skutečně potřebují pro svou práci.
- **Super Admin:** Role s názvem `super_admin` má obvykle přístup ke všemu nezávisle na zaškrtnutých polích.
- **Smazání role:** Pokud smažete roli, kterou mají uživatelé přiřazenou, ztratí tito uživatelé přístup k daným funkcím.

### Související sekce
- [Uživatelé](../03-lide-a-clenove/01-uzivatele.md)
- [Auditní logy](02-auditni-logy.md)
