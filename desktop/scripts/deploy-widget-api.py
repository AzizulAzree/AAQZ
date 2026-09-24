"""Run on the Laravel VM from the extracted widget-server-update archive.

sudo python3 deploy-widget-api.py /var/www/laravel-app
Only the widget controller, SavedLogin service and widget route block change.
Existing files are backed up and restored if route compilation fails.
"""
import datetime
import os
from pathlib import Path
import re
import shutil
import subprocess
import sys
import tempfile

ROUTE_BLOCK = re.compile(r"^Route::prefix\('api/widget'\).*?^\}\);", re.M | re.S)


def replace_routes(current, replacement):
    if len(ROUTE_BLOCK.findall(current)) != 1 or len(ROUTE_BLOCK.findall(replacement)) != 1:
        raise RuntimeError('Expected exactly one widget route group; inspect routes before deploying.')
    return ROUTE_BLOCK.sub(lambda _: replacement.strip(), current, count=1)


def main():
    root = Path(sys.argv[1]).resolve()
    source = Path(__file__).resolve().parent
    if root != Path('/var/www/laravel-app') or not (root / 'artisan').is_file():
        raise RuntimeError('Expected the existing /var/www/laravel-app deployment.')
    if 'function showNote(' not in (root / 'app/Http/Controllers/ProjectController.php').read_text():
        raise RuntimeError('Deploy the existing workspace note reader first.')
    # Read-only schema check; this deploy never creates tables or runs migrations.
    probe = r'''require 'vendor/autoload.php'; $app = require 'bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    foreach (['saved_logins','workspaces','workspace_nodes','sticky_notes'] as $table) {
        if (!Illuminate\Support\Facades\Schema::hasTable($table)) { fwrite(STDERR, "Missing required table: $table\n"); exit(1); }
    }'''
    subprocess.run(['php', '-r', probe], cwd=root, check=True)
    names = ['app/Http/Controllers/WidgetCalendarController.php', 'app/Support/SavedLogin.php']
    contents = {name: (source / name).read_text() for name in names}
    contents['routes/web.php'] = replace_routes((root / 'routes/web.php').read_text(), (source / 'widget-routes.txt').read_text())
    with tempfile.TemporaryDirectory(prefix='aaqz-widget-check-') as temp:
        for index, content in enumerate(contents.values()):
            staged = Path(temp) / f'{index}.php'
            staged.write_text(content)
            subprocess.run(['php', '-l', str(staged)], check=True)
    backup = Path('/var/backups') / ('aaqz-widget-' + datetime.datetime.now(datetime.timezone.utc).strftime('%Y%m%dT%H%M%S%fZ'))
    backup.mkdir(mode=0o700)
    for name in contents:
        dest = backup / name
        dest.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(root / name, dest)
    try:
        for name, content in contents.items():
            target = root / name
            stat = target.stat()
            fd, temporary = tempfile.mkstemp(prefix='.widget-', dir=target.parent)
            try:
                with os.fdopen(fd, 'w') as handle:
                    handle.write(content)
                os.chmod(temporary, stat.st_mode)
                os.chown(temporary, stat.st_uid, stat.st_gid)
                os.replace(temporary, target)
            finally:
                if os.path.exists(temporary):
                    os.unlink(temporary)
        subprocess.run(['sudo', '-u', 'www-data', 'php', 'artisan', 'route:cache'], cwd=root, check=True)
    except Exception:
        for name in contents:
            shutil.copy2(backup / name, root / name)
        subprocess.run(['sudo', '-u', 'www-data', 'php', 'artisan', 'route:cache'], cwd=root, check=False)
        raise
    print(f'Widget API deployed. Backup: {backup}')
    subprocess.run(['php', 'artisan', 'route:list', '--path=api/widget'], cwd=root, check=True)


if __name__ == '__main__':
    main()
