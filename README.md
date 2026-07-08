# AAQZ

AAQZ is a private Laravel 13 workspace app for keeping everyday planning and procurement work in one place. It combines a calendar view for reminders and follow-ups, a project area for organizing workspaces and shortcuts, a BPP procurement workspace with printable/exportable form flows, a public portfolio page, and a personal sticky note that follows the signed-in user across pages and devices.

The app is designed around a simple idea: reduce context switching. Instead of spreading quick reminders, recurring follow-ups, bookmarked links, and admin tasks across several tools, AAQZ keeps them inside one authenticated interface.

## App Overview

AAQZ is currently built around five core areas:

- `Calendar`: a monthly planning view with reminder summaries, quick entry creation, and follow-up support.
- `Project`: a lightweight workspace organizer for folders, shortcuts, and recently opened links.
- `BPP`: a procurement request workspace for managing BPP data, supplier comparison drafts, appendix rows, quotation extraction review, and printable/PDF outputs.
- `Portfolio`: a public scroll-based portfolio page for Azizul Azree, available outside authentication.
- `Sticky note`: a draggable personal note that auto-saves, supports quick strike-through formatting, and stays with the user across authenticated pages.

The app uses server-rendered Laravel Blade views with Alpine.js for focused interactive behavior where needed.

## Current Features

### Private Access

- Login-protected application with Laravel Breeze Blade authentication.
- Public self-registration is disabled.
- Guests are redirected to the login screen.
- Signed-in users land in the authenticated workspace instead of a public homepage.

### Calendar And Reminder Flow

- Monthly calendar rendered in Blade without a heavy calendar package.
- Sunday-to-Saturday calendar grid with previous and next month navigation.
- Quick entry creation directly from a selected day.
- Entry detail modals for reviewing saved notes and metadata.
- Three-day reminder strip at the top of the dashboard.
- Follow-up reminders that can automatically surface an entry again after a chosen number of days.
- User color markers shown on entries and account surfaces for clear ownership.

### Project Workspace Organizer

- Create personal workspaces.
- Add nested folders inside each workspace.
- Add shortcuts inside folders.
- Track recently opened shortcuts for quick return access.
- Keep project links grouped in a cleaner, less cluttered structure than a traditional bookmarks list.

### Public Portfolio Page

- Public portfolio available at `/azizulazree` without authentication.
- Legacy `/portfolio` and `/portfolio/azizul-azree` paths redirect to the new public route.
- Full-screen scroll presentation with section-based storytelling.
- Includes storefront previews, infrastructure notes, previous roles, contact details, and a reveal footer treatment.
- Uses optimized local thumbnails for the showcased storefront work.

### BPP Procurement Workspace

- Create and update BPP records from the authenticated app.
- Manage the BPP drafting workspace for procurement method, applicant details, B(i), B(ii), supplier recommendation fields, and selection reasons.
- Manage appendix row drafts for C2, C3, and C4 style procurement tables.
- Manage supplier quote rows and selected supplier state.
- Auto-sync selected supplier details into the BPP `D` section fields.
- Review printable page previews for the checklist, page one, page two, C1, C2, C3, C4, and the combined BPP preview route.
- Export the current printable six-page package to PDF using a browser-based renderer and FPDI page merging.
- Parse a structured quotation extraction result and store it for review before applying it to the BPP draft.
- Apply a valid quotation extraction result into:
  - `bpp_supplier_quotes`
  - `bpp_supplier_quote_items`
  - `bpp_appendix_rows`

### Sticky Note Prototype

- Floating sticky note available across authenticated pages.
- Draggable note position that persists for the user.
- Auto-saving note content in the database.
- Collapse and expand interaction.
- Minimal `Strike` action for crossing out highlighted text inside the note.
- Uses the app's normal font styling at a smaller note size.
- State stored in the database so the note follows the user across devices.

### Settings And Admin Tools

- User management screen for adding additional sign-in accounts.
- Per-user color assignment and uniqueness enforcement.
- Read-only style data browser for inspecting stored application data from the admin area.

### BPP Printable And Export Routes

Authenticated BPP printable routes currently include:

- `/bpp/{bpp}/printables/preview`
- `/bpp/{bpp}/printables/checklist`
- `/bpp/{bpp}/printables/page-one`
- `/bpp/{bpp}/printables/page-two`
- `/bpp/{bpp}/printables/c1`
- `/bpp/{bpp}/printables/c2`
- `/bpp/{bpp}/printables/c3`
- `/bpp/{bpp}/printables/c4`

The PDF export routes are:

- `/bpp/{bpp}/pdf`
- `/bpp/{bpp}/export/pdf`

The combined preview route is the quick in-app BPP review surface and currently shows the four core BPP pages together:

- fixed page 1 template
- fixed page 2 template
- live page 3 BPP request body
- fixed page 4 template

The current merged export flow is browser-rendered and produces a six-page PDF package:

- page 1 from `page-one-document`
- page 2 from `page-two-document`
- page 3 from `page-three-document`
- page 4 from `page-four-document`
- page 5 from `page-five-document`
- page 6 from `page-six-document`

The export pipeline renders each page to PDF through a local Chromium-based browser and then merges the page PDFs with FPDI so portrait and landscape pages can coexist in one package.

### Public Routes

The app also exposes a small set of public routes outside authentication:

- `/azizulazree`
- `/portfolio`
- `/portfolio/azizul-azree`
- `/ordering/{user}/{token}`

## Why This App Exists

AAQZ is meant to support a small, private workflow rather than a public SaaS product. It fits best when the goal is:

- keeping personal or shared planning lightweight
- tracking upcoming tasks without opening a full project suite
- storing quick-access links in a structured way
- capturing temporary notes without losing them during page navigation

## Future Planning

The current version already covers the core workflow, but there are a few natural next steps for the app.

### Product Direction

- Turn sticky notes into a fuller notes system with multiple notes, colors, pinning, and archiving.
- Let calendar entries connect directly to project workspaces or shortcuts.
- Add filtering so users can view calendar items by owner, reminder type, or follow-up state.
- Improve the reminder experience with overdue, upcoming, and completed states.

### Collaboration Improvements

- Shared workspaces for multiple users.
- Shared project folders or team shortcut collections.
- Team-visible notes or comments attached to a day, workspace, or link.
- Role-based permissions beyond the current first-user admin model.

### Quality Of Life

- Search across shortcuts, workspaces, calendar entries, and notes.
- Drag-and-drop organization in the project section.
- Better mobile-specific sticky note behavior.
- Richer note formatting for the sticky note or future note system beyond the current strike-through action.
- Continue refining the BPP printable layout so the generated package stays visually aligned with the official document set.

## Developer Documentation

The sections below focus on local setup, database expectations, and implementation notes for development.

### Authentication Setup

Authentication was added with the official Laravel Breeze starter kit using the Blade stack. The app keeps the default `web` session guard and standard login/logout flow, with password reset routes left available because they are part of the starter kit.

To keep the app private:

- Public self-registration has been disabled by removing the `/register` routes.
- The home page redirects guests to `/login` and signed-in users to `/dashboard`.
- The main dashboard is protected by the `auth` middleware.
- Email verification routes were removed because this private app does not need that extra flow.

### Calendar Notes

The dashboard calendar uses a dedicated collector so additional models can feed the calendar later without rewriting the main view.

Calendar entries currently use the `calendar_entries` table with these main fields:

- `entry_date`
- `title`
- `details` nullable
- `source_type` nullable
- `source_id` nullable
- follow-up fields
- timestamps

Manual entries can be created in Tinker:

```bash
php artisan tinker
```

```php
\App\Models\CalendarEntry::create([
    'entry_date' => '2026-04-20',
    'title' => 'Example entry',
    'details' => 'Shown on the dashboard calendar.',
]);
```

### Sticky Note Notes

Sticky note state is stored in the `sticky_notes` table and currently supports one note per user.

The saved fields are:

- `content`
- `position_x`
- `position_y`
- `is_collapsed`

The `content` field now preserves simple strike-through formatting for highlighted text while still keeping the note as one continuous personal memo.

This is still intentionally a simple prototype-friendly structure that can be expanded later into a richer notes feature.

### BPP Notes

The BPP module currently stores its draft state across:

- `bpps`
- `bpp_supplier_quotes`
- `bpp_supplier_quote_items`
- `bpp_appendix_rows`

The quotation extraction assistant expects a strict plain-text payload in the `QUOTATION_EXTRACTION_V1` format. A valid extraction can be parsed for review and then applied into the supplier quote and appendix draft tables.

The extraction import flow depends on these sections being present exactly:

- `SUPPLIERS`
- `SUPPLIER_COMPARISON_ITEMS`
- `SELECTED_SUPPLIER_ITEMS`
- `TOTALS`

If the structured extraction is invalid, the BPP draft review is saved, but the supplier quote and appendix tables are not replaced until a valid result is applied.

PDF package export depends on a locally installed Chromium-based browser. The app checks `BPP_PDF_BROWSER_PATH` first, then falls back to common Chrome and Edge paths on Windows.

### Test User Login

When you run:

```bash
php artisan db:seed
```

the app also creates a ready-to-use test account for local use:

- Name: `Test User`
- Email: `test@example.com`
- Password: `password`

### Create The First User

Use the custom Artisan command:

```bash
php artisan app:create-user
```

The command prompts for the name, email address, and password, then creates the user in the local database.

After signing in, open the Profile page to choose the color that should represent that user throughout the app.

### Local Development With SQLite

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

5. Seed the database for sample calendar data and a ready-to-use test login:

```bash
php artisan db:seed
```

6. Or create your own first user:

```bash
php artisan app:create-user
```

7. Start the app:

```bash
composer run dev
```

If you want to open the public portfolio directly during local development, use:

```bash
php artisan serve
```

Then visit:

- `/azizulazree`

The default local `.env` values should use:

```dotenv
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
BPP_PDF_BROWSER_PATH='C:\Program Files\Google\Chrome\Application\chrome.exe'
```

### Production MariaDB Settings

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

If you plan to use BPP PDF export in production, also set `BPP_PDF_BROWSER_PATH` to the full path of the server's Chrome or Edge binary.

### Commands Used During Setup

The initial auth setup used these commands:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
npm run build
php artisan migrate
php artisan test
```

Useful BPP-related development commands:

```bash
php artisan view:clear
php artisan route:list --path=bpp
php artisan test tests/Feature/BppTest.php --filter=quotation_extraction
php artisan test tests/Feature/BppTest.php --filter=pdf
```
