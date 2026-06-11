import { mkdir, readFile, writeFile } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '..');

const vendorThemePath = path.join(projectRoot, 'vendor', 'filament', 'filament', 'dist', 'theme.css');
const overridesPath = path.join(projectRoot, 'resources', 'css', 'filament', 'admin', 'theme.css');
const outputPath = path.join(projectRoot, 'public', 'css', 'filament', 'admin', 'theme.css');

const [vendorTheme, overrides] = await Promise.all([
    readFile(vendorThemePath, 'utf8'),
    readFile(overridesPath, 'utf8'),
]);

await mkdir(path.dirname(outputPath), { recursive: true });
await writeFile(outputPath, `${vendorTheme.trim()}\n\n${overrides.trim()}\n`, 'utf8');

console.log(`Built Filament theme: ${path.relative(projectRoot, outputPath)}`);