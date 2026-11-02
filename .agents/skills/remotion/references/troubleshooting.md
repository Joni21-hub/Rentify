# Remotion Troubleshooting, Performance & Best Practices

This guide diagnoses and resolves common issues encountered when building and rendering Remotion videos.

---

## 1. Common Issues & Fixes

### 1.1 "Error: Timeout while waiting for delayRender()"
* **Cause**: A component called `delayRender()`, but `continueRender(handle)` was never called or threw an unhandled error inside a promise.
* **Fix**: Always include a `.catch()` or `finally` block in any asynchronous operation to guarantee `continueRender(handle)` is invoked:
  ```tsx
  useEffect(() => {
    fetchData()
      .then(setData)
      .catch((err) => console.error('Fetch failed, rendering fallback:', err))
      .finally(() => continueRender(handle));
  }, [handle]);
  ```

### 1.2 Black or Missing Video Frames with `<Video />`
* **Cause**: HTML5 `<video>` tag struggles to seek to exact milliseconds during headless rendering in Chromium, especially at high concurrency.
* **Fix**: Replace `<Video src="..." />` with `<OffthreadVideo src="..." />`. OffthreadVideo decodes frames ahead of time in native background workers without relying on browser playback timing.

### 1.3 Missing Custom Web Fonts in Rendered Video
* **Cause**: Chromium starts capturing frames before Google Fonts or web fonts have fully loaded into the browser context.
* **Fix**: Use `@remotion/google-fonts` or preload fonts with `delayRender()` and `document.fonts.ready`:
  ```tsx
  import { useEffect, useState } from 'react';
  import { delayRender, continueRender } from 'remotion';

  export const FontLoader: React.FC = () => {
    const [handle] = useState(() => delayRender('Loading web fonts'));
    useEffect(() => {
      document.fonts.ready.then(() => continueRender(handle));
    }, [handle]);
    return null;
  };
  ```

### 1.4 Flickering / Frame Jumping on Dynamic Content
* **Cause**: Code used `Math.random()` or `Date.now()`. Since frames are rendered concurrently across multiple browser tabs, each tab calculates different values for the same frame index.
* **Fix**: Use deterministic hashing:
  ```tsx
  import { random } from 'remotion';

  // Seeded random number that stays identical for frame 45 across any thread:
  const seed = `particle-${index}-${frame}`;
  const xOffset = random(seed) * 100;
  ```

---

## 2. Rendering Optimization Guidelines

1. **Keep Assets Compressed**:
   * Downscale source images to match video resolution (e.g. do not load an 8000x6000 raw photo if the composition is 1080x1920).
   * Pre-compress background music to 128kbps / 192kbps AAC/MP3.
2. **Tune Concurrency**:
   * If rendering crashes with `Out of memory` (OOM), lower concurrency: `--concurrency=2` or `--concurrency=4`.
   * On high-spec servers, increase `--concurrency` up to CPU core count - 1.
3. **Use Webpack Caching**:
   * Pre-bundle once with `remotion bundle` before batch rendering many videos with different `--props`. Passing the bundle URL prevents recompilation on every run.
