# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Technology Stack

This is a Laravel 12 application with an Inertia.js + React frontend, using:
- **Backend**: Laravel 12 (PHP 8.2+), Laravel Fortify for authentication
- **Admin Panel**: Filament 3.3+ for admin interface
- **Frontend**: React 19, TypeScript, Inertia.js for SPA-like experience
- **Styling**: Tailwind CSS 4, Radix UI components
- **Build Tool**: Vite 7
- **Testing**: Pest (PHP), configured with RefreshDatabase trait for Feature tests
- **Code Quality**: Laravel Pint (PHP), ESLint + Prettier (JavaScript/TypeScript)
- **Development**: Laravel Wayfinder for type-safe routing

## Development Commands

### Starting Development Server
```bash
composer dev
# Runs: PHP dev server, queue worker, Pail logs, and Vite dev server concurrently
```

For SSR (Server-Side Rendering):
```bash
composer dev:ssr
# Builds SSR bundle and starts Inertia SSR server
```

### Building Assets
```bash
npm run build        # Client-side build
npm run build:ssr    # Client + SSR build
```

### Running Tests
```bash
composer test        # Run all Pest tests
php artisan test     # Alternative command
```

To run a single test file:
```bash
php artisan test tests/Feature/ExampleTest.php
```

To run a specific test:
```bash
php artisan test --filter test_name
```

### Code Quality
```bash
# PHP
vendor/bin/pint              # Format PHP code

# JavaScript/TypeScript
npm run lint                 # Run ESLint with auto-fix
npm run format               # Format with Prettier
npm run format:check         # Check formatting
npm run types                # Type-check TypeScript
```

### Filament Admin Panel
```bash
# Common Filament commands
php artisan make:filament-resource ModelName    # Create a new resource
php artisan make:filament-page PageName         # Create a new page
php artisan make:filament-widget WidgetName     # Create a new widget
php artisan make:filament-user                  # Create an admin user

# Asset management
php artisan filament:assets                     # Publish Filament assets
php artisan filament:upgrade                    # Upgrade Filament (auto-run on composer update)
```

## Architecture Overview

### Inertia.js Integration
This application uses Inertia.js to build a single-page application without a traditional API. Key concepts:

- **Server-side**: Controllers return Inertia responses via `Inertia::render('page-name', $props)`
- **Client-side**: Pages are React components in `resources/js/pages/`
- **Shared data**: Configured in `HandleInertiaRequests` middleware (app/Http/Middleware/HandleInertiaRequests.php:37-50)
  - All pages receive: `name`, `quote`, `auth.user`, `sidebarOpen`
- **Page resolution**: Uses `resources/js/pages/${name}.tsx` convention (resources/js/app.tsx:13-16)

### Frontend Structure

**Layouts** (`resources/js/layouts/`):
- `app-layout.tsx` - Main authenticated app layout wrapper
- `auth-layout.tsx` - Authentication pages layout wrapper
- `app/` - Specific app layout variants (sidebar, header)
- `auth/` - Specific auth layout variants (split, simple, card)
- `settings/` - Settings pages layout

**Components** (`resources/js/components/`):
- `ui/` - Shadcn/ui components (Button, Dialog, etc.)
- Application-specific components (AppShell, AppSidebar, NavMain, etc.)
- Custom hooks in `hooks/` (use-appearance, use-two-factor-auth, etc.)

**Type-safe Routing** (`resources/js/actions/`):
- Laravel Wayfinder generates type-safe route helpers
- Mirrors backend route structure (App/Http/Controllers/, Laravel/Fortify/, etc.)
- Use these instead of hardcoded route strings

### Backend Structure

**Routes**:
- `routes/web.php` - Main routes, imports `auth.php` and `settings.php`
- `routes/auth.php` - Authentication routes (login, register, password reset, email verification)
- `routes/settings.php` - Settings routes (profile, password, 2FA)

**Authentication**:
- Powered by Laravel Fortify (app/Providers/FortifyServiceProvider.php)
- Custom controllers in `app/Http/Controllers/Auth/`
- Two-factor authentication via Fortify

**Middleware**:
- `HandleInertiaRequests` - Shares data with all Inertia pages
- `HandleAppearance` - Manages light/dark mode preference

### Theme/Appearance System
The application supports light/dark/auto themes:
- Backend stores preference in cookies (HandleAppearance middleware)
- Frontend manages via `use-appearance` hook (resources/js/hooks/use-appearance.tsx)
- Theme initialized on page load (resources/js/app.tsx:28)

### Testing with Pest
- Pest is configured to use `RefreshDatabase` trait for Feature tests (tests/Pest.php:14-16)
- Unit tests in `tests/Unit/`, Feature tests in `tests/Feature/`
- Tests extend `Tests\TestCase` which bootstraps Laravel

### Filament Admin Panel
The application uses Filament for the admin interface:
- **Admin routes**: Automatically registered at `/admin` by Filament
- **Panel Provider**: `app/Providers/Filament/AdminPanelProvider.php` - configures the admin panel
- **Resources**: Located in `app/Filament/Resources/` - define CRUD interfaces for models
- **Pages**: Located in `app/Filament/Pages/` - custom admin pages
- **Widgets**: Located in `app/Filament/Widgets/` - dashboard widgets
- **Configuration**: `config/filament.php` - Filament settings
- **Brand**: Logo at `public/logo_costeno_LP.svg`, brand name "Costeño LP"
- **Home URL**: Redirects to Documents resource index page
- **Preference**: Use Filament's built-in components and avoid custom CSS/JS when possible
- **Navigation after actions**: Create and Edit actions must redirect back to the resource list page
- **Notifications standard**: All notifications must include icon, title, and subtitle (body)
- **File storage**: Uses 'public' disk for file uploads (configured in `config/filament.php`)

## Key Configuration Files

- `vite.config.ts` - Vite configuration with Laravel plugin, React, Tailwind, and Wayfinder
- `phpunit.xml` - Test suite configuration (SQLite in-memory for tests)
- `composer.json` - PHP dependencies and composer scripts
- `package.json` - NPM dependencies and scripts
- `eslint.config.js` - ESLint configuration
- `.prettierrc` - Prettier configuration
- `config/filament.php` - Filament admin panel configuration

## Development Workflow

1. Ensure `.env` is configured (copy from `.env.example`)
2. Run `composer install` and `npm install`
3. Generate application key: `php artisan key:generate`
4. Run migrations: `php artisan migrate`
5. Create admin user: `php artisan make:filament-user`
6. Start development: `composer dev` (starts all services)
7. Access frontend at `http://localhost:8000` (or APP_URL from .env)
8. Access admin panel at `http://localhost:8000/admin`

## Domain Models

The application manages legal/administrative documents with the following key models:

**User** (app/Models/User.php):
- Authentication via Laravel Fortify
- Two-factor authentication support
- Belongs to multiple Branches
- Tracks uploaded Documents
- Fields: name, email, avatar, phone, address, is_active, last_login_at

**Branch** (app/Models/Branch.php):
- Organizational units (e.g., offices, departments)
- Has many Users and Documents
- Fields: name, code, address, phone, email, is_active

**DocumentType** (app/Models/DocumentType.php):
- Categories for documents
- Has many Documents
- Fields: name, description
- Soft deletes enabled

**Document** (app/Models/Document.php):
- Core entity for file management
- Belongs to: DocumentType, Branch, User (uploadedBy)
- File handling with automatic metadata capture (original_filename, file_size, mime_type)
- Optional expiration tracking (expires_at)
- Method: `isExpired()` - checks if document has expired
- Soft deletes enabled

## Development Guidelines

- **Admin Panel**: Always prefer Filament's built-in functionality for admin features. Avoid custom CSS/JS when possible.
- **Planning**: All agents must ask clarifying questions before implementing any functionality.
- **Implementation**: Always show a detailed plan before implementing new features, without exception.
- **Soft Deletes**: All Filament resources and Laravel models should use soft deletes by default.
- **File Uploads**: Use 'public' disk consistently across all Filament resources for file storage.