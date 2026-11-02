# Remotion CLI, Server-Side Rendering (SSR) & Automation

This reference details CLI flags, configuration files, and the programmatic Node.js API (`@remotion/renderer`) for scalable video rendering pipelines.

---

## 1. CLI Command Reference

### Development
```bash
# Launch interactive Remotion Studio on port 3000
npx remotion studio

# Specify custom entry or port
npx remotion studio src/index.ts --port=3001
```

### Static Rendering (MP4, WebM, GIF, PNG)
```bash
# Render video composition to MP4
npx remotion render <CompositionId> out/video.mp4

# Render with custom dynamic props JSON
npx remotion render ProductReel out/custom.mp4 --props='{"productName":"Camera Sony A7IV","price":150000}'

# Render single still frame (thumbnail/poster)
npx remotion still ProductReel out/poster.png --frame=60

# Render high-quality animated GIF
npx remotion render FlashDeal out/deal.gif --codec=gif --fps=15

# Render transparent ProRes 4444 video
npx remotion render LowerThird out/overlay.mov --codec=prores --prores-profile=4444 --pixel-format=yuva444p10le
```

### Performance & Concurrency Flags
* `--concurrency=<number>`: Number of parallel browser tabs to render frames (e.g. `--concurrency=8`). Default is 50% of CPU cores.
* `--gl=angle` / `--gl=swiftshader`: Specifies OpenGL backend for GPU rendering.
* `--crf=<0-51>`: Constant Rate Factor for H.264 (Default: 18. Lower = higher quality, larger file).
* `--muted`: Skips audio processing if no sound is needed (faster render).

---

## 2. Configuration (`remotion.config.ts`)

Configure persistent rendering behaviors:

```typescript
import { Config } from '@remotion/cli/config';

// Output pixel format
Config.setPixelFormat('yuv420p');

// Default video codec
Config.setCodec('h264');

// Concurrency tuning (number of browser threads)
Config.setConcurrency(6);

// Overwrite existing files without asking
Config.setOverwriteOutput(true);

// Enable Chromium hardware acceleration if supported
Config.setChromiumOpenGlRenderer('angle');
```

---

## 3. Programmatic Node.js SSR (`@remotion/renderer`)

When building an API server, background worker, or batch generation script, use `@remotion/renderer`:

```typescript
import path from 'path';
import { bundle } from '@remotion/bundler';
import { renderMedia, selectComposition } from '@remotion/renderer';

async function generateVideo() {
  const entryPoint = path.resolve('./src/index.ts');
  console.log('Bundling Remotion project...');

  // 1. Bundle Remotion project using Webpack
  const bundleLocation = await bundle({
    entryPoint,
    webpackOverride: (config) => config,
  });

  // 2. Select composition & pass runtime props
  const inputProps = {
    title: 'Flash Sale Sewa Kamera!',
    discountPercent: 40,
    vendorName: 'Rentify Official',
  };

  const composition = await selectComposition({
    serveUrl: bundleLocation,
    id: 'ProductCatalogPromo',
    inputProps,
  });

  // 3. Render video to MP4
  const outputLocation = path.resolve('./out/generated-promo.mp4');
  console.log(`Rendering ${composition.durationInFrames} frames...`);

  await renderMedia({
    composition,
    serveUrl: bundleLocation,
    codec: 'h264',
    outputLocation,
    inputProps,
    concurrency: 4,
    onProgress: ({ renderedFrames, totalFrames, progress }) => {
      console.log(`Render progress: ${(progress * 100).toFixed(1)}% (${renderedFrames}/${totalFrames})`);
    },
  });

  console.log(`Success! Video rendered at: ${outputLocation}`);
}

generateVideo().catch(console.error);
```
