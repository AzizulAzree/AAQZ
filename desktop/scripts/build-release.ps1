$ErrorActionPreference = 'Stop'
Push-Location (Join-Path $PSScriptRoot '..')
$previousKey = $env:TAURI_SIGNING_PRIVATE_KEY
$previousJobs = $env:CARGO_BUILD_JOBS
try {
    if (!$env:TAURI_SIGNING_PRIVATE_KEY) {
        $signingKey = Join-Path $env:USERPROFILE '.tauri\aaqz-calendar.key'
        if (!(Test-Path -LiteralPath $signingKey)) { throw 'Updater signing key is missing. See desktop/README.md; do not generate a replacement for an existing release channel.' }
        $env:TAURI_SIGNING_PRIVATE_KEY = $signingKey
    }
    $env:CARGO_BUILD_JOBS = '2'
    & npm.cmd run tauri -- build --ci
    if ($LASTEXITCODE -ne 0) { throw 'Signed installer build failed' }
    & node scripts/prepare-release.mjs
    if ($LASTEXITCODE -ne 0) { throw 'Release manifest generation failed' }
    $config = Get-Content src-tauri/tauri.conf.json -Raw | ConvertFrom-Json
    $installer = Get-ChildItem -LiteralPath (Join-Path 'release' ('widget-v' + $config.version)) -Filter '*-setup.exe'
    if (@($installer).Count -ne 1) { throw 'Expected one installer to verify' }
    & cargo run --release --manifest-path src-tauri/Cargo.toml --example verify-release -- $installer.FullName
    if ($LASTEXITCODE -ne 0) { throw 'Installer signature verification failed; do not publish' }
} finally {
    $env:TAURI_SIGNING_PRIVATE_KEY = $previousKey
    $env:CARGO_BUILD_JOBS = $previousJobs
    Pop-Location
}
