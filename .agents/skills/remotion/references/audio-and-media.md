# Remotion Audio, Waveforms & Media Integration

Multimedia synchronization is a core strength of Remotion. This reference covers audio processing, waveforms, and video clips.

---

## 1. Audio Playback & Volume Ducking

### Basic `<Audio />` Component
```tsx
import { Audio, staticFile } from 'remotion';

<Audio
  src={staticFile('music/background-track.mp3')}
  startFrom={30}       // Skip first 1 second (30 frames) of the source file
  endAt={330}          // Stop playback at 10 seconds (300 frames duration)
  playbackRate={1.0}   // Normal speed (can be 0.5 to 2.0)
  volume={0.7}         // 70% volume
/>
```

### Dynamic Audio Ducking (Lowering music during voiceover)
```tsx
import { Audio, staticFile, interpolate, useCurrentFrame } from 'remotion';

export const SoundtrackWithDucking: React.FC<{ voiceoverActive: boolean }> = ({ voiceoverActive }) => {
  const frame = useCurrentFrame();

  // Voiceover speaks between frames 60 and 240
  const isSpeaking = frame >= 60 && frame <= 240;
  
  const volume = interpolate(
    frame,
    [50, 60, 240, 250],
    [0.8, 0.2, 0.2, 0.8],
    { extrapolateLeft: 'clamp', extrapolateRight: 'clamp' }
  );

  return <Audio src={staticFile('music/bg.mp3')} volume={volume} />;
};
```

---

## 2. Audio Visualization & Waveforms (`@remotion/media-utils`)

Remotion provides `@remotion/media-utils` to analyze audio files and extract visualization data.

```tsx
import { useEffect, useState } from 'react';
import { useCurrentFrame, useVideoConfig, delayRender, continueRender, staticFile } from 'remotion';
import { getAudioData, visualizeAudio } from '@remotion/media-utils';

export const WaveformVisualizer: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();
  const [audioData, setAudioData] = useState<any>(null);
  const [handle] = useState(() => delayRender('Loading audio data for waveform'));

  const audioSrc = staticFile('music/track.mp3');

  useEffect(() => {
    getAudioData(audioSrc)
      .then((data) => {
        setAudioData(data);
        continueRender(handle);
      })
      .catch((err) => {
        console.error(err);
        continueRender(handle);
      });
  }, [audioSrc, handle]);

  if (!audioData) return null;

  // Extract 32 frequency visualization bars for the current frame
  const visualization = visualizeAudio({
    fps,
    frame,
    audioData,
    numberOfSamples: 32,
  });

  return (
    <div style={{ display: 'flex', gap: 6, alignItems: 'flex-end', height: 120 }}>
      {visualization.map((amplitude, i) => (
        <div
          key={i}
          style={{
            width: 8,
            height: `${Math.max(8, amplitude * 120)}px`,
            backgroundColor: '#38bdf8',
            borderRadius: 4,
          }}
        />
      ))}
    </div>
  );
};
```

---

## 3. High Performance Video Clips (`<OffthreadVideo />`)

When embedding external MP4 clips inside a Remotion composition:

```tsx
import { OffthreadVideo, staticFile } from 'remotion';

export const ClipOverlay: React.FC = () => {
  return (
    <OffthreadVideo
      src={staticFile('videos/product-demo.mp4')}
      startFrom={15}
      endAt={165}
      volume={0} // Muted background footage
      style={{
        width: '100%',
        height: '100%',
        objectFit: 'cover',
      }}
    />
  );
};
```

> [!IMPORTANT]
> `<OffthreadVideo />` renders video frames synchronously in Node/Chromium worker processes during CLI rendering, completely bypassing HTML5 `<video>` decoding bottlenecks and stutter. Always use `<OffthreadVideo />` for final exports.
