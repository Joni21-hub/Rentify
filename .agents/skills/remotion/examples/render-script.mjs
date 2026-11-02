/**
 * Server-Side Batch Video Renderer for Remotion
 * Usage: node render-script.mjs
 */

import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

async function main() {
  // Dynamically import remotion packages (must be installed in the project)
  const { bundle } = await import('@remotion/bundler');
  const { renderMedia, selectComposition } = await import('@remotion/renderer');

  const projectRoot = path.resolve(__dirname, '../../..');
  const vidDir = path.join(projectRoot, 'vid-remotion');
  const entryPoint = path.join(vidDir, 'src/index.ts');
  const outDir = path.join(vidDir, 'out');

  if (!fs.existsSync(outDir)) {
    fs.mkdirSync(outDir, { recursive: true });
  }

  console.log('🚀 1. Bundling Remotion project...');
  const bundleLocation = await bundle({
    entryPoint,
    webpackOverride: (config) => config,
  });

  const compositionsToRender = [
    {
      id: 'RentifyBrandPromo',
      outFile: 'RentifyBrandPromo.mp4',
      props: {},
    },
    {
      id: 'ProductCatalogPromo',
      outFile: 'SonyA7IV-Catalog.mp4',
      props: {
        productName: 'Sony Alpha A7 IV',
        category: 'Kamera & Lensa',
        rentalPricePerDay: 250000,
        retailPrice: 38000000,
        vendorName: 'Nusantara Lens Official',
      },
    },
  ];

  for (const item of compositionsToRender) {
    console.log(`\n🎬 2. Preparing composition: ${item.id}...`);

    const composition = await selectComposition({
      serveUrl: bundleLocation,
      id: item.id,
      inputProps: item.props,
    });

    const outputLocation = path.join(outDir, item.outFile);
    console.log(`⚡ 3. Rendering to ${outputLocation} (${composition.durationInFrames} frames)...`);

    await renderMedia({
      composition,
      serveUrl: bundleLocation,
      codec: 'h264',
      outputLocation,
      inputProps: item.props,
      concurrency: 4,
      onProgress: ({ renderedFrames, totalFrames, progress }) => {
        const pct = (progress * 100).toFixed(1);
        process.stdout.write(`\r   Progress: [${pct}%] Frame ${renderedFrames}/${totalFrames}`);
      },
    });

    console.log(`\n✅ Render complete: ${item.outFile}`);
  }

  console.log('\n🎉 All video renders finished successfully!');
}

main().catch((err) => {
  console.error('\n❌ Render failed with error:', err);
  process.exit(1);
});
