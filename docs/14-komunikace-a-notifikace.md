# Komunikace a notifikace

Účel: Správa veřejných oznámení (bannerů) a doručování cílených notifikací členům klubu přes in-app a emailové kanály.

## 1. Oznámení (Announcements)
- **Model:** `Announcement` (tabulka `announcements`).
- **Funkce:** Horní lišta (Announcement Bar) na webu pro důležité zprávy.
- **Parametry:** Titulek, zpráva, CTA, publikum (veřejnost/členové/všichni), stylová varianta (info/success/warning/urgent).
- **Frontend:** Komponenta `x-announcement-bar` integrovaná v public i member layoutu.

## 2. In-app Notifikace (Členský portál)
- **Technologie:** Laravel Notifications využívající `database` kanál.
- **Notifikační centrum:** Sekce v členské zóně (`/clenska-sekce/notifikace`) s výpisem zpráv a stavem přečtení.
- **Badge:** Ikona zvonku v horní navigaci s dynamickým počtem nepřečtených zpráv.

## 3. Emailové notifikace
- **BaseNotification:** Abstraktní třída zajišťující sjednocený vzhled emailů s brandingem klubu.
- **Kanály:** Automatické doručování přes `mail` a `database` na základě typu zprávy.

## 4. Architektura a integrace
- **Event-driven:** Notifikace jsou spouštěny pomocí Laravel Eventů (např. `RsvpChanged`).
- **CommunicationService:** Centrální služba pro načítání oznámení s cachováním.
- **User Preferences:** Uživatelé mají v DB JSON sloupec `notification_preferences`.
