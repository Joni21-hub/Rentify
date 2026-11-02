import React from "react";
import {
  AbsoluteFill,
  interpolate,
  Sequence,
  spring,
  useCurrentFrame,
  useVideoConfig,
  staticFile,
  Audio,
} from "remotion";
import {
  Search,
  ShieldCheck,
  CreditCard,
  PackageCheck,
  Sparkles,
  ArrowRight,
  Clock,
  Compass,
} from "lucide-react";
import { RENTIFY_THEME } from "./theme";

export const HowItWorksPromo: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const audioVolume = interpolate(
    frame,
    [0, 20, 330, 360],
    [0, 0.75, 0.75, 0],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  const orb1Y = Math.sin(frame / 22) * 40;
  const orb2Y = Math.cos(frame / 26) * 45;

  return (
    <AbsoluteFill
      style={{
        background: RENTIFY_THEME.colors.bgGradient,
        fontFamily: RENTIFY_THEME.typography.fontFamily,
        color: RENTIFY_THEME.colors.textWhite,
        overflow: "hidden",
      }}
    >
      <Audio src={staticFile("assets/bgm-promo.mp3")} volume={audioVolume} />

      {/* Cyber Ambient Glows */}
      <div
        style={{
          position: "absolute",
          top: -100 + orb1Y,
          left: -100,
          width: 800,
          height: 800,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(0, 242, 254, 0.25) 0%, transparent 70%)",
          filter: "blur(90px)",
          pointerEvents: "none",
        }}
      />
      <div
        style={{
          position: "absolute",
          bottom: -150 + orb2Y,
          right: -100,
          width: 850,
          height: 850,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, transparent 70%)",
          filter: "blur(90px)",
          pointerEvents: "none",
        }}
      />

      {/* Top Header */}
      <div
        style={{
          position: "absolute",
          top: 60,
          left: 50,
          right: 50,
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          zIndex: 50,
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 14 }}>
          <div
            style={{
              width: 54,
              height: 54,
              borderRadius: 16,
              background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              boxShadow: "0 0 25px rgba(0, 242, 254, 0.5)",
            }}
          >
            <Sparkles size={30} color="#ffffff" />
          </div>
          <div>
            <h1
              style={{
                fontSize: 38,
                fontWeight: 900,
                color: "#ffffff",
                margin: 0,
                lineHeight: 1,
              }}
            >
              Rentify
            </h1>
            <span style={{ fontSize: 16, color: "#94a3b8", fontWeight: 600 }}>
              rentify21.my.id
            </span>
          </div>
        </div>

        <div
          style={{
            background: "rgba(15, 23, 42, 0.8)",
            border: "1px solid rgba(255, 255, 255, 0.15)",
            padding: "8px 20px",
            borderRadius: 999,
            fontSize: 18,
            fontWeight: 800,
            color: "#38bdf8",
            display: "flex",
            alignItems: "center",
            gap: 8,
          }}
        >
          <Clock size={20} color="#38bdf8" />
          <span>Panduan Kilat Sewa</span>
        </div>
      </div>

      {/* ================= SCENE 1: HOOK (0 - 80) ================= */}
      <Sequence from={0} durationInFrames={80}>
        <SceneIntroHook fps={fps} />
      </Sequence>

      {/* ================= SCENE 2: 4 STEPS TIMELINE (75 - 280) ================= */}
      <Sequence from={75} durationInFrames={205}>
        <SceneFourSteps fps={fps} />
      </Sequence>

      {/* ================= SCENE 3: OUTRO CTA (275 - 360) ================= */}
      <Sequence from={275} durationInFrames={85}>
        <SceneHowItWorksOutro fps={fps} />
      </Sequence>
    </AbsoluteFill>
  );
};

const SceneIntroHook: React.FC<{ fps: number }> = ({ fps }) => {
  const frame = useCurrentFrame();
  const enter = spring({ frame, fps, config: { damping: 14, stiffness: 85 } });

  return (
    <AbsoluteFill
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        padding: "0 50px",
        textAlign: "center",
        opacity: interpolate(frame, [65, 80], [1, 0], {
          extrapolateRight: "clamp",
        }),
      }}
    >
      <div
        style={{
          transform: `scale(${enter})`,
          background: "rgba(0, 242, 254, 0.18)",
          border: "1.5px solid rgba(0, 242, 254, 0.45)",
          color: "#38bdf8",
          padding: "12px 28px",
          borderRadius: 999,
          fontSize: 22,
          fontWeight: 800,
          marginBottom: 24,
          boxShadow: "0 0 25px rgba(0, 242, 254, 0.25)",
          display: "flex",
          alignItems: "center",
          gap: 10,
        }}
      >
        <Compass size={24} />
        <span>BELUM PERNAH SEWA DI RENTIFY?</span>
      </div>

      <h2
        style={{
          transform: `translateY(${interpolate(enter, [0, 1], [50, 0])}px)`,
          fontSize: 62,
          fontWeight: 900,
          lineHeight: 1.15,
          color: "#ffffff",
          margin: "0 0 20px 0",
          letterSpacing: -1.8,
        }}
      >
        Cara Mudah Sewa Alat,
        <br />
        <span
          style={{
            background: "linear-gradient(135deg, #00f2fe 0%, #10b981 100%)",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
            filter: "drop-shadow(0 0 25px rgba(0, 242, 254, 0.4))",
          }}
        >
          Hanya Dalam 4 Langkah!
        </span>
      </h2>

      <p
        style={{
          fontSize: 26,
          color: "#cbd5e1",
          maxWidth: 800,
          lineHeight: 1.4,
          margin: 0,
          fontWeight: 600,
        }}
      >
        Tanpa titip KTP fisik, tanpa jaminan BPKB, dan 100% aman bergaransi!
      </p>
    </AbsoluteFill>
  );
};

const STEPS = [
  {
    num: "01",
    title: "Cari & Booking di rentify21.my.id",
    desc: "Pilih gear kamera, drone, atau outdoor dekat kotamu dengan rating terpercaya.",
    icon: Search,
    color: "#00f2fe",
  },
  {
    num: "02",
    title: "Verifikasi Kilat KYC Biometrik",
    desc: "Cukup foto e-KTP & liveness selfie 1x. Aman, terenkripsi & anti-repot.",
    icon: ShieldCheck,
    color: "#10b981",
  },
  {
    num: "03",
    title: "Instant QRIS & Digital Manifest",
    desc: "Bayar via QRIS otomatis. Kondisi fisik barang difoto & dicatat transparan.",
    icon: CreditCard,
    color: "#fbbf24",
  },
  {
    num: "04",
    title: "Terima Gear & Nikmati Liburanmu!",
    desc: "Barang diantar instan atau ambil di lokasi vendor. Siap beraksi!",
    icon: PackageCheck,
    color: "#38bdf8",
  },
];

const SceneFourSteps: React.FC<{ fps: number }> = ({ fps }) => {
  const frame = useCurrentFrame();

  return (
    <AbsoluteFill
      style={{
        padding: "190px 50px 60px 50px",
        display: "flex",
        flexDirection: "column",
        justifyContent: "flex-start",
        gap: 20,
        opacity: interpolate(frame, [190, 205], [1, 0], {
          extrapolateRight: "clamp",
        }),
      }}
    >
      <div style={{ textAlign: "center", marginBottom: 6 }}>
        <span
          style={{
            fontSize: 18,
            fontWeight: 800,
            color: "#38bdf8",
            letterSpacing: 2,
            textTransform: "uppercase",
          }}
        >
          ALUR TRANSAKSI RESMI
        </span>
        <h2
          style={{
            fontSize: 44,
            fontWeight: 900,
            color: "#ffffff",
            margin: "6px 0 0 0",
            letterSpacing: -1,
          }}
        >
          Praktis • Cepat • Terjamin
        </h2>
      </div>

      {STEPS.map((step, idx) => {
        const stepDelay = idx * 16;
        const progress = spring({
          frame: frame - stepDelay,
          fps,
          config: { damping: 14, stiffness: 90 },
        });

        const IconComponent = step.icon;

        return (
          <div
            key={idx}
            style={{
              transform: `translateY(${interpolate(progress, [0, 1], [50, 0])}px) scale(${progress})`,
              opacity: progress,
              background: "rgba(15, 23, 42, 0.82)",
              backdropFilter: "blur(20px)",
              borderRadius: 26,
              padding: "20px 24px",
              display: "flex",
              alignItems: "center",
              gap: 20,
              border: `1.5px solid ${step.color}40`,
              boxShadow: "0 15px 35px rgba(0,0,0,0.35)",
            }}
          >
            {/* Step Number with glowing border */}
            <div
              style={{
                width: 68,
                height: 68,
                borderRadius: 20,
                background: `${step.color}18`,
                border: `2px solid ${step.color}60`,
                display: "flex",
                flexDirection: "column",
                alignItems: "center",
                justifyContent: "center",
                boxShadow: `0 0 20px ${step.color}35`,
                flexShrink: 0,
              }}
            >
              <IconComponent size={28} color={step.color} />
              <span
                style={{
                  fontSize: 12,
                  fontWeight: 900,
                  color: step.color,
                  marginTop: 2,
                }}
              >
                {step.num}
              </span>
            </div>

            <div style={{ flex: 1 }}>
              <h3
                style={{
                  fontSize: 24,
                  fontWeight: 900,
                  color: "#ffffff",
                  margin: "0 0 4px 0",
                }}
              >
                {step.title}
              </h3>
              <p
                style={{
                  fontSize: 17,
                  color: "#94a3b8",
                  margin: 0,
                  lineHeight: 1.35,
                  fontWeight: 500,
                }}
              >
                {step.desc}
              </p>
            </div>
          </div>
        );
      })}
    </AbsoluteFill>
  );
};

const SceneHowItWorksOutro: React.FC<{ fps: number }> = ({ fps }) => {
  const frame = useCurrentFrame();
  const enter = spring({ frame, fps, config: { damping: 13, stiffness: 90 } });
  const pulse = Math.sin(frame / 10) * 0.04;

  return (
    <AbsoluteFill
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        padding: "0 50px",
        textAlign: "center",
      }}
    >
      <div
        style={{
          transform: `scale(${enter})`,
          width: 110,
          height: 110,
          borderRadius: 32,
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          boxShadow: "0 0 50px rgba(0, 242, 254, 0.6)",
          marginBottom: 24,
        }}
      >
        <Sparkles size={60} color="#ffffff" />
      </div>

      <h2
        style={{
          fontSize: 54,
          fontWeight: 900,
          color: "#ffffff",
          margin: "0 0 16px 0",
          letterSpacing: -1.5,
        }}
      >
        Semudah Itu Sewa di Rentify!
      </h2>

      <p
        style={{
          fontSize: 24,
          color: "#cbd5e1",
          maxWidth: 800,
          lineHeight: 1.4,
          marginBottom: 36,
          fontWeight: 600,
        }}
      >
        Jelajahi ribuan alat siap pakai di Semarang & sekitarnya sekarang juga.
      </p>

      <div
        style={{
          transform: `scale(${enter * (1 + pulse)})`,
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          padding: "22px 50px",
          borderRadius: 999,
          boxShadow: "0 15px 40px rgba(0, 242, 254, 0.5)",
          display: "flex",
          alignItems: "center",
          gap: 16,
        }}
      >
        <span style={{ fontSize: 28, fontWeight: 900, color: "#ffffff" }}>
          Kunjungi rentify21.my.id
        </span>
        <ArrowRight size={32} color="#ffffff" strokeWidth={3} />
      </div>
    </AbsoluteFill>
  );
};
