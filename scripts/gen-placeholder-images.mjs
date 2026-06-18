// One-shot generator for local event placeholder images.
// Produces themed SVGs under public/images/events/ used by App\Support\EventImages.
import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const outDir = resolve(dirname(fileURLToPath(import.meta.url)), '..', 'public', 'images', 'events');
mkdirSync(outDir, { recursive: true });

const W = 800;
const H = 500;

// category -> [from, to] gradient colors
const categories = {
    concert: ['#7c3aed', '#db2777'],
    conference: ['#2563eb', '#0891b2'],
    meetup: ['#059669', '#65a30d'],
    workshop: ['#ea580c', '#d97706'],
    festival: ['#db2777', '#f59e0b'],
    sports: ['#16a34a', '#0d9488'],
    networking: ['#4f46e5', '#7c3aed'],
    exhibition: ['#9333ea', '#c026d3'],
};

const textures = {
    'texture-1': ['#1e293b', '#0f172a'],
    'texture-2': ['#312e81', '#1e1b4b'],
    'texture-3': ['#134e4a', '#042f2e'],
    'texture-4': ['#7c2d12', '#431407'],
};

function dots() {
    let out = '';
    for (let i = 0; i < 28; i++) {
        const cx = (i * 137) % W;
        const cy = (i * 89) % H;
        const r = 6 + ((i * 13) % 26);
        const o = 0.04 + ((i % 5) * 0.02);
        out += `<circle cx="${cx}" cy="${cy}" r="${r}" fill="#ffffff" opacity="${o.toFixed(2)}"/>`;
    }
    return out;
}

function svg(id, [from, to], label) {
    return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}" role="img" aria-label="${label}">
  <defs>
    <linearGradient id="g-${id}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="${from}"/>
      <stop offset="1" stop-color="${to}"/>
    </linearGradient>
  </defs>
  <rect width="${W}" height="${H}" fill="url(#g-${id})"/>
  ${dots()}
  <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"
        font-family="system-ui, -apple-system, Segoe UI, sans-serif"
        font-size="46" font-weight="700" fill="#ffffff" opacity="0.92"
        letter-spacing="2" style="text-transform:uppercase">${label}</text>
</svg>
`;
}

let count = 0;
for (const [name, colors] of Object.entries(categories)) {
    writeFileSync(resolve(outDir, `${name}.svg`), svg(name, colors, name));
    count++;
}
for (const [name, colors] of Object.entries(textures)) {
    writeFileSync(resolve(outDir, `${name}.svg`), svg(name, colors, 'Event'));
    count++;
}

console.log(`Wrote ${count} placeholder images to ${outDir}`);
