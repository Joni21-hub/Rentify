---
name: remotion
description: Comprehensive framework and operational toolkit for programmatic video creation, motion graphics, dynamic catalog reels, and video automation using Remotion (React for video). Use this skill whenever creating, modifying, animating, rendering, or troubleshooting Remotion video compositions, configuring SSR/CLI pipelines, or building programmatic marketing video systems.
---

# Remotion Video Automation Skill

Remotion is an open-source framework ([github.com/remotion-dev/remotion](https://github.com/remotion-dev/remotion)) that enables developers to create MP4, WebM, GIF, and still graphic videos programmatically using standard React, CSS, TypeScript, and modern web tools.

This skill equips the agent with end-to-end capabilities: designing frame-accurate animations, dynamic parameter-driven compositions, audio/waveform integrations, transitions, and automated batch rendering pipelines.

---

## 1. Core Philosophy & Determinism Rule

> [!IMPORTANT]
> **Video as a Pure Function of Time**:
> A Remotion composition must be 100% deterministic:
> `Video = f(frame, fps, props)`
>
> 1. **Never use non-deterministic functions** like `Date.now()`, `Math.random()`, or wall-clock timers directly in components. Use `random('seed')` from `remotion` instead.
> 2. **Never initiate unhandled asynchronous state** during rendering. If data or assets must load asynchronously, wrap them with `delayRender()` and `continueRender()`.
> 3. **Never rely on CSS transitions or keyframes** for timeline-critical animations (`transition: all 0.3s` will desync across render threads). Drive all visual state through `frame` and math interpolations (`spring`, `interpolate`).

---

## 2. Directory & Component Structure

A standard Remotion video application layout:

```text
vid-remotion/
├── src/
│   ├── index.ts              # Entry point calling registerRoot(Root)
│   ├── Root.tsx              # Root component declaring <Composition> & <Still>
│   ├── components/           # Reusable visual layers, text, badges, cards
│   ├── compositions/         # Full video templates (Brand, Product, Flash)
│   ├── styles/               # CSS / Tailwind setup
│   └── types/                # Zod schemas and TypeScript interfaces
├── public/                   # Static media (mp3, png, svg, fonts) accessed via staticFile()
├── remotion.config.ts        # CLI & bundler settings (pixel format, codecs, concurrency)
└── package.json
```

---

## 3. Essential Primitives & Hooks

### 3.1 Compositions & Stills (`Root.tsx`)
```tsx
import { Composition, Still } from 'remotion';
import { MainVideo, videoSchema, defaultVideoProps } from './compositions/MainVideo';

export const RemotionRoot: React.FC = () => {
  return (
    <>
      <Composition
        id="MainVideo"
        component={MainVideo}
        durationInFrames={300} // 10s at 30fps
        fps={30}
        width={1080}
        height={1920} // 9:16 vertical story/reel
        schema={videoSchema}
        defaultProps={defaultVideoProps}
        calculateMetadata={async ({ defaultProps, props, abortSignal }) => {
          // Dynamic overrides for duration, fps, or dimensions based on props
          return {
            durationInFrames: props.scenes.length * 90,
            props,
          };
        }}
      />
    </>
  );
};
```

### 3.2 Time & Layout Primitives
* **`useCurrentFrame()`**: Returns current integer frame (`0` to `durationInFrames - 1`).
* **`useVideoConfig()`**: Returns `{ fps, width, height, durationInFrames, id }`.
* **`<AbsoluteFill>`**: Full-bleed absolute container (`top: 0, left: 0, right: 0, bottom: 0`).
* **`<Sequence from={30} durationInFrames={60}>`**: Shifts local time so child frames start at 0 between frame 30 and 90.
* **`<Series>`**: Automatically sequences consecutive children without manual frame arithmetic:
  ```tsx
  <Series>
    <Series.Sequence durationInFrames={60}><SceneA /></Series.Sequence>
    <Series.Sequence durationInFrames={90}><SceneB /></Series.Sequence>
  </Series>
  ```

---

## 4. Animation Systems

### 4.1 Physics-Based Spring (`spring()`)
Springs provide natural, bouncy motion without robotic linear curves:
```tsx
import { spring, useCurrentFrame, useVideoConfig } from 'remotion';

const frame = useCurrentFrame();
const { fps } = useVideoConfig();

const scale = spring({
  frame,
  fps,
  config: {
    damping: 12,    // Lower = bouncier, Higher = stiffer
    mass: 0.6,      // Heavier = slower start & overshoot
    stiffness: 100, // Speed of recovery
  },
  from: 0,
  to: 1,
});

return <div style={{ transform: `scale(${scale})` }}>Headline</div>;
```

### 4.2 Mathematical Interpolation (`interpolate()`)
Maps frame numbers smoothly to numeric values:
```tsx
import { interpolate, Easing, useCurrentFrame } from 'remotion';

const frame = useCurrentFrame();

const opacity = interpolate(frame, [0, 20, 80, 100], [0, 1, 1, 0], {
  extrapolateLeft: 'clamp',
  extrapolateRight: 'clamp',
  easing: Easing.bezier(0.25, 0.1, 0.25, 1),
});
```

### 4.3 Scene Transitions (`@remotion/transitions`)
```tsx
import { TransitionSeries, linearTiming } from '@remotion/transitions';
import { fade } from '@remotion/transitions/fade';
import { slide } from '@remotion/transitions/slide';

<TransitionSeries>
  <TransitionSeries.Sequence durationInFrames={60}>
    <Intro />
  </TransitionSeries.Sequence>
  <TransitionSeries.Transition
    presentation={slide({ direction: 'from-right' })}
    timing={linearTiming({ durationInFrames: 15 })}
  />
  <TransitionSeries.Sequence durationInFrames={75}>
    <MainContent />
  </TransitionSeries.Sequence>
</TransitionSeries>
```

---

## 5. Audio, Video & Assets

### 5.1 Assets & Audio
* Put static assets in `public/` and load with `staticFile('audio.mp3')`.
* Use `<Audio />` with volume interpolation for fade-in / fade-out:
  ```tsx
  import { Audio, staticFile, interpolate, useCurrentFrame } from 'remotion';

  const frame = useCurrentFrame();
  const volume = interpolate(frame, [0, 30, 270, 300], [0, 0.8, 0.8, 0], {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
  });

  <Audio src={staticFile('music/upbeat.mp3')} volume={volume} />
  ```

### 5.2 Video Clips (`<OffthreadVideo />`)
> [!TIP]
> Always prefer `<OffthreadVideo />` over standard `<Video />` for rendered output. It extracts frames in parallel off the main DOM thread, avoiding black frames and stuttering during CLI render.

---

## 6. CLI Execution & Rendering Cheatsheet

| Task | Command |
| :--- | :--- |
| **Start Live Dev Studio** | `npx remotion studio` |
| **Render Single Frame (Thumbnail)** | `npx remotion still <CompositionId> out/thumb.png --frame=60` |
| **Render Video (MP4)** | `npx remotion render <CompositionId> out/video.mp4` |
| **Pass Dynamic JSON Props** | `npx remotion render <CompositionId> out/custom.mp4 --props='{"title":"Promo Spesial"}'` |
| **Set Concurrency (CPU Threads)** | `npx remotion render <CompositionId> out/video.mp4 --concurrency=8` |
| **Bundle Production Webpack** | `npx remotion bundle src/index.ts` |
| **Upgrade All Remotion Packages** | `npx remotion upgrade` |

---

## 7. Sub-References & Guides

Read these modular documents for specialized implementations:

1. [Core API & Primitives Reference](./references/core-api.md): Detailed parameter lists for hooks, delayRender, and metadata calculation.
2. [Animation, Curves & Transitions](./references/animation-and-transitions.md): Presets for kinetic typography, spring configs, and path animations.
3. [Audio, Waveforms & Captions](./references/audio-and-media.md): Audio visualization with `@remotion/media-utils` and auto-captions with Whisper.
4. [CLI & Server-Side Rendering (Node.js API)](./references/cli-and-rendering.md): Automating batch video generation via Node.js `@remotion/renderer`.
5. [Troubleshooting & Performance](./references/troubleshooting.md): Solving memory leaks, font rendering, and async race conditions.

---

## 8. Ready-to-Use Examples

* [DynamicCatalogReel.tsx.example](./examples/DynamicCatalogReel.tsx.example): Vertical promo reel with animated badges and pricing.
* [MotionGraphicsTitle.tsx.example](./examples/MotionGraphicsTitle.tsx.example): Kinetic headline animation.
* [render-script.mjs](./examples/render-script.mjs): Standalone Node.js SSR batch render script.
