# Remotion Core API & Primitives Reference

This document covers essential APIs and patterns for building programmatic videos with Remotion.

---

## 1. Compositions & Registration

### `<Composition />`
Defines a playable, renderable video unit registered inside the Remotion root.

```tsx
<Composition
  id="ProductReel"
  component={ProductReel}
  durationInFrames={360}     // 12 seconds at 30fps
  fps={30}                   // 24, 30, or 60
  width={1080}
  height={1920}              // 1080x1920 (9:16), 1920x1080 (16:9), 1080x1080 (1:1)
  schema={productSchema}     // Zod schema for props validation
  defaultProps={defaultData} // Fallback data in preview
  calculateMetadata={async ({ props, defaultProps, abortSignal }) => {
    // Dynamic metadata (e.g. query API or adjust duration based on audio length)
    return {
      durationInFrames: props.scenes.length * 90,
      props: { ...props },
    };
  }}
/>
```

### `<Still />`
Used for single-frame graphic exports (e.g. social banners, thumbnails, dynamic OG images):
```tsx
<Still
  id="ThumbnailExport"
  component={BannerGraphic}
  width={1200}
  height={630}
  schema={bannerSchema}
  defaultProps={defaultBannerProps}
/>
```

---

## 2. Essential Hooks

### `useCurrentFrame(): number`
Returns the active frame index as an integer from `0` to `durationInFrames - 1`.
```tsx
const frame = useCurrentFrame();
```

### `useVideoConfig(): VideoConfig`
Returns static configuration about the current composition:
```tsx
const { fps, width, height, durationInFrames, id } = useVideoConfig();
```

---

## 3. Asynchronous Resource Handling

If a component requires dynamic fonts, remote images, or fetch data during rendering, you MUST inform Remotion's renderer using `delayRender()` and `continueRender()`.

```tsx
import { useEffect, useState } from 'react';
import { delayRender, continueRender } from 'remotion';

export const DynamicDataComponent: React.FC = () => {
  const [data, setData] = useState<any>(null);
  const [handle] = useState(() => delayRender('Fetching remote product data'));

  useEffect(() => {
    fetch('https://api.rentify.id/products/top')
      .then((res) => res.json())
      .then((json) => {
        setData(json);
        continueRender(handle);
      })
      .catch((err) => {
        console.error('Failed to load data:', err);
        continueRender(handle); // Avoid stalling the render indefinitely
      });
  }, [handle]);

  if (!data) return null;
  return <div>{data.title}</div>;
};
```

---

## 4. Layout & Positioning

### `<AbsoluteFill />`
A pre-styled `div` with absolute positioning that stretches to fill 100% width and height of the video canvas:
```tsx
<AbsoluteFill style={{ backgroundColor: '#0f172a', justifyContent: 'center', alignItems: 'center' }}>
  <h1>Centered Content</h1>
</AbsoluteFill>
```

### `<Sequence />`
Controls when child elements enter and leave the timeline:
* `from`: Starting frame index relative to parent sequence.
* `durationInFrames`: Total frames the sequence is visible.
* `layout`: `'none'` (inherits normal DOM flow) or `'absolute-fill'` (default).

```tsx
<Sequence from={0} durationInFrames={90}>
  <IntroCard />
</Sequence>
<Sequence from={90} durationInFrames={120}>
  <ProductShowcase />
</Sequence>
<Sequence from={210} durationInFrames={90}>
  <CallToAction />
</Sequence>
```

### `<Series />`
Enables consecutive stacking without needing to calculate `from` offsets manually:
```tsx
<Series>
  <Series.Sequence durationInFrames={60}>
    <Intro />
  </Series.Sequence>
  <Series.Sequence durationInFrames={120}>
    <Body />
  </Series.Sequence>
  <Series.Sequence durationInFrames={60}>
    <Outro />
  </Series.Sequence>
</Series>
```

---

## 5. Static Assets (`staticFile`)

All public assets (images, fonts, audio files) placed in the `public/` directory must be referenced using `staticFile()`:

```tsx
import { staticFile, Img } from 'remotion';

// Resolves to http://localhost:3000/logos/rentify.svg in dev
// or file:///path/to/public/logos/rentify.svg in render bundle
<Img src={staticFile('logos/rentify.svg')} alt="Rentify Logo" />
```
