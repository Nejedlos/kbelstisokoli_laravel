# Správa plateb a příspěvků

Ekonomický modul slouží k evidenci předpisů plateb (např. členské příspěvky) a jejich následné spárování s reálnými platbami.

## 💰 Jak vytvořit předpis platby

1. Přejděte do **Ekonomika > Předpisy plateb**.
2. Klikněte na **Nový předpis**.
3. **Parametry:**
    - **Částka:** Kolik má člen zaplatit.
    - **Variabilní symbol:** Důležité pro automatické párování.
    - **Splatnost:** Do kdy má být zaplaceno.
4. **Přiřazení:** Můžete vybrat konkrétního uživatele nebo celý tým hromadně.

## 🔄 Párování plateb
Systém umožňuje import bankovních výpisů (přes externí moduly). Platby jsou automaticky párovány na základě variabilního symbolu. Pokud se shoda nenajde, můžete platbu přiřadit ručně.

## 📈 Sledování dlužníků
V přehledu vidíte barevně odlišené stavy:
- <span style="color: green">Zaplaceno</span>
- <span style="color: orange">Částečně</span>
- <span style="color: red">Po splatnosti (Dlužník)</span>
