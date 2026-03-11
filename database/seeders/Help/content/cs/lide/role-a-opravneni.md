Systém Kbelští sokoli využívá model RBAC (Role-Based Access Control). To znamená, že přístup k jednotlivým funkcím v administraci i mobilní aplikaci je řízen přidělenými rolemi.

### Hlavní systémové role
1. **Superadmin**: Má absolutní přístup ke všemu v systému, včetně citlivých nastavení, logů a správy samotných rolí.
2. **Administrátor**: Hlavní role pro vedení klubu. Může spravovat členy, finance, týmy a obsah webu, ale nemá přístup k nejcitlivějším technickým konfiguracím.
3. **Trenér**: Role zaměřená na sportovní činnost. Vidí své týmy, tréninky, docházku a zápasy. Nemůže upravovat globální nastavení nebo finance ostatních členů.
4. **Redaktor**: Specializovaná role pro správu obsahu. Může psát aktuality, nahrávat fotogalerie a upravovat statické stránky na webu.
5. **Uživatel (Hráč/Rodič)**: Základní role, kterou má každý člen. Umožňuje přístup do členské sekce pro správu vlastního profilu, plateb a docházky.

### Jak přidělit roli členovi?
Přidělování rolí probíhá v detailu člena (Lidé a členové > Uživatelé).
1. Vyhledejte uživatele a otevřete jeho editaci.
2. Přejděte na záložku **Zabezpečení** (případně Admin podle konfigurace).
3. V poli **Role** vyberte ze seznamu požadovanou roli (např. "Coach").
4. Uložte změny tlačítkem **Uložit změny**.
*Poznámka: Uživatel může mít i více rolí současně (např. Trenér a zároveň Redaktor).*

### Rozdíl mezi Rolí a Týmovou rolí
- **Systémová role** (tato sekce): Určuje, co uživatel vidí v menu administrace a jaká má globální práva.
- **Týmová role** (v Sezónní konfiguraci): Určuje pouze vztah k týmu (např. Kapitán, Brankář). Nemá vliv na přístup do administrace.

### Pokročilá správa (Superadmin)
V sekci **Nastavení systému > Role a Oprávnění** mohou superadministrátoři definovat jemná oprávnění (permissions) pro jednotlivé role.
- **Varování**: Změna oprávnění u základních rolí (jako Coach nebo Admin) může mít vliv na funkčnost celého systému. Provádějte tyto změny pouze s plným pochopením dopadů.
