import { readFile, readdir, mkdir, copyFile, writeFile } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const root = fileURLToPath(new URL('../', import.meta.url));
const config = JSON.parse(await readFile(path.join(root, 'src-tauri/tauri.conf.json'), 'utf8'));
const pkg = JSON.parse(await readFile(path.join(root, 'package.json'), 'utf8'));
if (config.version !== pkg.version) throw new Error('Package and Tauri versions must match');
const cargo = await readFile(path.join(root, 'src-tauri/Cargo.toml'), 'utf8');
if (cargo.match(/^version\s*=\s*"([^"]+)"/m)?.[1] !== config.version) throw new Error('Cargo and Tauri versions must match');
const version = config.version;
const tag = `widget-v${version}`;
const endpoint = new URL(config.plugins.updater.endpoints[0]);
if (endpoint.origin !== 'https://github.com' || !endpoint.pathname.endsWith('/releases/latest/download/latest.json')) {
  throw new Error('Expected a GitHub Releases HTTPS endpoint');
}
const repository = endpoint.pathname.replace('/releases/latest/download/latest.json', '').slice(1);
const bundle = path.join(root, 'src-tauri/target/release/bundle/nsis');
const installers = (await readdir(bundle)).filter(name => name.endsWith(`_${version}_x64-setup.exe`));
if (installers.length !== 1) throw new Error('Expected exactly one x64 installer for this version');
const installer = installers[0];
// GitHub replaces spaces in uploaded release asset names with dots.
const assetName = installer.replaceAll(' ', '.');
const signature = (await readFile(path.join(bundle, `${installer}.sig`), 'utf8')).trim();
if (!signature) throw new Error('The updater signature is missing');
const output = path.join(root, 'release', tag);
await mkdir(output, { recursive: true });
await copyFile(path.join(bundle, installer), path.join(output, installer));
await copyFile(path.join(bundle, `${installer}.sig`), path.join(output, `${installer}.sig`));
await writeFile(path.join(output, 'latest.json'), JSON.stringify({
  version,
  notes: `AAQZ Calendar ${version}`,
  pub_date: new Date().toISOString(),
  platforms: {
    'windows-x86_64': {
      signature,
      url: `https://github.com/${repository}/releases/download/${tag}/${encodeURIComponent(assetName)}`,
    },
  },
}, null, 2) + '\n');
console.log(`Release files: ${output}`);
console.log(`Publish all three files in GitHub release ${tag} and mark it Latest.`);
