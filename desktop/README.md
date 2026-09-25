# AAQZ calendar widget

Small, read-only Tauri 2 / TypeScript Windows companion. Starts as a 128 × 52 logical-pixel transparent window containing a dark calendar pill. Clicking opens a 336 × 516 panel; clicking again or pressing Escape collapses it. The app appears in the Windows notification tray instead of the taskbar. Left-click its tray icon to show it; right-click for Show widget, Hide widget, or Quit. Alt+F4 also quits. No startup registration, reminders, editing, polling, local event cache, or database connection.

## Prerequisites notice

Windows development requires:

1. **Node.js 22.12+ (or a supported newer LTS) and npm** — frontend and Tauri CLI.
2. **Rustup, stable Rust and Cargo, `x86_64-pc-windows-msvc` toolchain** — native compilation.
3. **Visual Studio 2022 Build Tools**, **Desktop development with C++** workload — MSVC compiler/linker and a **Windows 10/11 SDK**. A full Visual Studio IDE is unnecessary.
4. **Microsoft Edge WebView2 Runtime** — renders the desktop interface.
5. For server development, the existing project's **PHP 8.3+**, Composer dependencies and configured Laravel database. The desktop itself needs no PHP, MySQL client or database credentials.

The C++ tools/SDK can require several GB of disk space and administrator approval. Restart the terminal after installation so Cargo is on PATH. Official instructions: <https://v2.tauri.app/start/prerequisites/#windows>.

## Install and open

Download the Windows `AAQZ Calendar_<version>_x64-setup.exe` from the [latest GitHub release](https://github.com/AzizulAzree/AAQZ/releases/latest), run it once, then open **AAQZ Calendar** from the Start menu. It installs for the current Windows user and can install WebView2 if missing. Close any old portable/development copy first. Version 0.1.0 has no updater, so existing prototype users need this one-time manual installation.

Expand the pill to refresh calendar entries and check GitHub for app updates. When a newer version is available, click **Update now** once. The app downloads it, verifies its signature, launches the installer and restarts. No installation starts without that click. Failed checks or downloads leave the calendar usable and offer Retry. After restart, choose a saved account to sign in with one click.

There are no timers or background calendar requests. Keeping the panel open does not continuously refresh it; close/reopen or change months for fresh entries. Update checks happen on expansion, not while the pill sits collapsed.

## Development launch

From this directory in PowerShell:

```powershell
npm ci
npm run tauri -- dev
```

The initial pill makes no API request. Open it to fetch the current month. If Laravel returns 401, sign in using an existing AAQZ account. Session cookies remain in memory. With Save account on this device checked, a separate revocable login token and account label are stored in Windows Credential Manager, scoped to this API server. Month navigation also requests that month's data. Every reopening retrieves fresh data; there is no background refresh.

Build a standalone executable without an installer:

```powershell
npm run tauri -- build --no-bundle
```

Output: `src-tauri/target/release/aaqz-calendar-widget.exe`. This is useful for development; distribute the installer for reliable in-app updates. End users need WebView2, but not the development toolchain.

## Publish an app update

Updates use Tauri's official updater, HTTPS GitHub downloads, and mandatory signature verification. Update signing is separate from Windows Authenticode signing; Windows can still show an unknown-publisher notice for the initial installer.

The private signing key is **outside the repository** at `%USERPROFILE%\.tauri\aaqz-calendar.key`, with access restricted to the current Windows user. Back it up securely. Never upload it to GitHub, attach it to a release, paste it into a task, or ship it with the app. Keep using the same key for future releases: existing installations trust the public key in `src-tauri/tauri.conf.json`. If publishing from another machine, transfer the key securely or supply `TAURI_SIGNING_PRIVATE_KEY` and (if applicable) `TAURI_SIGNING_PRIVATE_KEY_PASSWORD` through that machine's secret management.

For each new version:

1. Set the same higher version in `package.json`/`package-lock.json`, `src-tauri/Cargo.toml`, and `src-tauri/tauri.conf.json`. For example, run `npm version 0.2.1 --no-git-tag-version`, then update the two native files.
2. Run `npm test`, then **`npm run release`**. This builds with two Cargo jobs, signs the NSIS installer, creates `latest.json`, and verifies that the installer matches the app's public key and that modified bytes are rejected. Never publish if this command fails.
3. Commit the release source, create tag **`widget-v<version>`** pointing to that commit, and push that tag to `AzizulAzree/AAQZ`.
4. Create a GitHub Release for that tag. Upload all three files from `desktop/release/widget-v<version>/`: the setup EXE, its `.sig`, and `latest.json`.
5. Publish it as a normal release and mark it **Latest**. Drafts and prereleases are not offered by the configured endpoint. Keep earlier installers available because their manifests contain version-specific download URLs.

Only app binaries and public metadata belong in release assets. The Laravel server/database is not bundled. Do not mark unrelated Laravel releases as Latest without including the desktop update manifest; this repository's Latest release is the desktop update channel. A future dedicated downloads repository would avoid that constraint.

The release script prepares files but does not silently publish them. Published app versions do not require a Laravel deployment unless the server API itself changes.

## API and existing Laravel logic

- Model/table: `App\Models\CalendarEntry` / `calendar_entries`.
- Existing display: authenticated `/dashboard`, `DashboardController`, `CalendarMonth`, `CalendarEntryCollector`, and `resources/js/calendar-dashboard.js`.
- Existing calendar is shared across authenticated users; ownership (`source_type = self`, `source_id = users.id`) controls modification. This API preserves the dashboard's visibility, rather than inventing a new per-user scope.
- `GET /api/widget/calendar?month=YYYY-MM` uses `CalendarMonth` and `CalendarEntryCollector`, returning only `events[].id`, `title`, and `date`. Existing generated follow-up dates remain consistent with the web calendar. No new schema changes or event insertion are needed; saved accounts reuse the existing saved_logins table.
- `GET /api/widget/session` initializes the Laravel session and returns its CSRF token. `POST /api/widget/login` uses the existing `LoginRequest`, password verification, web guard and login lockout, then regenerates the session. The save_account option creates a saved-login record; only its token hash and password fingerprint are stored on the server. No persistent session cookies are written.
- All three routes intentionally live in `routes/web.php`, retaining the session and CSRF middleware. Calendar access requires `auth`; the group is rate limited. Do not move these into a stateless API middleware group without replacing session handling.
- Rust's HTTP client retains cookies in memory and sends requests only to the configured server. Redirects are disabled. Passwords, session cookies and raw server errors are not logged or persisted. Saved-login tokens are kept only in Windows Credential Manager. The frontend never receives session cookies or database configuration.

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

## Version 0.2.0 verification

- TypeScript checking and the production frontend build passed.
- All seven Edge/Playwright UI tests passed, covering refresh-on-expand, no polling, explicit update consent, successful install/restart requests, and retry after check/download failures. Native updater calls are mocked in these UI tests.
- The release command built the NSIS setup EXE and signature, generated the GitHub update manifest, verified the installer using the configured public key, and confirmed tampered installer bytes are rejected.

## Saved accounts and workspace (0.3.0)

- On the first login, leave **Save account on this device** checked. After restart, click the saved name/email card. Up to 10 accounts can be saved per server and Windows user. **Switch account** signs out of the active session and opens the picker; **Use another account** opens the form. **Remove** revokes that saved token and deletes the Windows credential. If offline, local removal still completes and the UI reports that server revocation could not be confirmed.
- Tokens expire after 90 days and stop working after password changes, server revocation, or account deletion. An expired card prefills only the email and asks for the password again. No password is stored. Signing out retains saved accounts, as the website does. The initial 0.2.x login cannot be recovered; sign in once in 0.3.0 to save it.
- **Workspace** shows only the signed-in user's existing workspaces, nested folders, shortcut URLs, workspace notes and sticky note. Notes load on demand and render as plain text. Only HTTP(S) shortcuts without embedded credentials can open in the default browser. Browser website sessions remain separate from the widget session.
- The current tab refreshes when expanded. Calendar month navigation refetches calendar data; selecting Workspace refetches its tree. No polling or disk cache of notes/workspace content. This is a read-only view; edit items in the AAQZ website.
- Deploy `app/Support/SavedLogin.php`, `app/Http/Controllers/WidgetCalendarController.php` and the widget route additions in `routes/web.php`, then refresh Laravel's route cache. The existing `saved_logins` table, workspace tables and `ProjectController::showNote` must be deployed first. No new migration is introduced.
- New API: POST `/api/widget/resume`, `/forget`, `/logout`, GET `/workspace`, `/notes/{id}`. Mutations retain CSRF protection; resume is rate limited. Workspace/note reads enforce authentication and ownership.
- Verification: 20 widget/saved-login Laravel tests, 11 mocked IPC browser tests and two Rust tests (actual Windows credential persistence/removal and shortcut URL validation). Production account selection still requires the user's first sign-in.
## Production deployment on 25 September 2026

- Deployed 0.3.0 through Google Cloud browser SSH to `/var/www/laravel-app`. The server lacked the existing saved-login prerequisite, so only `2026_09_18_020000_create_saved_logins_table.php` was migrated and the SavedLogin service added before the widget deployment. No unrelated migrations were run.
- The widget deployment preserved unrelated routes, passed PHP syntax checks and rebuilt the route cache. Backup: `/var/backups/aaqz-widget-20260925T094859326972Z` (the SavedLogin service was newly added just before this backup).
- Live workspace requests without authentication and attempts to resume an invalid saved token both return 401. Authenticated real-account UI verification requires the user's first sign-in in 0.3.0.
- Installed 0.3.0 locally and published `widget-v0.3.0` as Latest. The publicly downloaded setup matches the local SHA-256, its signature verifies, and modified bytes are rejected.
