# AAQZ

This is a private Laravel 13 application configured for lightweight, server-rendered authentication.

## Authentication Setup

Authentication was added with the official Laravel Breeze starter kit using the Blade stack. The app keeps the default `web` session guard and standard login/logout flow, with password reset routes left available because they are part of the starter kit.

To keep the app private:

- Public self-registration has been disabled by removing the `/register` routes.
- The home page now redirects guests to `/login` and signed-in users to `/dashboard`.
- The main dashboard is protected by the `auth` middleware.
- Email verification routes were removed because this single-user app does not need that extra flow.

## Create The First User

Use the custom Artisan command:

```bash
php artisan app:create-user
```

The command will prompt for the name, email address, and password, then create the user in the local database.

## Local Development With SQLite

The app is set up for SQLite by default.

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Create the SQLite database file if it does not already exist:

```bash
New-Item -ItemType File -Path .\database\database.sqlite -Force
```

4. Run the migrations:

```bash
php artisan migrate
```

5. Create your first user:

```bash
php artisan app:create-user
```

6. Start the app:

```bash
composer run dev
```

The default local `.env` values should use:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

## Production MariaDB Settings

For production, switch the database connection in `.env` to MariaDB and then run migrations against the production database:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aaqz
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

Then deploy with:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

Laravel's default migrations remain safe for MariaDB in production because they use framework schema abstractions rather than SQLite-only column features.

## Commands Run For This Setup

The auth setup used these commands:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
php artisan test
```
