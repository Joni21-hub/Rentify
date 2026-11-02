import React from "react";
import {
  AbsoluteFill,
  interpolate,
  spring,
  useCurrentFrame,
  useVideoConfig,
  staticFile,
  Audio,
  Img,
} from "remotion";
import { z } from "zod";
import {
  Search,
  Sparkles,
  ShieldCheck,
  MapPin,
  Star,
  Tag,
  CreditCard,
  QrCode,
  Wallet,
  TrendingUp,
  ArrowRight,
  ShoppingBag,
  Bell,
  SlidersHorizontal,
  BadgeCheck,
  Zap,
  Check,
  CheckCircle2,
} from "lucide-react";
import { RENTIFY_THEME } from "./theme";

export const rentifyUIShowcaseSchema = z.object({
  brandName: z.string().default("Rentify"),
  domainUrl: z.string().default("rentify21.my.id"),
  userCity: z.string().default("Semarang"),
  searchKeyword: z.string().default("Sewa Sony A7 IV & Tenda"),
  featuredProductName: z.string().default("Sony Alpha 7 IV Mirrorless"),
  rentalDailyPrice: z.string().default("Rp 250.000 / hari"),
  retailPrice: z.string().default("Rp 36.500.000"),
  savingsPercentage: z.string().default("Hemat 98%"),
  voucherCode: z.string().default("RENTIFYWEEKEND"),
  voucherDiscountAmount: z.string().default("-Rp 50.000"),
  ownerSimulatedMonthlyIncome: z.string().default("Rp 3.850.000"),
  ctaButtonText: z.string().default("Mulai Sewa / Pasang Iklan"),
});

export type RentifyUIShowcaseProps = z.infer<typeof rentifyUIShowcaseSchema>;

export const RentifyUIShowcase: React.FC<RentifyUIShowcaseProps> = (props) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  // Background BGM volume fade
  const audioVolume = interpolate(
    frame,
    [0, 25, 570, 600],
    [0, 0.75, 0.75, 0],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  // Floating ambient lighting
  const bgFloat1 = Math.sin(frame / 30) * 35;
  const bgFloat2 = Math.cos(frame / 35) * 45;

  const deviceEntrance = spring({
    frame,
    fps,
    config: { damping: 14, stiffness: 80 },
  });

  // Dynamic Camera motion throughout the 6 scenes
  const deviceScale = interpolate(
    frame,
    [0, 60, 115, 145, 220, 250, 330, 360, 440, 470, 510, 560],
    [0.85, 1.02, 1.02, 1.06, 1.06, 1.1, 1.1, 1.12, 1.12, 1.02, 1.02, 0.9],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  const deviceRotateY = interpolate(
    frame,
    [0, 60, 115, 150, 220, 260, 330, 370, 440, 480, 510, 560],
    [-10, 0, 0, -3, 0, 3, 0, -2, 0, 4, 0, 0],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  const deviceTranslateY = interpolate(
    frame,
    [0, 60, 115, 145, 220, 250, 330, 360, 440, 470, 510, 560],
    [80, 25, 25, 5, 5, -20, -20, -30, -30, 15, 15, 50],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  return (
    <AbsoluteFill
      style={{
        background: RENTIFY_THEME.colors.bgGradient,
        fontFamily: RENTIFY_THEME.typography.fontFamily,
        color: RENTIFY_THEME.colors.textWhite,
        overflow: "hidden",
      }}
    >
      {/* Background BGM */}
      <Audio src={staticFile("assets/bgm-promo.mp3")} volume={audioVolume} />

      {/* Ambient Cyber Orbs */}
      <div
        style={{
          position: "absolute",
          top: -100 + bgFloat1,
          left: -100,
          width: 800,
          height: 800,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(0, 242, 254, 0.28) 0%, transparent 70%)",
          filter: "blur(90px)",
          pointerEvents: "none",
        }}
      />
      <div
        style={{
          position: "absolute",
          bottom: -120 + bgFloat2,
          right: -100,
          width: 850,
          height: 850,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, transparent 70%)",
          filter: "blur(100px)",
          pointerEvents: "none",
        }}
      />

      {/* Grid Pattern */}
      <div
        style={{
          position: "absolute",
          inset: 0,
          backgroundImage:
            "radial-gradient(rgba(56, 189, 248, 0.15) 1.2px, transparent 1.2px)",
          backgroundSize: "36px 36px",
          opacity: 0.5,
          pointerEvents: "none",
        }}
      />

      {/* Top Persistent Header Bar */}
      <TopFloatingHeader frame={frame} fps={fps} props={props} />

      {/* Central 3D Device Container */}
      <div
        style={{
          position: "absolute",
          inset: 0,
          display: "flex",
          justifyContent: "center",
          alignItems: "center",
          perspective: 1400,
          zIndex: 20,
        }}
      >
        <div
          style={{
            transform: `translateY(${deviceTranslateY}px) scale(${deviceScale * deviceEntrance}) rotateY(${deviceRotateY}deg)`,
            transformStyle: "preserve-3d",
            transition: "none",
          }}
        >
          <SmartphoneDevice frame={frame} fps={fps} props={props} />
        </div>
      </div>

      {/* Floating Badges */}
      <FloatingFeatureBadges frame={frame} fps={fps} />

      {/* Final Outro Overlay & CTA (Frames 505 - 600) */}
      {frame >= 505 && (
        <OutroCTAOverlay frame={frame - 505} fps={fps} props={props} />
      )}
    </AbsoluteFill>
  );
};

// =========================================================================
// TOP FLOATING HEADER BAR
// =========================================================================
const TopFloatingHeader: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  const enter = spring({ frame, fps, config: { damping: 15, stiffness: 90 } });

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
        zIndex: 50,
        opacity: enter,
        transform: `translateY(${interpolate(enter, [0, 1], [-40, 0])}px)`,
      }}
    >
      <div style={{ display: "flex", alignItems: "center", gap: 16 }}>
        <div
          style={{
            width: 56,
            height: 56,
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
                fontSize: 38,
                fontWeight: 900,
                letterSpacing: -1.2,
                color: "#ffffff",
                margin: 0,
                lineHeight: 1,
              }}
            >
              {props.brandName}
            </h1>
            <span
              style={{
                background: "rgba(0, 242, 254, 0.15)",
                color: "#38bdf8",
                border: "1px solid rgba(0, 242, 254, 0.4)",
                padding: "3px 10px",
                borderRadius: 8,
                fontSize: 12,
                fontWeight: 800,
              }}
            >
              LIVE APP
            </span>
          </div>
          <span style={{ fontSize: 16, fontWeight: 600, color: "#94a3b8" }}>
            {props.domainUrl}
          </span>
        </div>
      </div>

      <div
        style={{
          background: "rgba(15, 23, 42, 0.8)",
          backdropFilter: "blur(16px)",
          padding: "10px 22px",
          borderRadius: 999,
          border: "1px solid rgba(255, 255, 255, 0.15)",
          boxShadow: "0 8px 24px rgba(0,0,0,0.3)",
          display: "flex",
          alignItems: "center",
          gap: 10,
        }}
      >
        <ShieldCheck size={22} color="#10b981" />
        <span style={{ fontSize: 18, fontWeight: 800, color: "#ffffff" }}>
          Platform Sewa #1 di Indonesia
        </span>
      </div>
    </div>
  );
};

// =========================================================================
// SMARTPHONE DEVICE MOCKUP (COMMANDING 680x1380 SIZE)
// =========================================================================
const SmartphoneDevice: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  const phoneWidth = 680;
  const phoneHeight = 1380;
  const cornerRadius = 54;

  let islandText = "Rentify Live";
  let islandIcon = <Sparkles size={14} color="#38bdf8" />;

  if (frame >= 115 && frame < 220) {
    islandText = "Katalog Terverifikasi";
    islandIcon = <BadgeCheck size={14} color="#10b981" />;
  } else if (frame >= 220 && frame < 330) {
    islandText = "3 Pilar Keamanan Aktif";
    islandIcon = <ShieldCheck size={14} color="#38bdf8" />;
  } else if (frame >= 330 && frame < 440) {
    islandText = "Voucher Diskon Applied";
    islandIcon = <Tag size={14} color="#fbbf24" />;
  } else if (frame >= 440 && frame < 520) {
    islandText = "Mode Pemilik: Cuan Aktif";
    islandIcon = <TrendingUp size={14} color="#10b981" />;
  }

  return (
    <div
      style={{
        width: phoneWidth,
        height: phoneHeight,
        borderRadius: cornerRadius,
        background: "linear-gradient(145deg, #1e293b 0%, #070a12 100%)",
        padding: 16,
        position: "relative",
        boxShadow:
          "0 40px 90px -20px rgba(0, 0, 0, 0.8), 0 0 45px rgba(0, 242, 254, 0.25), inset 0 0 0 2px rgba(255, 255, 255, 0.2)",
      }}
    >
      {/* Outer Titanium Bezel Highlights */}
      <div
        style={{
          position: "absolute",
          inset: 0,
          borderRadius: cornerRadius,
          border: "2px solid rgba(255, 255, 255, 0.2)",
          pointerEvents: "none",
        }}
      />

      {/* Screen Inner Container */}
      <div
        style={{
          width: "100%",
          height: "100%",
          borderRadius: cornerRadius - 12,
          background: "#0b1120",
          overflow: "hidden",
          position: "relative",
          display: "flex",
          flexDirection: "column",
          color: "#ffffff",
        }}
      >
        {/* Status Bar */}
        <div
          style={{
            height: 52,
            width: "100%",
            background: "rgba(11, 17, 32, 0.95)",
            backdropFilter: "blur(12px)",
            display: "flex",
            alignItems: "center",
            justifyContent: "space-between",
            padding: "0 32px",
            zIndex: 40,
            position: "relative",
            borderBottom: "1px solid rgba(255, 255, 255, 0.08)",
          }}
        >
          <span style={{ fontSize: 16, fontWeight: 800, color: "#ffffff" }}>
            09:41
          </span>

          {/* Dynamic Island */}
          <div
            style={{
              position: "absolute",
              top: 10,
              left: "50%",
              transform: "translateX(-50%)",
              background: "#000000",
              height: 32,
              borderRadius: 20,
              padding: "0 16px",
              display: "flex",
              alignItems: "center",
              gap: 8,
              border: "1px solid rgba(255, 255, 255, 0.15)",
              boxShadow: "0 4px 12px rgba(0,0,0,0.5)",
            }}
          >
            {islandIcon}
            <span
              style={{
                color: "#ffffff",
                fontSize: 13,
                fontWeight: 700,
                letterSpacing: 0.3,
              }}
            >
              {islandText}
            </span>
          </div>

          <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
            <span style={{ fontSize: 13, fontWeight: 800, color: "#38bdf8" }}>
              5G
            </span>
            <div
              style={{
                width: 26,
                height: 13,
                borderRadius: 4,
                border: "1.5px solid #ffffff",
                padding: 1.5,
                display: "flex",
                alignItems: "center",
              }}
            >
              <div
                style={{
                  width: "100%",
                  height: "100%",
                  borderRadius: 2,
                  background: "#10b981",
                }}
              />
            </div>
          </div>
        </div>

        {/* ================= SCREEN CONTENT CONTAINER ================= */}
        <div
          style={{
            flex: 1,
            position: "relative",
            overflow: "hidden",
            background: "#0b1120",
          }}
        >
          {/* Scene 1: Home Discovery & Search (0 - 125) */}
          {frame < 125 && <ScreenHomeScene frame={frame} fps={fps} props={props} />}

          {/* Scene 2: Interactive Catalog & Tap (115 - 235) */}
          {frame >= 115 && frame < 235 && (
            <ScreenCatalogScene frame={frame - 115} fps={fps} props={props} />
          )}

          {/* Scene 3: Product Detail Page & 3 Trust Pillars (220 - 345) */}
          {frame >= 220 && frame < 345 && (
            <ScreenPDPScene frame={frame - 220} fps={fps} props={props} />
          )}

          {/* Scene 4: Smart Checkout & QRIS (330 - 450) */}
          {frame >= 330 && frame < 450 && (
            <ScreenCheckoutScene frame={frame - 330} fps={fps} props={props} />
          )}

          {/* Scene 5: Owner / Mitra Passive Income Mode (440 - 535) */}
          {frame >= 440 && frame < 535 && (
            <ScreenOwnerModeScene frame={frame - 440} fps={fps} props={props} />
          )}

          {/* Scene 6: App Overview (525+) */}
          {frame >= 525 && (
            <ScreenOutroOverview frame={frame - 525} fps={fps} props={props} />
          )}
        </div>

        {/* Bottom App Navigation Bar */}
        <div
          style={{
            height: 72,
            background: "rgba(11, 17, 32, 0.95)",
            borderTop: "1px solid rgba(255, 255, 255, 0.1)",
            display: "flex",
            justifyContent: "space-around",
            alignItems: "center",
            padding: "0 16px 10px 16px",
            zIndex: 40,
          }}
        >
          <div
            style={{
              display: "flex",
              flexDirection: "column",
              alignItems: "center",
              gap: 4,
              color: frame < 440 ? "#00f2fe" : "#64748b",
            }}
          >
            <ShoppingBag size={22} />
            <span style={{ fontSize: 12, fontWeight: 700 }}>Sewa</span>
          </div>
          <div
            style={{
              display: "flex",
              flexDirection: "column",
              alignItems: "center",
              gap: 4,
              color: "#64748b",
            }}
          >
            <Search size={22} />
            <span style={{ fontSize: 12, fontWeight: 600 }}>Cari</span>
          </div>
          <div
            style={{
              display: "flex",
              flexDirection: "column",
              alignItems: "center",
              gap: 4,
              color: frame >= 440 && frame < 525 ? "#10b981" : "#64748b",
            }}
          >
            <Wallet size={22} />
            <span style={{ fontSize: 12, fontWeight: 700 }}>Mitra Cuan</span>
          </div>
          <div
            style={{
              display: "flex",
              flexDirection: "column",
              alignItems: "center",
              gap: 4,
              color: "#64748b",
            }}
          >
            <ShieldCheck size={22} />
            <span style={{ fontSize: 12, fontWeight: 600 }}>Garansi</span>
          </div>
        </div>

        {/* Bottom Home Indicator */}
        <div
          style={{
            position: "absolute",
            bottom: 6,
            left: "50%",
            transform: "translateX(-50%)",
            width: 160,
            height: 5,
            borderRadius: 3,
            background: "#ffffff",
            opacity: 0.6,
            zIndex: 50,
          }}
        />
      </div>
    </div>
  );
};

// =========================================================================
// SCREEN SCENE 1: HOME DISCOVERY & SEARCH
// =========================================================================
const ScreenHomeScene: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  const enter = spring({ frame, fps, config: { damping: 14, stiffness: 85 } });

  const searchPrompt = props.searchKeyword;
  const typedCount = Math.floor(
    interpolate(frame, [20, 75], [0, searchPrompt.length], {
      extrapolateLeft: "clamp",
      extrapolateRight: "clamp",
    })
  );
  const displayedQuery = searchPrompt.substring(0, typedCount);

  return (
    <div
      style={{
        padding: "20px 24px",
        display: "flex",
        flexDirection: "column",
        gap: 16,
        transform: `translateY(${interpolate(enter, [0, 1], [30, 0])}px)`,
        opacity: enter,
      }}
    >
      {/* Location & Notification Bar */}
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
          <div
            style={{
              width: 36,
              height: 36,
              borderRadius: 12,
              background: "rgba(0, 242, 254, 0.15)",
              border: "1px solid rgba(0, 242, 254, 0.4)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
            }}
          >
            <MapPin size={20} color="#00f2fe" />
          </div>
          <div>
            <div style={{ fontSize: 12, color: "#94a3b8", fontWeight: 600 }}>
              Lokasi Pengambilan
            </div>
            <div
              style={{
                fontSize: 16,
                fontWeight: 900,
                color: "#ffffff",
                display: "flex",
                alignItems: "center",
                gap: 6,
              }}
            >
              {props.userCity}
              <span
                style={{
                  width: 8,
                  height: 8,
                  borderRadius: "50%",
                  background: "#10b981",
                  display: "inline-block",
                }}
              />
            </div>
          </div>
        </div>

        <div
          style={{
            width: 42,
            height: 42,
            borderRadius: 14,
            background: "rgba(255, 255, 255, 0.08)",
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            border: "1px solid rgba(255, 255, 255, 0.15)",
          }}
        >
          <Bell size={20} color="#38bdf8" />
        </div>
      </div>

      {/* Hero Greeting */}
      <div>
        <h3
          style={{
            fontSize: 26,
            fontWeight: 900,
            color: "#ffffff",
            margin: "0 0 4px 0",
            letterSpacing: -0.5,
          }}
        >
          Mau Sewa Apa Hari Ini? 👋
        </h3>
        <p style={{ fontSize: 14, color: "#94a3b8", margin: 0, fontWeight: 500 }}>
          Ribuan gear siap pakai dengan garansi aman 100%.
        </p>
      </div>

      {/* Typing Search Input */}
      <div
        style={{
          background: "rgba(15, 23, 42, 0.9)",
          borderRadius: 16,
          padding: "14px 18px",
          display: "flex",
          alignItems: "center",
          gap: 12,
          border: "2px solid #00f2fe",
          boxShadow: "0 0 25px rgba(0, 242, 254, 0.25)",
        }}
      >
        <Search size={22} color="#00f2fe" />
        <div style={{ flex: 1, fontSize: 16, fontWeight: 700, color: "#ffffff" }}>
          {displayedQuery}
          <span
            style={{
              opacity: Math.floor(frame / 6) % 2 === 0 ? 1 : 0,
              color: "#00f2fe",
              fontWeight: 900,
            }}
          >
            |
          </span>
        </div>
        <SlidersHorizontal size={20} color="#94a3b8" />
      </div>

      {/* Promo Banner Card with REAL Photo */}
      <div
        style={{
          borderRadius: 22,
          overflow: "hidden",
          position: "relative",
          height: 160,
          border: "1.5px solid rgba(0, 242, 254, 0.3)",
          boxShadow: "0 10px 30px rgba(0,0,0,0.5)",
        }}
      >
        <Img
          src={staticFile("assets/camera-sony.jpg")}
          style={{ width: "100%", height: "100%", objectFit: "cover" }}
        />
        <div
          style={{
            position: "absolute",
            inset: 0,
            background:
              "linear-gradient(90deg, rgba(7,10,18,0.92) 0%, rgba(7,10,18,0.65) 60%, rgba(7,10,18,0.2) 100%)",
            padding: "16px 20px",
            display: "flex",
            flexDirection: "column",
            justifyContent: "center",
          }}
        >
          <div
            style={{
              background: "rgba(245, 158, 11, 0.25)",
              color: "#fbbf24",
              border: "1px solid rgba(245, 158, 11, 0.5)",
              padding: "4px 12px",
              borderRadius: 999,
              fontSize: 12,
              fontWeight: 800,
              display: "inline-flex",
              alignItems: "center",
              gap: 6,
              width: "fit-content",
              marginBottom: 8,
            }}
          >
            <Zap size={14} color="#fbbf24" fill="#fbbf24" />
            <span>FLASH RENTAL PROMO</span>
          </div>
          <h4
            style={{
              fontSize: 22,
              fontWeight: 900,
              color: "#ffffff",
              margin: "0 0 4px 0",
            }}
          >
            Diskon s/d 40% Weekend!
          </h4>
          <span style={{ fontSize: 13, color: "#94a3b8" }}>
            Gunakan kode: <b style={{ color: "#fbbf24" }}>{props.voucherCode}</b>
          </span>
        </div>
      </div>

      {/* Featured Items Grid (REAL PHOTOS!) */}
      <div>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            marginBottom: 10,
          }}
        >
          <span style={{ fontSize: 16, fontWeight: 900, color: "#ffffff" }}>
            Rekomendasi Terlaris 🔥
          </span>
          <span style={{ fontSize: 13, color: "#38bdf8", fontWeight: 700 }}>
            Lihat Semua
          </span>
        </div>

        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
          {/* Item 1 */}
          <div
            style={{
              background: "rgba(15, 23, 42, 0.8)",
              borderRadius: 18,
              padding: 12,
              border: "1px solid rgba(255, 255, 255, 0.1)",
            }}
          >
            <div
              style={{
                height: 110,
                borderRadius: 12,
                overflow: "hidden",
                marginBottom: 8,
              }}
            >
              <Img
                src={staticFile("assets/camera-sony.jpg")}
                style={{ width: "100%", height: "100%", objectFit: "cover" }}
              />
            </div>
            <div style={{ fontSize: 14, fontWeight: 900, color: "#ffffff" }}>
              Sony Alpha 7 IV
            </div>
            <div style={{ fontSize: 13, fontWeight: 800, color: "#00f2fe", marginTop: 2 }}>
              Rp 250rb/hr
            </div>
          </div>

          {/* Item 2 */}
          <div
            style={{
              background: "rgba(15, 23, 42, 0.8)",
              borderRadius: 18,
              padding: 12,
              border: "1px solid rgba(255, 255, 255, 0.1)",
            }}
          >
            <div
              style={{
                height: 110,
                borderRadius: 12,
                overflow: "hidden",
                marginBottom: 8,
              }}
            >
              <Img
                src={staticFile("assets/camping-tent.jpg")}
                style={{ width: "100%", height: "100%", objectFit: "cover" }}
              />
            </div>
            <div style={{ fontSize: 14, fontWeight: 900, color: "#ffffff" }}>
              Paket Tenda Dome 4P
            </div>
            <div style={{ fontSize: 13, fontWeight: 800, color: "#10b981", marginTop: 2 }}>
              Rp 85rb/hr
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

// =========================================================================
// SCREEN SCENE 2: INTERACTIVE CATALOG & TAP (REAL PHOTOS)
// =========================================================================
const ScreenCatalogScene: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  const enter = spring({ frame, fps, config: { damping: 14, stiffness: 85 } });

  const pointerX = interpolate(frame, [20, 65], [380, 280], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });
  const pointerY = interpolate(frame, [20, 65], [450, 180], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });
  const pointerTapScale = interpolate(frame, [65, 75, 85], [1, 0.8, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });
  const rippleScale = interpolate(frame, [70, 100], [0.5, 2.5], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });
  const rippleOpacity = interpolate(frame, [70, 100], [0.8, 0], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  });

  return (
    <div
      style={{
        padding: "20px 24px",
        display: "flex",
        flexDirection: "column",
        gap: 16,
        transform: `translateY(${interpolate(enter, [0, 1], [30, 0])}px)`,
        opacity: enter,
        position: "relative",
      }}
    >
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
        }}
      >
        <div>
          <span style={{ fontSize: 13, fontWeight: 700, color: "#00f2fe" }}>
            Kamera & Lensa di Semarang
          </span>
          <h3
            style={{
              fontSize: 22,
              fontWeight: 900,
              color: "#ffffff",
              margin: "2px 0 0 0",
            }}
          >
            Paling Populer & Terlaris 🔥
          </h3>
        </div>
        <div
          style={{
            fontSize: 13,
            fontWeight: 800,
            color: "#38bdf8",
            background: "rgba(0, 242, 254, 0.15)",
            border: "1px solid rgba(0, 242, 254, 0.4)",
            padding: "4px 12px",
            borderRadius: 8,
          }}
        >
          Filter (3)
        </div>
      </div>

      {/* Target Card: Sony Alpha 7 IV (WITH REAL PHOTO!) */}
      <div
        style={{
          background: "rgba(15, 23, 42, 0.9)",
          borderRadius: 24,
          padding: 18,
          border:
            frame >= 70 ? "2.5px solid #00f2fe" : "1.5px solid rgba(255, 255, 255, 0.12)",
          boxShadow:
            frame >= 70
              ? "0 0 35px rgba(0, 242, 254, 0.45)"
              : "0 10px 30px rgba(0,0,0,0.3)",
          position: "relative",
          transform: frame >= 68 && frame <= 78 ? "scale(0.97)" : "scale(1)",
        }}
      >
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
            marginBottom: 12,
          }}
        >
          <div
            style={{
              background: "rgba(16, 185, 129, 0.2)",
              color: "#34d399",
              fontSize: 12,
              fontWeight: 800,
              padding: "4px 10px",
              borderRadius: 6,
              display: "flex",
              alignItems: "center",
              gap: 4,
            }}
          >
            <BadgeCheck size={14} />
            <span>Verified Official Store</span>
          </div>

          <div
            style={{
              display: "flex",
              alignItems: "center",
              gap: 4,
              fontSize: 13,
              fontWeight: 800,
              color: "#fbbf24",
            }}
          >
            <Star size={15} fill="#fbbf24" />
            <span>4.9 (128 ulasan)</span>
          </div>
        </div>

        <div style={{ display: "flex", gap: 16, alignItems: "center" }}>
          {/* REAL SONY A7 IV PHOTO */}
          <div
            style={{
              width: 120,
              height: 120,
              borderRadius: 18,
              overflow: "hidden",
              border: "1.5px solid rgba(0, 242, 254, 0.3)",
              flexShrink: 0,
            }}
          >
            <Img
              src={staticFile("assets/camera-sony.jpg")}
              style={{ width: "100%", height: "100%", objectFit: "cover" }}
            />
          </div>

          <div style={{ flex: 1 }}>
            <h4
              style={{
                fontSize: 20,
                fontWeight: 900,
                color: "#ffffff",
                margin: "0 0 4px 0",
                lineHeight: 1.2,
              }}
            >
              {props.featuredProductName}
            </h4>
            <div style={{ fontSize: 13, color: "#94a3b8", marginBottom: 8 }}>
              Rentify Official Store • Semarang
            </div>

            <div style={{ display: "flex", alignItems: "baseline", gap: 10 }}>
              <span style={{ fontSize: 22, fontWeight: 900, color: "#00f2fe" }}>
                {props.rentalDailyPrice}
              </span>
              <span
                style={{
                  fontSize: 13,
                  color: "#64748b",
                  textDecoration: "line-through",
                }}
              >
                {props.retailPrice}
              </span>
            </div>
          </div>
        </div>
      </div>

      {/* Second Card: Outdoor Camping Tent (REAL PHOTO) */}
      <div
        style={{
          background: "rgba(15, 23, 42, 0.75)",
          borderRadius: 22,
          padding: 16,
          border: "1px solid rgba(255, 255, 255, 0.1)",
          display: "flex",
          gap: 16,
          alignItems: "center",
        }}
      >
        <div
          style={{
            width: 90,
            height: 90,
            borderRadius: 16,
            overflow: "hidden",
            flexShrink: 0,
          }}
        >
          <Img
            src={staticFile("assets/camping-tent.jpg")}
            style={{ width: "100%", height: "100%", objectFit: "cover" }}
          />
        </div>
        <div style={{ flex: 1 }}>
          <h5 style={{ fontSize: 17, fontWeight: 900, color: "#ffffff", margin: "0 0 4px 0" }}>
            Tenda Dome Arpenaz 4.1 Family
          </h5>
          <div style={{ fontSize: 13, color: "#94a3b8" }}>Semarang Adventure</div>
          <div style={{ fontSize: 18, fontWeight: 900, color: "#10b981", marginTop: 4 }}>
            Rp 45.000 / hari
          </div>
        </div>
      </div>

      {/* Simulated Finger Pointer & Tap Ripple */}
      {frame >= 20 && (
        <div
          style={{
            position: "absolute",
            left: pointerX,
            top: pointerY,
            pointerEvents: "none",
            zIndex: 100,
          }}
        >
          {frame >= 70 && (
            <div
              style={{
                position: "absolute",
                top: -20,
                left: -20,
                width: 60,
                height: 60,
                borderRadius: "50%",
                background: "rgba(0, 242, 254, 0.4)",
                transform: `scale(${rippleScale})`,
                opacity: rippleOpacity,
              }}
            />
          )}

          <div
            style={{
              width: 36,
              height: 36,
              borderRadius: "50%",
              background: "#00f2fe",
              border: "3px solid #ffffff",
              boxShadow: "0 0 25px rgba(0, 242, 254, 0.8)",
              transform: `scale(${pointerTapScale})`,
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
            }}
          >
            <div
              style={{
                width: 10,
                height: 10,
                borderRadius: "50%",
                background: "#0f172a",
              }}
            />
          </div>
        </div>
      )}
    </div>
  );
};

// =========================================================================
// SCREEN SCENE 3: PRODUCT DETAIL PAGE & 3 TRUST PILLARS
// =========================================================================
const ScreenPDPScene: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  const enter = spring({ frame, fps, config: { damping: 14, stiffness: 85 } });

  return (
    <div
      style={{
        padding: "20px 24px",
        display: "flex",
        flexDirection: "column",
        gap: 14,
        transform: `translateY(${interpolate(enter, [0, 1], [30, 0])}px)`,
        opacity: enter,
      }}
    >
      {/* Product Hero Image */}
      <div
        style={{
          width: "100%",
          height: 220,
          borderRadius: 24,
          overflow: "hidden",
          position: "relative",
          border: "2px solid rgba(0, 242, 254, 0.4)",
          boxShadow: "0 10px 30px rgba(0,0,0,0.5)",
        }}
      >
        <Img
          src={staticFile("assets/camera-sony.jpg")}
          style={{ width: "100%", height: "100%", objectFit: "cover" }}
        />
        <div
          style={{
            position: "absolute",
            top: 12,
            right: 12,
            background: "rgba(15, 23, 42, 0.85)",
            border: "1px solid rgba(0, 242, 254, 0.4)",
            padding: "6px 12px",
            borderRadius: 999,
            display: "flex",
            alignItems: "center",
            gap: 6,
            fontSize: 12,
            fontWeight: 800,
            color: "#00f2fe",
          }}
        >
          <Zap size={14} color="#00f2fe" fill="#00f2fe" />
          <span>Instant Booking Ready</span>
        </div>
      </div>

      {/* Product Title & Rate */}
      <div>
        <h3
          style={{
            fontSize: 24,
            fontWeight: 900,
            color: "#ffffff",
            margin: "0 0 6px 0",
            letterSpacing: -0.5,
          }}
        >
          {props.featuredProductName}
        </h3>
        <div style={{ display: "flex", alignItems: "baseline", gap: 12 }}>
          <span style={{ fontSize: 26, fontWeight: 900, color: "#00f2fe" }}>
            {props.rentalDailyPrice}
          </span>
          <span
            style={{
              background: "rgba(16, 185, 129, 0.2)",
              color: "#34d399",
              border: "1px solid rgba(16, 185, 129, 0.4)",
              padding: "2px 10px",
              borderRadius: 999,
              fontSize: 13,
              fontWeight: 800,
            }}
          >
            {props.savingsPercentage}
          </span>
        </div>
      </div>

      {/* 3 Trust Pillars Badges */}
      <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
        <div
          style={{
            background: "rgba(15, 23, 42, 0.8)",
            padding: "12px 16px",
            borderRadius: 16,
            border: "1px solid rgba(0, 242, 254, 0.3)",
            display: "flex",
            alignItems: "center",
            gap: 12,
          }}
        >
          <ShieldCheck size={22} color="#00f2fe" />
          <span style={{ fontSize: 14, fontWeight: 700, color: "#e2e8f0" }}>
            Penyewa & Pemilik Wajib KYC Biometrik
          </span>
        </div>
        <div
          style={{
            background: "rgba(15, 23, 42, 0.8)",
            padding: "12px 16px",
            borderRadius: 16,
            border: "1px solid rgba(16, 185, 129, 0.3)",
            display: "flex",
            alignItems: "center",
            gap: 12,
          }}
        >
          <CheckCircle2 size={22} color="#10b981" />
          <span style={{ fontSize: 14, fontWeight: 700, color: "#e2e8f0" }}>
            Digital Manifest Serah Terima Kondisi Fisik
          </span>
        </div>
      </div>

      {/* Bottom Action CTA */}
      <div
        style={{
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          padding: "16px",
          borderRadius: 18,
          textAlign: "center",
          boxShadow: "0 0 25px rgba(0, 242, 254, 0.4)",
        }}
      >
        <span style={{ fontSize: 18, fontWeight: 900, color: "#ffffff" }}>
          Lanjut ke Pembayaran QRIS 👉
        </span>
      </div>
    </div>
  );
};

// =========================================================================
// SCREEN SCENE 4: CHECKOUT & INSTANT QRIS
// =========================================================================
const ScreenCheckoutScene: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  const enter = spring({ frame, fps, config: { damping: 14, stiffness: 85 } });

  const voucherApplied = frame >= 35;
  const voucherBounce = spring({
    frame: frame - 35,
    fps,
    config: { damping: 10, stiffness: 120 },
  });

  const paymentDone = frame >= 75;
  const modalEnter = spring({
    frame: frame - 75,
    fps,
    config: { damping: 12, stiffness: 100 },
  });

  const laserY = interpolate((frame * 3) % 60, [0, 60], [0, 80]);

  return (
    <div
      style={{
        padding: "20px 24px",
        display: "flex",
        flexDirection: "column",
        gap: 14,
        transform: `translateY(${interpolate(enter, [0, 1], [30, 0])}px)`,
        opacity: enter,
        position: "relative",
      }}
    >
      <div style={{ fontSize: 20, fontWeight: 900, color: "#ffffff" }}>
        Ringkasan Sewa & Pembayaran
      </div>

      {/* Order Item Card */}
      <div
        style={{
          background: "rgba(15, 23, 42, 0.8)",
          borderRadius: 18,
          padding: 16,
          border: "1px solid rgba(255, 255, 255, 0.12)",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
        }}
      >
        <div>
          <div style={{ fontSize: 16, fontWeight: 900, color: "#ffffff" }}>
            {props.featuredProductName}
          </div>
          <div style={{ fontSize: 13, color: "#94a3b8" }}>
            Durasi: 3 Hari (14 - 16 Nov)
          </div>
        </div>
        <div style={{ fontSize: 18, fontWeight: 900, color: "#00f2fe" }}>
          Rp 750.000
        </div>
      </div>

      {/* Voucher Code Applied Box */}
      <div
        style={{
          background: voucherApplied ? "rgba(16, 185, 129, 0.2)" : "rgba(15, 23, 42, 0.8)",
          borderRadius: 16,
          padding: "12px 18px",
          border: voucherApplied ? "2px solid #10b981" : "1.5px solid rgba(255, 255, 255, 0.2)",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          transform: voucherApplied ? `scale(${voucherBounce})` : "none",
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 10 }}>
          <Tag size={18} color={voucherApplied ? "#34d399" : "#00f2fe"} />
          <span
            style={{
              fontSize: 15,
              fontWeight: 800,
              color: voucherApplied ? "#34d399" : "#ffffff",
            }}
          >
            {voucherApplied ? props.voucherCode : "Masukkan Kode Voucher"}
          </span>
        </div>
        {voucherApplied ? (
          <div
            style={{
              background: "#10b981",
              color: "#ffffff",
              fontSize: 13,
              fontWeight: 900,
              padding: "4px 10px",
              borderRadius: 8,
            }}
          >
            {props.voucherDiscountAmount}
          </div>
        ) : (
          <span style={{ fontSize: 13, color: "#00f2fe", fontWeight: 700 }}>
            Terapkan
          </span>
        )}
      </div>

      {/* Total Due */}
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          padding: "6px 8px",
        }}
      >
        <span style={{ fontSize: 16, fontWeight: 700, color: "#94a3b8" }}>
          Total Tagihan
        </span>
        <span style={{ fontSize: 26, fontWeight: 900, color: "#00f2fe" }}>
          {voucherApplied ? "Rp 690.000" : "Rp 750.000"}
        </span>
      </div>

      {/* Instant QRIS Section (WITH REAL QRIS IMAGE!) */}
      <div
        style={{
          background: "rgba(15, 23, 42, 0.9)",
          borderRadius: 20,
          padding: 16,
          border: "2px solid rgba(0, 242, 254, 0.35)",
          display: "flex",
          alignItems: "center",
          gap: 16,
          position: "relative",
          overflow: "hidden",
        }}
      >
        <div
          style={{
            width: 100,
            height: 100,
            borderRadius: 14,
            overflow: "hidden",
            background: "#ffffff",
            padding: 6,
            position: "relative",
            flexShrink: 0,
          }}
        >
          <Img
            src={staticFile("assets/qris.png")}
            style={{ width: "100%", height: "100%", objectFit: "contain" }}
          />
          {/* Laser Scan Line */}
          <div
            style={{
              position: "absolute",
              top: laserY,
              left: 0,
              right: 0,
              height: 3,
              background: "#ef4444",
              boxShadow: "0 0 10px #ef4444",
            }}
          />
        </div>

        <div>
          <div style={{ display: "flex", alignItems: "center", gap: 6 }}>
            <QrCode size={18} color="#00f2fe" />
            <span style={{ fontSize: 15, fontWeight: 900, color: "#ffffff" }}>
              Instant QRIS Terverifikasi
            </span>
          </div>
          <p style={{ fontSize: 12, color: "#94a3b8", margin: "4px 0" }}>
            Scan via GoPay, OVO, Dana, ShopeePay, BCA, Mandiri
          </p>
          <span style={{ fontSize: 12, color: "#10b981", fontWeight: 800 }}>
            ⚡ Verifikasi Otomatis Detik Ini
          </span>
        </div>
      </div>

      {/* Payment Success Confirmation Popup */}
      {paymentDone && (
        <div
          style={{
            position: "absolute",
            inset: "20px 24px",
            background: "rgba(15, 23, 42, 0.95)",
            backdropFilter: "blur(20px)",
            borderRadius: 24,
            border: "2px solid #10b981",
            boxShadow: "0 0 40px rgba(16, 185, 129, 0.5)",
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            justifyContent: "center",
            padding: 24,
            transform: `scale(${modalEnter})`,
            zIndex: 100,
          }}
        >
          <div
            style={{
              width: 68,
              height: 68,
              borderRadius: "50%",
              background: "#10b981",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              boxShadow: "0 0 25px rgba(16, 185, 129, 0.6)",
              marginBottom: 14,
            }}
          >
            <Check size={38} color="#ffffff" strokeWidth={3} />
          </div>
          <h3
            style={{
              fontSize: 24,
              fontWeight: 900,
              color: "#ffffff",
              margin: "0 0 6px 0",
            }}
          >
            Pembayaran Berhasil! 🎉
          </h3>
          <p style={{ fontSize: 14, color: "#cbd5e1", textAlign: "center", margin: 0 }}>
            Order terkonfirmasi. Barang siap diambil / diantar vendor!
          </p>
        </div>
      )}
    </div>
  );
};

// =========================================================================
// SCREEN SCENE 5: OWNER / MITRA PASSIVE INCOME MODE
// =========================================================================
const ScreenOwnerModeScene: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  const enter = spring({ frame, fps, config: { damping: 14, stiffness: 85 } });

  const simulatedNumber = Math.floor(
    interpolate(frame, [10, 65], [0, 3850000], {
      extrapolateLeft: "clamp",
      extrapolateRight: "clamp",
    })
  );
  const formattedIncome = "Rp " + simulatedNumber.toLocaleString("id-ID");

  return (
    <div
      style={{
        padding: "20px 24px",
        display: "flex",
        flexDirection: "column",
        gap: 16,
        transform: `translateY(${interpolate(enter, [0, 1], [30, 0])}px)`,
        opacity: enter,
      }}
    >
      <div>
        <span style={{ fontSize: 13, fontWeight: 800, color: "#10b981" }}>
          PORTAL MITRA VENDOR
        </span>
        <h3 style={{ fontSize: 24, fontWeight: 900, color: "#ffffff", margin: "2px 0 0 0" }}>
          Punya Barang Nganggur? 💰
        </h3>
      </div>

      {/* Wallet Card */}
      <div
        style={{
          background: "linear-gradient(135deg, #064e3b 0%, #061c15 100%)",
          borderRadius: 24,
          padding: 24,
          border: "2px solid rgba(16, 185, 129, 0.4)",
          boxShadow: "0 0 35px rgba(16, 185, 129, 0.25)",
        }}
      >
        <span style={{ fontSize: 14, color: "#a7f3d0", fontWeight: 700 }}>
          Total Pasif Income Bulan Ini
        </span>
        <h2
          style={{
            fontSize: 44,
            fontWeight: 900,
            color: "#34d399",
            margin: "8px 0 14px 0",
            letterSpacing: -1,
          }}
        >
          {formattedIncome}
        </h2>

        <div
          style={{
            background: "rgba(16, 185, 129, 0.25)",
            border: "1px solid rgba(16, 185, 129, 0.5)",
            padding: "6px 14px",
            borderRadius: 999,
            display: "inline-flex",
            alignItems: "center",
            gap: 8,
            fontSize: 13,
            fontWeight: 900,
            color: "#ffffff",
          }}
        >
          <Sparkles size={16} />
          <span>0% Komisi Pemilik Barang</span>
        </div>
      </div>

      <div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
        <div
          style={{
            background: "rgba(15, 23, 42, 0.8)",
            padding: "12px 16px",
            borderRadius: 14,
            border: "1px solid rgba(255, 255, 255, 0.1)",
            display: "flex",
            alignItems: "center",
            gap: 10,
          }}
        >
          <CheckCircle2 size={20} color="#10b981" />
          <span style={{ fontSize: 14, color: "#e2e8f0", fontWeight: 700 }}>
            Penyewa Wajib KYC KTP & Liveness Check
          </span>
        </div>
        <div
          style={{
            background: "rgba(15, 23, 42, 0.8)",
            padding: "12px 16px",
            borderRadius: 14,
            border: "1px solid rgba(255, 255, 255, 0.1)",
            display: "flex",
            alignItems: "center",
            gap: 10,
          }}
        >
          <CreditCard size={20} color="#10b981" />
          <span style={{ fontSize: 14, color: "#e2e8f0", fontWeight: 700 }}>
            Pencairan Saldo Otomatis ke Semua Rekening
          </span>
        </div>
      </div>
    </div>
  );
};

// =========================================================================
// SCREEN SCENE 6: FINAL OVERVIEW
// =========================================================================
const ScreenOutroOverview: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  return (
    <div
      style={{
        padding: 30,
        display: "flex",
        flexDirection: "column",
        alignItems: "center",
        justifyContent: "center",
        height: "100%",
        textAlign: "center",
      }}
    >
      <div
        style={{
          width: 90,
          height: 90,
          borderRadius: 26,
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          boxShadow: "0 0 40px rgba(0, 242, 254, 0.6)",
          marginBottom: 20,
        }}
      >
        <Sparkles size={50} color="#ffffff" />
      </div>

      <h3
        style={{
          fontSize: 32,
          fontWeight: 900,
          color: "#ffffff",
          margin: "0 0 8px 0",
        }}
      >
        {props.brandName} App
      </h3>

      <p style={{ fontSize: 16, color: "#94a3b8", margin: "0 0 24px 0" }}>
        Sewa Pintar, Hidup Fleksibel.
      </p>

      <div
        style={{
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          color: "#ffffff",
          padding: "12px 30px",
          borderRadius: 999,
          fontSize: 16,
          fontWeight: 800,
        }}
      >
        rentify21.my.id
      </div>
    </div>
  );
};

// =========================================================================
// FLOATING MARKETING FEATURE BADGES (CYBER DARK GLASS)
// =========================================================================
const FloatingFeatureBadges: React.FC<{ frame: number; fps: number }> = ({
  frame,
  fps,
}) => {
  const b1 = spring({ frame: frame - 15, fps, config: { damping: 14 } });
  const b2 = spring({ frame: frame - 120, fps, config: { damping: 14 } });

  return (
    <>
      {frame >= 15 && frame < 180 && (
        <div
          style={{
            position: "absolute",
            top: 260,
            left: 40,
            transform: `scale(${b1})`,
            opacity: b1,
            background: "rgba(15, 23, 42, 0.88)",
            backdropFilter: "blur(16px)",
            padding: "12px 20px",
            borderRadius: 18,
            boxShadow: "0 0 30px rgba(0, 242, 254, 0.25)",
            border: "1.5px solid rgba(0, 242, 254, 0.35)",
            display: "flex",
            alignItems: "center",
            gap: 12,
            zIndex: 35,
          }}
        >
          <Sparkles size={20} color="#00f2fe" />
          <span style={{ fontSize: 16, fontWeight: 800, color: "#ffffff" }}>
            1.000+ Alat Siap Pakai di Semarang
          </span>
        </div>
      )}

      {frame >= 120 && frame < 260 && (
        <div
          style={{
            position: "absolute",
            top: 260,
            right: 40,
            transform: `scale(${b2})`,
            opacity: b2,
            background: "rgba(15, 23, 42, 0.88)",
            backdropFilter: "blur(16px)",
            padding: "12px 20px",
            borderRadius: 18,
            boxShadow: "0 0 30px rgba(16, 185, 129, 0.25)",
            border: "1.5px solid rgba(16, 185, 129, 0.35)",
            display: "flex",
            alignItems: "center",
            gap: 12,
            zIndex: 35,
          }}
        >
          <ShieldCheck size={20} color="#34d399" />
          <span style={{ fontSize: 16, fontWeight: 800, color: "#ffffff" }}>
            Garansi & Proteksi 100% Aman
          </span>
        </div>
      )}
    </>
  );
};

// =========================================================================
// OUTRO CTA OVERLAY (FULL SCREEN HERO FINALE)
// =========================================================================
const OutroCTAOverlay: React.FC<{
  frame: number;
  fps: number;
  props: RentifyUIShowcaseProps;
}> = ({ frame, fps, props }) => {
  const enter = spring({ frame, fps, config: { damping: 13, stiffness: 90 } });
  const pulse = Math.sin(frame / 10) * 0.04;

  return (
    <AbsoluteFill
      style={{
        background: "rgba(7, 10, 18, 0.95)",
        backdropFilter: "blur(24px)",
        display: "flex",
        flexDirection: "column",
        justifyContent: "center",
        alignItems: "center",
        padding: "0 50px",
        zIndex: 100,
        opacity: enter,
      }}
    >
      <div
        style={{
          transform: `scale(${enter})`,
          width: 120,
          height: 120,
          borderRadius: 36,
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
          boxShadow: "0 0 60px rgba(0, 242, 254, 0.6)",
          marginBottom: 24,
        }}
      >
        <Sparkles size={66} color="#ffffff" />
      </div>

      <h2
        style={{
          fontSize: 60,
          fontWeight: 900,
          textAlign: "center",
          lineHeight: 1.15,
          letterSpacing: -2,
          margin: "0 0 16px 0",
          color: "#ffffff",
        }}
      >
        Solusi Sewa Pintar,
        <br />
        <span
          style={{
            background: "linear-gradient(135deg, #00f2fe 0%, #38bdf8 100%)",
            WebkitBackgroundClip: "text",
            WebkitTextFillColor: "transparent",
            filter: "drop-shadow(0 0 25px rgba(0, 242, 254, 0.5))",
          }}
        >
          Cuan Dari Barang Nganggur!
        </span>
      </h2>

      <p
        style={{
          fontSize: 24,
          color: "#cbd5e1",
          textAlign: "center",
          maxWidth: 800,
          lineHeight: 1.4,
          marginBottom: 32,
          fontWeight: 600,
        }}
      >
        Mau sewa kamera, tenda camping, atau gadget? Semua ada di Rentify. Dilindungi
        verifikasi biometrik & garansi 100% aman.
      </p>

      {/* Main Animated Glowing CTA */}
      <div
        style={{
          transform: `scale(${1 + pulse})`,
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          color: "#ffffff",
          padding: "22px 50px",
          borderRadius: 999,
          fontSize: 30,
          fontWeight: 900,
          letterSpacing: -0.5,
          boxShadow: "0 15px 45px rgba(0, 242, 254, 0.5)",
          display: "flex",
          alignItems: "center",
          gap: 16,
          marginBottom: 24,
        }}
      >
        <span>{props.ctaButtonText}</span>
        <ArrowRight size={34} strokeWidth={3} />
      </div>

      <div
        style={{
          background: "rgba(255, 255, 255, 0.08)",
          padding: "12px 32px",
          borderRadius: 999,
          border: "1.5px solid rgba(0, 242, 254, 0.4)",
          fontSize: 24,
          fontWeight: 800,
          color: "#00f2fe",
          boxShadow: "0 0 25px rgba(0, 242, 254, 0.2)",
        }}
      >
        🌐 {props.domainUrl}
      </div>

      <div
        style={{
          marginTop: 24,
          fontSize: 18,
          fontWeight: 700,
          color: "#94a3b8",
          display: "flex",
          alignItems: "center",
          gap: 8,
        }}
      >
        <Star size={20} fill="#fbbf24" color="#fbbf24" />
        <span>Dipercaya ribuan pengguna di Semarang & seluruh Indonesia</span>
      </div>
    </AbsoluteFill>
  );
};
