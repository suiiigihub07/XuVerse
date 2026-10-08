"""Build a code-only ZIP; no database import, credentials, or runtime media."""
from pathlib import Path
from datetime import datetime, timezone
import subprocess
import zipfile

root = Path(__file__).resolve().parents[1]
tracked = subprocess.check_output(['git', 'ls-files', '-z'], cwd=root).decode().split('\0')
files = []
for name in filter(None, tracked):
    path = Path(name)
    if path.parts[0] in {'admin', 'includes', 'assets', 'content'}:
        files.append(path)
    elif name == '.htaccess' or (len(path.parts) == 1 and path.suffix == '.php'
                                  and not name.startswith('config.') and 'backup' not in name):
        files.append(path)
if not (root / 'vendor/autoload.php').is_file():
    raise SystemExit('Run composer install --no-dev --prefer-dist --optimize-autoloader first.')
files.extend(p.relative_to(root) for p in (root / 'vendor').rglob('*') if p.is_file())
# This security rule is the only permitted file inside the runtime upload directory.
files.append(Path('uploads/.htaccess'))
output = root / 'dist' / ('xuverse-code-' + datetime.now(timezone.utc).strftime('%Y%m%d-%H%M%S') + '.zip')
output.parent.mkdir(exist_ok=True)
with zipfile.ZipFile(output, 'w', zipfile.ZIP_DEFLATED) as archive:
    for path in sorted(set(files)):
        archive.write(root / path, path.as_posix())
print(output)
print('Code only. Extract over the existing site without deleting other files. No SQL import.')
