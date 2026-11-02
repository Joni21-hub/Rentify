#!/usr/bin/env node

/**
 * Batch video renderer CLI helper
 * Reads a JSON file of items and triggers `remotion render` with custom props
 * Usage: node batch-render.mjs <compositionId> <items.json>
 */

import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';

const [,, compId, jsonFile] = process.argv;

if (!compId || !jsonFile) {
  console.log('Usage: node batch-render.mjs <CompositionId> <items.json>');
  console.log('Example: node batch-render.mjs ProductCatalogPromo catalog-items.json');
  process.exit(1);
}

const resolvedJson = path.resolve(process.cwd(), jsonFile);
if (!fs.existsSync(resolvedJson)) {
  console.error(`Error: File not found at ${resolvedJson}`);
  process.exit(1);
}

const raw = fs.readFileSync(resolvedJson, 'utf-8');
const items = JSON.parse(raw);

if (!Array.isArray(items)) {
  console.error('Error: JSON file must contain an array of item props.');
  process.exit(1);
}

console.log(`Starting batch render for ${items.length} items using composition: ${compId}...`);

const outDir = path.resolve(process.cwd(), 'out');
if (!fs.existsSync(outDir)) {
  fs.mkdirSync(outDir, { recursive: true });
}

items.forEach((item, index) => {
  const slug = item.slug || `video-${index + 1}`;
  const outPath = path.join(outDir, `${slug}.mp4`);
  const propsArg = JSON.stringify(item);

  console.log(`\n[${index + 1}/${items.length}] Rendering ${slug} -> ${outPath}...`);
  try {
    execSync(`npx remotion render ${compId} "${outPath}" --props='${propsArg}'`, {
      stdio: 'inherit',
    });
    console.log(`✅ [${index + 1}/${items.length}] Completed: ${outPath}`);
  } catch (err) {
    console.error(`❌ Failed to render ${slug}:`, err.message);
  }
});

console.log('\n🎉 Batch render processing completed.');
