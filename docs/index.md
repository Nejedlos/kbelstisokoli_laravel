# Dokumentace projektu Kbelští sokoli

Vítejte v technické dokumentaci projektu Kbelští sokoli. Tato složka obsahuje detailní popis modulů, funkcionalit a technických specifikací celého systému.

## Zdrojový kód (GitHub)
Repozitář: [https://github.com/Nejedlos/kbelstisokoli_laravel](https://github.com/Nejedlos/kbelstisokoli_laravel)

## Obsah dokumentace
- [Struktura projektu](project_structure.md)
- [Správa uživatelů](user_management.md)
- [Ekonomický modul](economy_management.md)
- [Administrace](administration.md)
- [Nasazení (Deployment)](deployment.md)

## Rychlý start
Projekt je postaven na Laravelu 12. Pro lokální vývoj se doporučuje používat **Laravel Sail**.

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```
