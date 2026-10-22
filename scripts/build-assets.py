"""Baut fertige Assets isoliert; --check prüft Quellstand und ausgelieferte Dateien."""
import argparse
import hashlib
import json
from pathlib import Path
import shutil
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[1]
PUBLIC = ROOT / 'src/Resources/public'
STAMP = PUBLIC / 'build-integrity.json'

def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()

def sources():
    paths = [ROOT / '.shopware-extension.yml', ROOT / 'scripts/build-assets.py']
    paths += list((ROOT / 'scripts/assets').glob('*.json'))
    paths += [p for p in (ROOT / 'src/Resources/app').rglob('*') if p.is_file() and 'dist' not in p.parts and 'node_modules' not in p.parts]
    return {p.relative_to(ROOT).as_posix(): digest(p) for p in sorted(paths)}

def outputs():
    return {p.relative_to(PUBLIC).as_posix(): digest(p) for folder in ['administration', 'storefront'] for p in sorted((PUBLIC / folder).rglob('*')) if p.is_file()}

def check():
    data = json.loads(STAMP.read_text())
    if data['sources'] != sources() or data['outputs'] != outputs():
        raise SystemExit('Assets fehlen oder sind veraltet. python3 scripts/build-assets.py ausführen.')
    manifest = json.loads((PUBLIC / 'administration/.vite/manifest.json').read_text())['main.js']
    for path in [manifest['file'], *manifest['css']]:
        if not (PUBLIC / 'administration' / path).is_file():
            raise SystemExit('Administration-Manifest verweist auf fehlende Dateien.')
    if not (PUBLIC / 'storefront/mgd-ai-labels.css').is_file():
        raise SystemExit('Fertige Storefront-Stile fehlen.')
    print('Asset-Quellstand, Prüfsummen und Manifest vollständig.')

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('--check', action='store_true')
parser.add_argument('--cli', default='shopware-cli')
args = parser.parse_args()
if args.check:
    check()
else:
    version = subprocess.check_output([args.cli, '--version'], text=True).strip()
    if version != 'shopware-cli version 0.15.12':
        raise SystemExit('Für reproduzierbare Releases wird shopware-cli 0.15.12 benötigt.')
    with tempfile.TemporaryDirectory(prefix='mgd-assets-') as directory, tempfile.TemporaryDirectory(prefix='mgd-build-tools-') as tools_directory:
        temp = Path(directory)
        for name in ['composer.json', '.shopware-extension.yml']:
            shutil.copy2(ROOT / name, temp / name)
        shutil.copytree(ROOT / 'src', temp / 'src', ignore=shutil.ignore_patterns('public', 'node_modules', 'dist'))
        subprocess.run([args.cli, 'extension', 'build', str(temp)], check=True)
        deps = Path(tools_directory) / 'deps'
        shutil.copytree(ROOT / 'scripts/assets', deps)
        subprocess.run(['npm', 'ci', '--ignore-scripts', '--no-audit', '--no-fund'], cwd=deps, check=True)
        css = temp / 'src/Resources/public/storefront/mgd-ai-labels.css'
        css.parent.mkdir(parents=True, exist_ok=True)
        subprocess.run([str(deps / 'node_modules/.bin/sass'), '--no-source-map', '--style=compressed', str(temp / 'src/Resources/app/storefront/src/scss/base.scss'), str(css)], check=True)
        for name in ['administration', 'storefront']:
            target = PUBLIC / name
            if target.is_symlink():
                raise SystemExit('Asset-Ziel darf kein Symlink sein.')
            if target.exists():
                shutil.rmtree(target)
            shutil.copytree(temp / 'src/Resources/public' / name, target)
    STAMP.write_text(json.dumps({'tool': version, 'sources': sources(), 'outputs': outputs()}, indent=2) + '\n')
    check()
