# Kontaktní formulář a Antispamová ochrana (reCAPTCHA v3)

Tento modul zajišťuje bezpečné odesílání e-mailů z frontendu bez přímého zveřejnění e-mailových adres uživatelů/trenérů botům.

## 1. Princip fungování
- Všechny e-mailové adresy na frontendu jsou kódovány pomocí `EmailObfuscator` (Base64).
- Odkazy na e-mail nesměřují na `mailto:`, ale na speciální stránku `/napiste-nam?to={encoded_email}`.
- Tato stránka obsahuje Livewire komponentu `ContactForm`, která příjemce dekóduje a umožní uživateli odeslat zprávu i s přílohou (max. 10MB).
- Odesílání je chráněno pomocí **Google reCAPTCHA v3**.

## 2. Nastavení v administraci
Správce může konfigurovat reCAPTCHA v sekci **Administrátorské nástroje -> reCAPTCHA**:
- **Aktivovat ochranu:** Zapíná/vypíná validaci na backendu.
- **Site Key:** Veřejný klíč pro frontend.
- **Secret Key:** Tajný klíč pro backendovou validaci.
- **Práh citlivosti (Threshold):** Určuje přísnost (výchozí 0.5). Čím vyšší číslo, tím přísnější kontrola.

## 3. Technické detaily

### 3.1 Backend
- **App\Services\RecaptchaService:** Služba pro validaci tokenů z Googlu.
- **App\Support\EmailObfuscator:** Pomocná třída pro kódování/dekódování adres.
- **App\Mail\ContactFormMail:** Mailable třída pro samotné odeslání.
- **App\Livewire\ContactForm:** Komponenta obsluhující formulář, nahrávání souborů a integraci s reCAPTCHA.

### 3.2 Frontend
- Komponenta `<x-mailto :email="..." />` automaticky generuje bezpečné odkazy.
- JavaScript v `resources/js/app.js` (`initEmailProtection`) se stará o to, aby i dynamicky vložené e-maily byly přesměrovány na formulář.
- Formulář využívá Alpine.js pro asynchronní získání reCAPTCHA tokenu těsně před odesláním.

## 4. Použití v kódu
Pro vložení bezpečného e-mailového odkazu použijte komponentu `mailto`:
```blade
<x-mailto email="info@kbelstisokoli.cz" class="btn btn-primary">
    Napište nám
</x-mailto>
```

Pokud potřebujete získat URL pro formulář v PHP:
```php
use App\Support\EmailObfuscator;
$url = EmailObfuscator::getContactUrl('coach@example.com');
```

## 5. Google reCAPTCHA v3 (Údaje)
Pro projekt jsou nastaveny tyto klíče (lze změnit v adminu):
- **Site key:** `6LfRn3csAAAAAKPzWb8wMPDrP8k9qRNbh6ZA6E_I`
- **Secret key:** `6LfRn3csAAAAAH7X7gs09H8TJ8VCTX7lCDJLvldN`
