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
  Img,
} from "remotion";
import {
  ShieldCheck,
  Sparkles,
  ArrowRight,
  TrendingUp,
  Award,
  Wallet,
  CheckCircle2,
  QrCode,
  Tag,
  Lock,
  Zap,
} from "lucide-react";
import { RENTIFY_THEME } from "./theme";

export const RentifyBrandPromo: React.FC = () => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  // Background audio volume interpolation (smooth fade in and fade out)
  const audioVolume = interpolate(
    frame,
    [0, 20, 420, 450],
    [0, 0.75, 0.75, 0],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  // Floating ambient cyber glow orbs
  const orb1Y = Math.sin(frame / 22) * 45;
  const orb1X = Math.cos(frame / 26) * 35;
  const orb2Y = Math.cos(frame / 28) * 50;
  const orb2X = Math.sin(frame / 24) * 40;

  return (
    <AbsoluteFill
      style={{
        background: RENTIFY_THEME.colors.bgGradient,
        fontFamily: RENTIFY_THEME.typography.fontFamily,
        color: RENTIFY_THEME.colors.textWhite,
        overflow: "hidden",
      }}
    >
      {/* Background BGM Track */}
      <Audio src={staticFile("assets/bgm-promo.mp3")} volume={audioVolume} />

      {/* Cyber Mesh & Ambient Light Glows */}
      <div
        style={{
          position: "absolute",
          top: -150 + orb1Y,
          left: -100 + orb1X,
          width: 800,
          height: 800,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(0, 242, 254, 0.28) 0%, rgba(2, 132, 199, 0.15) 50%, transparent 75%)",
          filter: "blur(90px)",
          pointerEvents: "none",
        }}
      />
      <div
        style={{
          position: "absolute",
          bottom: -180 + orb2Y,
          right: -120 + orb2X,
          width: 850,
          height: 850,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, rgba(245, 158, 11, 0.15) 50%, transparent 75%)",
          filter: "blur(100px)",
          pointerEvents: "none",
        }}
      />

      {/* Futuristic Grid Pattern Overlay */}
      <div
        style={{
          position: "absolute",
          inset: 0,
          backgroundImage:
            "radial-gradient(rgba(56, 189, 248, 0.18) 1.2px, transparent 1.2px)",
          backgroundSize: "40px 40px",
          opacity: 0.5,
          pointerEvents: "none",
        }}
      />

      {/* Top Persistent Cinematic Header */}
      <TopBrandHeader frame={frame} fps={fps} />

      {/* ================= SCENE 1: RENTER HOOK & GEAR SHOWCASE (0 - 115) ================= */}
      <Sequence from={0} durationInFrames={115}>
        <SceneRenterHook fps={fps} />
      </Sequence>

      {/* ================= SCENE 2: 3 PILAR KEAMANAN (110 - 225) ================= */}
      <Sequence from={110} durationInFrames={115}>
        <SceneTrustPillars fps={fps} />
      </Sequence>

      {/* ================= SCENE 3: OWNER YIELD & MESIN CUAN (220 - 335) ================= */}
      <Sequence from={220} durationInFrames={115}>
        <SceneOwnerYield fps={fps} />
      </Sequence>

      {/* ================= SCENE 4: HERO OUTRO & CTA (330 - 450) ================= */}
      <Sequence from={330} durationInFrames={120}>
        <SceneOutroCTA fps={fps} />
      </Sequence>
    </AbsoluteFill>
  );
};

// =========================================================================
// TOP PERSISTENT BRAND HEADER
// =========================================================================
const TopBrandHeader: React.FC<{ frame: number; fps: number }> = ({
  frame,
  fps,
}) => {
  const enter = spring({ frame, fps, config: { damping: 14, stiffness: 90 } });

  return (
    <div
      style={{
        position: "absolute",
        top: 60,
        left: 50,
        right: 50,
        display: "flex",
        justifyContent: "space-between",
        alignItems: "center",
        zIndex: 100,
        opacity: enter,
        transform: `translateY(${interpolate(enter, [0, 1], [-30, 0])}px)`,
      }}
    >
      <div style={{ display: "flex", alignItems: "center", gap: 16 }}>
        <div
          style={{
            width: 58,
            height: 58,
            borderRadius: 18,
            background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            boxShadow: "0 0 25px rgba(0, 242, 254, 0.45)",
          }}
        >
          <Sparkles size={32} color="#ffffff" />
        </div>
        <div>
          <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
            <h1
              style={{
                fontSize: 40,
                fontWeight: 900,
                letterSpacing: -1.2,
                color: "#ffffff",
                margin: 0,
                lineHeight: 1,
              }}
            >
              Rentify
            </h1>
            <span
              style={{
                background: "rgba(0, 242, 254, 0.15)",
                color: "#38bdf8",
                border: "1px solid rgba(0, 242, 254, 0.4)",
                padding: "3px 10px",
                borderRadius: 8,
                fontSize: 13,
                fontWeight: 800,
                letterSpacing: 0.8,
              }}
            >
              APP & WEB
            </span>
          </div>
          <span
            style={{
              fontSize: 16,
              fontWeight: 600,
              color: "#94a3b8",
              letterSpacing: 0.5,
            }}
          >
            rentify21.my.id
          </span>
        </div>
      </div>

      <div
        style={{
          background: "rgba(15, 23, 42, 0.75)",
          backdropFilter: "blur(16px)",
          padding: "10px 22px",
          borderRadius: 999,
          border: "1px solid rgba(255, 255, 255, 0.15)",
          boxShadow: "0 8px 30px rgba(0,0,0,0.3)",
          fontSize: 18,
          fontWeight: 800,
          color: "#ffffff",
          display: "flex",
          alignItems: "center",
          gap: 10,
        }}
      >
        <Award size={22} color="#f59e0b" />
        <span>Platform Rental #1</span>
      </div>
    </div>
  );
};

// =========================================================================
// SCENE 1: RENTER HOOK & VISUAL GEAR SHOWCASE (REAL PHOTOS)
// =========================================================================
const SceneRenterHook: React.FC<{ fps: number }> = ({ fps }) => {
  const frame = useCurrentFrame();

  const titleProgress = spring({
    frame,
    fps,
    config: { damping: 13, stiffness: 95 },
  });

  const cardsProgress = spring({
    frame: frame - 15,
    fps,
    config: { damping: 14, stiffness: 85 },
  });

  const items = [
    {
      title: "Sony Alpha 7 IV + Lensa GM",
      category: "Kamera & Lensa",
      imgSrc: staticFile("assets/camera-sony.jpg"),
      buyPrice: "Rp 36.500.000",
      rentPrice: "Rp 150.000 / hari",
      savings: "Hemat 98%",
      color: "#00f2fe",
    },
    {
      title: "Paket Camping Tenda 4P Waterproof",
      category: "Outdoor & Adventure",
      imgSrc: staticFile("assets/camping-tent.jpg"),
      buyPrice: "Rp 3.250.000",
      rentPrice: "Rp 45.000 / hari",
      savings: "Hemat 97%",
      color: "#10b981",
    },
    {
      title: "DJI Mini 4 Pro Drone 4K HDR",
      category: "Drone & Aerial",
      imgSrc: staticFile("assets/drone-tech.jpg"),
      buyPrice: "Rp 15.900.000",
      rentPrice: "Rp 120.000 / hari",
      savings: "Hemat 98%",
      color: "#f59e0b",
    },
  ];

  return (
    <AbsoluteFill
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        padding: "0 50px",
        marginTop: 70,
      }}
    >
      {/* Top Hook Pill */}
      <div
        style={{
          transform: `scale(${titleProgress})`,
          opacity: titleProgress,
          background:
            "linear-gradient(90deg, rgba(245, 158, 11, 0.25) 0%, rgba(251, 191, 36, 0.15) 100%)",
          color: "#fbbf24",
          border: "1.5px solid rgba(245, 158, 11, 0.5)",
          padding: "10px 26px",
          borderRadius: 999,
          fontSize: 22,
          fontWeight: 800,
          marginBottom: 20,
          boxShadow: "0 0 25px rgba(245, 158, 11, 0.25)",
          display: "flex",
          alignItems: "center",
          gap: 10,
        }}
      >
        <Zap size={22} color="#fbbf24" fill="#fbbf24" />
        <span>TIPS CERDAS HEMAT PULUHAN JUTA!</span>
      </div>

      {/* Main Kinetic Headline */}
      <h2
        style={{
          transform: `translateY(${interpolate(titleProgress, [0, 1], [50, 0])}px)`,
          opacity: titleProgress,
          fontSize: 58,
          fontWeight: 900,
          textAlign: "center",
          lineHeight: 1.15,
          letterSpacing: -1.8,
          margin: "0 0 16px 0",
          color: "#ffffff",
        }}
      >
        Mau Pakai Gear Idaman,
        <br />
        <span
          style={{
            background: "linear-gradient(135deg, #00f2fe 0%, #38bdf8 100%)",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
            filter: "drop-shadow(0 0 20px rgba(0, 242, 254, 0.4))",
          }}
        >
          Tanpa Harus Beli Mahal?
        </span>
      </h2>

      <p
        style={{
          opacity: titleProgress,
          fontSize: 24,
          color: "#94a3b8",
          textAlign: "center",
          maxWidth: 850,
          lineHeight: 1.35,
          marginBottom: 36,
          fontWeight: 600,
        }}
      >
        Sewa kamera, drone, hingga tenda camping premium mulai puluhan ribu di Rentify.
      </p>

      {/* 3 Real Product Showcase Cards */}
      <div
        style={{
          width: "100%",
          display: "flex",
          flexDirection: "column",
          gap: 18,
          opacity: cardsProgress,
          transform: `translateY(${interpolate(cardsProgress, [0, 1], [60, 0])}px)`,
        }}
      >
        {items.map((item, idx) => {
          const itemStagger = spring({
            frame: frame - 20 - idx * 8,
            fps,
            config: { damping: 14, stiffness: 90 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `scale(${itemStagger})`,
                opacity: itemStagger,
                background: "rgba(15, 23, 42, 0.8)",
                backdropFilter: "blur(20px)",
                borderRadius: 24,
                padding: "16px 22px",
                border: "1.5px solid rgba(255, 255, 255, 0.12)",
                boxShadow: "0 15px 35px rgba(0,0,0,0.4)",
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between",
                gap: 20,
              }}
            >
              {/* Product Photo Thumbnail */}
              <div
                style={{
                  width: 100,
                  height: 100,
                  borderRadius: 18,
                  overflow: "hidden",
                  border: `2px solid ${item.color}50`,
                  boxShadow: `0 0 20px ${item.color}30`,
                  flexShrink: 0,
                  position: "relative",
                  background: "#0b1120",
                }}
              >
                <Img
                  src={item.imgSrc}
                  style={{
                    width: "100%",
                    height: "100%",
                    objectFit: "cover",
                  }}
                />
              </div>

              {/* Product Info */}
              <div style={{ flex: 1 }}>
                <span
                  style={{
                    fontSize: 14,
                    fontWeight: 800,
                    color: item.color,
                    textTransform: "uppercase",
                    letterSpacing: 0.8,
                  }}
                >
                  {item.category}
                </span>
                <h3
                  style={{
                    fontSize: 26,
                    fontWeight: 900,
                    color: "#ffffff",
                    margin: "4px 0 6px 0",
                    lineHeight: 1.2,
                  }}
                >
                  {item.title}
                </h3>
                <div style={{ display: "flex", alignItems: "center", gap: 12 }}>
                  <span
                    style={{
                      fontSize: 16,
                      color: "#64748b",
                      textDecoration: "line-through",
                      fontWeight: 600,
                    }}
                  >
                    Beli: {item.buyPrice}
                  </span>
                  <span
                    style={{
                      background: "rgba(16, 185, 129, 0.2)",
                      color: "#34d399",
                      border: "1px solid rgba(16, 185, 129, 0.4)",
                      fontSize: 14,
                      fontWeight: 900,
                      padding: "2px 10px",
                      borderRadius: 999,
                    }}
                  >
                    {item.savings}
                  </span>
                </div>
              </div>

              {/* Rental Rate Badge */}
              <div
                style={{
                  textAlign: "right",
                  background: "rgba(2, 132, 199, 0.15)",
                  border: "1px solid rgba(56, 189, 248, 0.35)",
                  padding: "12px 18px",
                  borderRadius: 18,
                }}
              >
                <span style={{ fontSize: 13, color: "#94a3b8", display: "block", fontWeight: 700 }}>
                  Tarif Sewa
                </span>
                <span
                  style={{
                    fontSize: 22,
                    fontWeight: 900,
                    color: "#38bdf8",
                    whiteSpace: "nowrap",
                  }}
                >
                  {item.rentPrice}
                </span>
              </div>
            </div>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

// =========================================================================
// SCENE 2: 3 PILAR KEAMANAN (ANTI-WASWAS & RESMI)
// =========================================================================
const SceneTrustPillars: React.FC<{ fps: number }> = ({ fps }) => {
  const frame = useCurrentFrame();

  const titleProgress = spring({
    frame,
    fps,
    config: { damping: 13, stiffness: 95 },
  });

  const pillars = [
    {
      title: "1. Verifikasi KYC Biometrik",
      desc: "Validasi e-KTP & liveness detection 100% untuk penyewa & mitra pemilik.",
      icon: ShieldCheck,
      color: "#00f2fe",
      badge: "Verifikasi Otomatis",
    },
    {
      title: "2. Digital Manifest Fisik",
      desc: "Foto 360° & cek kelayakan barang sebelum dan sesudah sewa tercatat di sistem.",
      icon: CheckCircle2,
      color: "#10b981",
      badge: "Transparan & Adil",
    },
    {
      title: "3. Jaminan Keamanan Transaksi",
      desc: "Proteksi dana sewa & garansi barang aman tanpa khawatir sengketa.",
      icon: Lock,
      color: "#f59e0b",
      badge: "Proteksi 100%",
    },
  ];

  return (
    <AbsoluteFill
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        padding: "0 50px",
        marginTop: 60,
      }}
    >
      <div
        style={{
          transform: `scale(${titleProgress})`,
          opacity: titleProgress,
          background: "rgba(16, 185, 129, 0.18)",
          color: "#34d399",
          border: "1.5px solid rgba(16, 185, 129, 0.45)",
          padding: "10px 26px",
          borderRadius: 999,
          fontSize: 22,
          fontWeight: 800,
          marginBottom: 20,
          boxShadow: "0 0 25px rgba(16, 185, 129, 0.25)",
          display: "flex",
          alignItems: "center",
          gap: 10,
        }}
      >
        <ShieldCheck size={24} color="#34d399" />
        <span>TRANSAKSI TENANG & TERPERCAYA</span>
      </div>

      <h2
        style={{
          transform: `translateY(${interpolate(titleProgress, [0, 1], [40, 0])}px)`,
          opacity: titleProgress,
          fontSize: 58,
          fontWeight: 900,
          textAlign: "center",
          lineHeight: 1.15,
          letterSpacing: -1.8,
          margin: "0 0 16px 0",
          color: "#ffffff",
        }}
      >
        3 Pilar Keamanan Rentify,
        <br />
        <span
          style={{
            background: "linear-gradient(135deg, #10b981 0%, #00f2fe 100%)",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
            filter: "drop-shadow(0 0 20px rgba(16, 185, 129, 0.4))",
          }}
        >
          Bebas Was-Was 100%!
        </span>
      </h2>

      <p
        style={{
          opacity: titleProgress,
          fontSize: 24,
          color: "#94a3b8",
          textAlign: "center",
          maxWidth: 820,
          lineHeight: 1.35,
          marginBottom: 40,
          fontWeight: 600,
        }}
      >
        Platform dirancang dengan standar keamanan perbankan dan ekosistem rental modern.
      </p>

      {/* 3 Pillar Cards */}
      <div
        style={{
          width: "100%",
          display: "flex",
          flexDirection: "column",
          gap: 20,
        }}
      >
        {pillars.map((pillar, idx) => {
          const cardProgress = spring({
            frame: frame - 18 - idx * 10,
            fps,
            config: { damping: 14, stiffness: 90 },
          });

          const IconComponent = pillar.icon;

          return (
            <div
              key={idx}
              style={{
                transform: `translateX(${interpolate(cardProgress, [0, 1], [-80, 0])}px)`,
                opacity: cardProgress,
                background: "rgba(15, 23, 42, 0.82)",
                backdropFilter: "blur(20px)",
                borderRadius: 26,
                padding: "24px 28px",
                border: "1.5px solid rgba(255, 255, 255, 0.12)",
                boxShadow: "0 15px 35px rgba(0,0,0,0.35)",
                display: "flex",
                alignItems: "center",
                gap: 24,
              }}
            >
              <div
                style={{
                  width: 72,
                  height: 72,
                  borderRadius: 22,
                  background: `${pillar.color}20`,
                  border: `2px solid ${pillar.color}60`,
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  boxShadow: `0 0 25px ${pillar.color}35`,
                  flexShrink: 0,
                }}
              >
                <IconComponent size={38} color={pillar.color} />
              </div>

              <div style={{ flex: 1 }}>
                <div style={{ display: "flex", alignItems: "center", gap: 12, marginBottom: 6 }}>
                  <h3
                    style={{
                      fontSize: 28,
                      fontWeight: 900,
                      color: "#ffffff",
                      margin: 0,
                    }}
                  >
                    {pillar.title}
                  </h3>
                  <span
                    style={{
                      background: `${pillar.color}18`,
                      color: pillar.color,
                      border: `1px solid ${pillar.color}40`,
                      fontSize: 13,
                      fontWeight: 800,
                      padding: "2px 10px",
                      borderRadius: 999,
                    }}
                  >
                    {pillar.badge}
                  </span>
                </div>
                <p
                  style={{
                    fontSize: 20,
                    color: "#94a3b8",
                    margin: 0,
                    lineHeight: 1.4,
                    fontWeight: 500,
                  }}
                >
                  {pillar.desc}
                </p>
              </div>
            </div>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

// =========================================================================
// SCENE 3: OWNER YIELD & MESIN CUAN (PASIF INCOME MITRA)
// =========================================================================
const SceneOwnerYield: React.FC<{ fps: number }> = ({ fps }) => {
  const frame = useCurrentFrame();

  const titleProgress = spring({
    frame,
    fps,
    config: { damping: 13, stiffness: 95 },
  });

  const counterProgress = interpolate(frame, [15, 65], [0, 3850000], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });

  const formattedIncome =
    "Rp " + Math.floor(counterProgress).toLocaleString("id-ID");

  return (
    <AbsoluteFill
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        padding: "0 50px",
        marginTop: 60,
      }}
    >
      <div
        style={{
          transform: `scale(${titleProgress})`,
          opacity: titleProgress,
          background: "rgba(0, 242, 254, 0.18)",
          color: "#38bdf8",
          border: "1.5px solid rgba(0, 242, 254, 0.45)",
          padding: "10px 26px",
          borderRadius: 999,
          fontSize: 22,
          fontWeight: 800,
          marginBottom: 20,
          boxShadow: "0 0 25px rgba(0, 242, 254, 0.25)",
          display: "flex",
          alignItems: "center",
          gap: 10,
        }}
      >
        <TrendingUp size={24} color="#38bdf8" />
        <span>PUNYA ALAT MENGANGGUR DI RUMAH?</span>
      </div>

      <h2
        style={{
          transform: `translateY(${interpolate(titleProgress, [0, 1], [40, 0])}px)`,
          opacity: titleProgress,
          fontSize: 58,
          fontWeight: 900,
          textAlign: "center",
          lineHeight: 1.15,
          letterSpacing: -1.8,
          margin: "0 0 16px 0",
          color: "#ffffff",
        }}
      >
        Ubah Aset Pasif Jadi
        <br />
        <span
          style={{
            background: "linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
            filter: "drop-shadow(0 0 20px rgba(245, 158, 11, 0.4))",
          }}
        >
          Mesin Cuan Tiap Bulan!
        </span>
      </h2>

      <p
        style={{
          opacity: titleProgress,
          fontSize: 24,
          color: "#94a3b8",
          textAlign: "center",
          maxWidth: 820,
          lineHeight: 1.35,
          marginBottom: 36,
          fontWeight: 600,
        }}
      >
        Kamera, drone, tenda, sound system nganggur? Sewakan dengan proteksi aman.
      </p>

      {/* Main Income Counter Card */}
      <div
        style={{
          width: "100%",
          background: "rgba(15, 23, 42, 0.85)",
          backdropFilter: "blur(24px)",
          borderRadius: 32,
          padding: "40px",
          border: "2px solid rgba(251, 191, 36, 0.4)",
          boxShadow: "0 20px 50px rgba(245, 158, 11, 0.15)",
          textAlign: "center",
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 12 }}>
          <Wallet size={26} color="#fbbf24" />
          <span style={{ fontSize: 22, color: "#cbd5e1", fontWeight: 700 }}>
            Simulasi Pasif Income Pemilik Barang
          </span>
        </div>

        {/* Dynamic Rolling Number */}
        <h3
          style={{
            fontSize: 72,
            fontWeight: 900,
            color: "#fbbf24",
            margin: "0 0 10px 0",
            letterSpacing: -2,
            lineHeight: 1,
            filter: "drop-shadow(0 0 25px rgba(245, 158, 11, 0.5))",
          }}
        >
          {formattedIncome}
          <span style={{ fontSize: 32, color: "#94a3b8", fontWeight: 700 }}> / bulan</span>
        </h3>

        {/* 0% Commission Golden Badge */}
        <div
          style={{
            background: "linear-gradient(90deg, #10b981 0%, #059669 100%)",
            color: "#ffffff",
            padding: "10px 24px",
            borderRadius: 999,
            fontSize: 20,
            fontWeight: 900,
            boxShadow: "0 8px 25px rgba(16, 185, 129, 0.4)",
            display: "inline-flex",
            alignItems: "center",
            gap: 10,
            margin: "14px 0 24px 0",
          }}
        >
          <Sparkles size={20} color="#ffffff" />
          <span>0% KOMISI PEMILIK (100% Milikmu!)</span>
        </div>

        {/* 3 Quick Benefit Bullets */}
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "1fr 1fr",
            gap: 16,
            width: "100%",
            textAlign: "left",
          }}
        >
          <div
            style={{
              background: "rgba(255, 255, 255, 0.05)",
              padding: "14px 18px",
              borderRadius: 16,
              display: "flex",
              alignItems: "center",
              gap: 12,
            }}
          >
            <CheckCircle2 size={24} color="#10b981" />
            <span style={{ fontSize: 18, color: "#e2e8f0", fontWeight: 700 }}>
              Penyewa Wajib KYC KTP
            </span>
          </div>
          <div
            style={{
              background: "rgba(255, 255, 255, 0.05)",
              padding: "14px 18px",
              borderRadius: 16,
              display: "flex",
              alignItems: "center",
              gap: 12,
            }}
          >
            <CheckCircle2 size={24} color="#10b981" />
            <span style={{ fontSize: 18, color: "#e2e8f0", fontWeight: 700 }}>
              Cair Otomatis ke Bank
            </span>
          </div>
        </div>
      </div>
    </AbsoluteFill>
  );
};

// =========================================================================
// SCENE 4: HERO OUTRO & CALL TO ACTION (CTA)
// =========================================================================
const SceneOutroCTA: React.FC<{ fps: number }> = ({ fps }) => {
  const frame = useCurrentFrame();

  const enter = spring({
    frame,
    fps,
    config: { damping: 13, stiffness: 90 },
  });

  const buttonPulse = Math.sin(frame / 10) * 0.04;

  return (
    <AbsoluteFill
      style={{
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        padding: "0 50px",
        marginTop: 40,
      }}
    >
      {/* Brand Glowing Logo Box */}
      <div
        style={{
          transform: `scale(${enter})`,
          opacity: enter,
          width: 130,
          height: 130,
          borderRadius: 36,
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          boxShadow:
            "0 0 60px rgba(0, 242, 254, 0.6), inset 0 0 20px rgba(255,255,255,0.4)",
          marginBottom: 30,
        }}
      >
        <Sparkles size={72} color="#ffffff" />
      </div>

      {/* Main Headline */}
      <h2
        style={{
          transform: `translateY(${interpolate(enter, [0, 1], [40, 0])}px)`,
          opacity: enter,
          fontSize: 64,
          fontWeight: 900,
          textAlign: "center",
          lineHeight: 1.15,
          letterSpacing: -2,
          margin: "0 0 14px 0",
          color: "#ffffff",
        }}
      >
        Mulai Sewa & Cuan
        <br />
        <span
          style={{
            background: "linear-gradient(135deg, #00f2fe 0%, #38bdf8 100%)",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
            filter: "drop-shadow(0 0 25px rgba(0, 242, 254, 0.5))",
          }}
        >
          Bersama Rentify!
        </span>
      </h2>

      <p
        style={{
          opacity: enter,
          fontSize: 26,
          color: "#cbd5e1",
          textAlign: "center",
          maxWidth: 800,
          lineHeight: 1.4,
          marginBottom: 32,
          fontWeight: 600,
        }}
      >
        Platform rental barang & gear terlengkap dengan sistem teraman di Indonesia.
      </p>

      {/* Weekend Promo Code Box */}
      <div
        style={{
          transform: `scale(${enter})`,
          opacity: enter,
          background: "rgba(245, 158, 11, 0.15)",
          border: "2px dashed #f59e0b",
          borderRadius: 24,
          padding: "16px 36px",
          display: "flex",
          alignItems: "center",
          gap: 16,
          marginBottom: 36,
          boxShadow: "0 0 30px rgba(245, 158, 11, 0.2)",
        }}
      >
        <Tag size={30} color="#fbbf24" />
        <div>
          <span style={{ fontSize: 16, color: "#94a3b8", display: "block", fontWeight: 700 }}>
            Gunakan Kode Promo Spesial:
          </span>
          <span
            style={{
              fontSize: 32,
              fontWeight: 900,
              color: "#fbbf24",
              letterSpacing: 2,
            }}
          >
            RENTIFYWEEKEND
          </span>
        </div>
        <span
          style={{
            background: "#f59e0b",
            color: "#0f172a",
            fontWeight: 900,
            fontSize: 16,
            padding: "6px 14px",
            borderRadius: 10,
          }}
        >
          Diskon Rp 50rb!
        </span>
      </div>

      {/* Main Animated Glowing CTA Button */}
      <div
        style={{
          transform: `scale(${enter * (1 + buttonPulse)})`,
          opacity: enter,
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          padding: "24px 60px",
          borderRadius: 999,
          boxShadow: "0 15px 40px rgba(0, 242, 254, 0.5)",
          display: "flex",
          alignItems: "center",
          gap: 16,
          marginBottom: 26,
        }}
      >
        <span style={{ fontSize: 32, fontWeight: 900, color: "#ffffff" }}>
          Kunjungi rentify21.my.id
        </span>
        <ArrowRight size={34} color="#ffffff" strokeWidth={3} />
      </div>

      {/* Instant QRIS Supported Logos */}
      <div
        style={{
          opacity: enter,
          display: "flex",
          alignItems: "center",
          gap: 16,
          color: "#94a3b8",
          fontSize: 18,
          fontWeight: 700,
        }}
      >
        <QrCode size={24} color="#38bdf8" />
        <span>Instant QRIS • GoPay • OVO • DANA • ShopeePay • Bank Transfer</span>
      </div>
    </AbsoluteFill>
  );
};
