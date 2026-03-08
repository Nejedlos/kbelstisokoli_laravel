# Introduction and Project Overview

Welcome to the technical documentation of the Kbelští sokoli project. This section provides a basic overview of the system, its purpose, and key technologies.

## System Purpose
The system serves for comprehensive management of the Kbelští sokoli club, including:
- Member base and athlete profiles.
- Sports planning, training, and attendance (Attendance).
- Economic agenda, membership fees, and invoicing.
- Communication with members and public presentation of the club.

## Technological Stack
- **Backend:** Laravel 12 (PHP 8.4+)
- **Administration:** Filament PHP 5
- **Frontend:** Laravel Folio, Blade, Livewire, Tailwind CSS
- **Database:** SQLite (development), MySQL (production)

## Complete Documentation
This file is part of structured documentation. You can find a complete overview of all topics in the main hub:

👉 [**Documentation Index (Hub)**](../index.md)

## Quick Start for Developers
The project is fully containerized using Laravel Sail.

```bash
# Start the environment
./vendor/bin/sail up -d

# Initial setup (migrations and data seeding)
./vendor/bin/sail artisan migrate --seed

# Build assets
npm install && npm run build
```

Detailed information about configuration can be found in [Environment Configuration](./04-configuration.md).
