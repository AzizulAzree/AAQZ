# AAQZ calendar widget — first prototype

Small, read-only Tauri 2 / TypeScript Windows companion. Starts as a 128 × 52 logical-pixel transparent window containing a dark calendar pill. Clicking opens a 336 × 516 panel; clicking again or pressing Escape collapses it. Alt+F4 quits. No tray, startup registration, reminders, editing, polling, local event cache, or database connection.

## Prerequisites notice

Windows development requires:

1. **Node.js 22.12+ (or a supported newer LTS) and npm** — frontend and Tauri CLI.
2. **Rustup, stable Rust and Cargo, `x86_64-pc-windows-msvc` toolchain** — native compilation.
3. **Visual Studio 2022 Build Tools**, **Desktop development with C++** workload — MSVC compiler/linker and a **Windows 10/11 SDK**. A full Visual Studio IDE is unnecessary.
4. **Microsoft Edge WebView2 Runtime** — renders the desktop interface.
5. For server development, the existing project's **PHP 8.3+**, Composer dependencies and configured Laravel database. The desktop itself needs no PHP, MySQL client or database credentials.

The C++ tools/SDK can require several GB of disk space and administrator approval. Restart the terminal after installation so Cargo is on PATH. Official instructions: <https://v2.tauri.app/start/prerequisites/#windows>.

## Launch

From this directory in PowerShell:

```powershell
npm ci
npm run tauri -- dev
```

The initial pill makes no API request. Open it to fetch the current month. If Laravel returns 401, sign in using an existing AAQZ account. The widget forgets its native in-memory session cookies when it exits. Month navigation also requests that month's data. Every reopening retrieves fresh data; there is no background refresh.

Build a standalone executable without an installer:

```powershell
npm run tauri -- build --no-bundle
```

Output: `src-tauri/target/release/aaqz-calendar-widget.exe`. End users need WebView2, but not the development toolchain.

## API and existing Laravel logic

- Model/table: `App\Models\CalendarEntry` / `calendar_entries`.
- Existing display: authenticated `/dashboard`, `DashboardController`, `CalendarMonth`, `CalendarEntryCollector`, and `resources/js/calendar-dashboard.js`.
- Existing calendar is shared across authenticated users; ownership (`source_type = self`, `source_id = users.id`) controls modification. This API preserves the dashboard's visibility, rather than inventing a new per-user scope.
- `GET /api/widget/calendar?month=YYYY-MM` uses `CalendarMonth` and `CalendarEntryCollector`, returning only `events[].id`, `title`, and `date`. Existing generated follow-up dates remain consistent with the web calendar. No schema changes or event insertion are needed.
- `GET /api/widget/session` initializes the Laravel session and returns its CSRF token. `POST /api/widget/login` uses the existing `LoginRequest`, password verification, web guard and login lockout, then regenerates the session. It does not create saved-login records or persistent remember cookies.
- All three routes intentionally live in `routes/web.php`, retaining the session and CSRF middleware. Calendar access requires `auth`; the group is rate limited. Do not move these into a stateless API middleware group without replacing session handling.
- Rust's HTTP client retains cookies in memory and sends requests only to the configured server. Redirects are disabled. Credentials, tokens and raw server errors are not logged or persisted. The frontend never receives session cookies or database configuration.

## Server configuration and deployment

The single default server value lives in `src-tauri/src/main.rs`. Override **API_BASE_URL** in the process environment when launching:

```powershell
$env:API_BASE_URL = 'https://your-server.example'
npm run tauri -- dev
```

The executable reads this environment variable too. Laravel's `.env` is never loaded by the desktop. No frontend `.env` is required. HTTP is supported for the requested development prototype, but transmits passwords, session cookies and event data without encryption; use HTTPS for secure deployment and set Laravel's secure-cookie configuration accordingly. Do not disable TLS verification.

Deploy `app/Http/Controllers/WidgetCalendarController.php` and the widget route additions in `routes/web.php` to the existing Laravel application. Refresh the route cache using the application's normal deployment procedure. There are no widget migrations or seeds. The server continues to use its existing database configuration. Do not run first-install scripts or replace production `.env`.

An unauthenticated request with `Accept: application/json` should return JSON with status 401. A 404 means the route has not been deployed (or the route cache needs refresh). Authenticate to verify actual production records; do not make the route public for testing.

## Checks

From the Laravel repository root:

```powershell
php artisan test --filter=Calendar
```

From `desktop`:

```powershell
npm run check
npm run build
npm test
npm run tauri -- dev --no-watch
```

The Playwright tests use installed Microsoft Edge and mock only the native IPC boundary. Their fixtures are test-only and never enter Laravel or its database. These tests verify collapsed/open states, date selection and markers, navigation/refetching, safe text rendering, failure handling and sign-in. They do not verify native window placement or connectivity to production. Laravel feature tests run against an isolated in-memory test database.

## Verification on 22 September 2026

- Installed Rustup 1.29.1 / stable MSVC toolchain (Cargo 1.98.1), Visual Studio 2022 Build Tools 17.14.41 with the C++ workload, and Windows SDK 10.0.26100.0. Node 24.13.1, npm 11.8.0 and WebView2 153.0.4234.48 were already present.
- TypeScript checking, Vite build, three Edge/Playwright tests, and native Tauri debug build passed. `tauri dev --no-watch` compiled and launched a responding `AAQZ Calendar` window.
- The calendar-related Laravel tests passed, including eight widget API tests covering exact response fields, session authentication, CSRF enforcement and login lockout.
- The full Laravel suite exposed two unrelated existing `BppTest` preview failures: `printable_preview_routes_render_stable_bpp_pages` and `combined_bpp_preview_route_renders_all_four_core_pages`. These were reproduced separately (28 passed, 2 failed); BPP files were not changed.
- Deployed only the widget controller and routes to `/var/www/laravel-app` through the existing Google Cloud browser SSH session. PHP syntax checks and route-cache refresh passed. Original production routes are backed up at `/home/azizulazree94/widget-web-backup-1790065285.php`.
- The live unauthenticated calendar endpoint returns JSON 401. Laravel reports its existing MySQL connection and 110 calendar rows; no records were inserted or changed for the widget.
- The user confirmed that the native pill opens, real entries appear after sign-in, and it collapses again.
- The standalone release build passed with `CARGO_BUILD_JOBS=2` after an initial compiler-process failure at the default parallelism. The executable is `src-tauri/target/release/aaqz-calendar-widget.exe`. The running development widget was left open; close it with Alt+F4 before launching another copy.
