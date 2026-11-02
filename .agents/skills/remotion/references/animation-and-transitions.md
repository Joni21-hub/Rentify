# Remotion Animation, Curves & Transitions Reference

Smooth, professional motion graphics in Remotion are achieved by coupling mathematical functions with `useCurrentFrame()`.

---

## 1. Physics-Based Motion (`spring`)

The `spring()` utility calculates realistic physics-based dampening, mass, and stiffness.

```tsx
import { spring, useCurrentFrame, useVideoConfig } from 'remotion';

const frame = useCurrentFrame();
const { fps } = useVideoConfig();

const progress = spring({
  frame,
  fps,
  delay: 10,        // Wait 10 frames before starting
  from: 0,
  to: 1,
  config: {
    damping: 12,    // Lower = more oscillation/bounce
    mass: 0.8,      // Inertia (higher = slower acceleration)
    stiffness: 120, // Spring tension (higher = snappier)
    overshootClamping: false, // If true, cuts off bounce when hitting `to`
  },
});
```

### Common Spring Presets
* **Snappy Pop-in (Badges, Buttons)**:
  `{ damping: 10, mass: 0.5, stiffness: 150 }`
* **Smooth Gentle Drift (Hero Cards, Slide-ins)**:
  `{ damping: 18, mass: 1.0, stiffness: 80 }`
* **Exaggerated Bouncy (Stickers, Emojis)**:
  `{ damping: 8, mass: 0.4, stiffness: 200 }`
* **No Overshoot (Solid Progress Bars)**:
  `{ damping: 20, mass: 1, stiffness: 100, overshootClamping: true }`

---

## 2. Linear & Bezier Interpolation (`interpolate`)

`interpolate()` maps an input frame interval to an output range.

```tsx
import { interpolate, Easing, useCurrentFrame } from 'remotion';

const frame = useCurrentFrame();

// Fade in from frame 0-15, stay visible, fade out from 75-90
const opacity = interpolate(
  frame,
  [0, 15, 75, 90],
  [0, 1, 1, 0],
  {
    extrapolateLeft: 'clamp',
    extrapolateRight: 'clamp',
    easing: Easing.out(Easing.cubic),
  }
);
```

### Easing Curves
Available via `Easing`:
* `Easing.linear`
* `Easing.in(Easing.quad)` / `Easing.out(Easing.quad)` / `Easing.inOut(Easing.quad)`
* `Easing.out(Easing.cubic)`
* `Easing.bezier(x1, y1, x2, y2)` — custom CSS cubic-bezier curves (e.g. `Easing.bezier(0.16, 1, 0.3, 1)` for Apple-style snappy ease-out).

---

## 3. Transition Series (`@remotion/transitions`)

Package: `@remotion/transitions`

Enables visual crossfades, slides, wipes, and 3D flips between sequences.

```tsx
import { TransitionSeries, linearTiming, springTiming } from '@remotion/transitions';
import { fade } from '@remotion/transitions/fade';
import { slide } from '@remotion/transitions/slide';
import { wipe } from '@remotion/transitions/wipe';
import { flip } from '@remotion/transitions/flip';

<TransitionSeries>
  {/* Scene 1: 90 frames */}
  <TransitionSeries.Sequence durationInFrames={90}>
    <HeroScene />
  </TransitionSeries.Sequence>

  {/* Transition: 15 frames overlapping */}
  <TransitionSeries.Transition
    presentation={slide({ direction: 'from-right' })}
    timing={springTiming({ config: { damping: 14 } })}
  />

  {/* Scene 2: 120 frames */}
  <TransitionSeries.Sequence durationInFrames={120}>
    <ProductScene />
  </TransitionSeries.Sequence>

  {/* Transition: Fade */}
  <TransitionSeries.Transition
    presentation={fade()}
    timing={linearTiming({ durationInFrames: 20 })}
  />

  {/* Scene 3: 90 frames */}
  <TransitionSeries.Sequence durationInFrames={90}>
    <OutroScene />
  </TransitionSeries.Sequence>
</TransitionSeries>
```

---

## 4. SVG Shapes & Path Evolution

Remotion provides helper packages `@remotion/paths` and `@remotion/shapes` for vector animations:

```tsx
import { evolvePath } from '@remotion/paths';

const pathString = 'M 10 80 Q 95 10 180 80';
const evolution = evolvePath(progress, pathString);

return (
  <svg viewBox="0 0 200 100">
    <path
      d={pathString}
      stroke="#38bdf8"
      strokeWidth={4}
      fill="none"
      strokeDasharray={evolution.strokeDasharray}
      strokeDashoffset={evolution.strokeDashoffset}
    />
  </svg>
);
```
