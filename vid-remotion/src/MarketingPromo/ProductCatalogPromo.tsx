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
  Star,
  Sparkles,
  MapPin,
  ArrowRight,
  Zap,
  Tag,
  BadgeCheck,
  Check,
} from "lucide-react";
import { RENTIFY_THEME } from "./theme";

export const productCatalogSchema = z.object({
  productName: z.string().default("Sony Alpha 7 IV Mirrorless Camera"),
  category: z.string().default("Kamera & Lensa"),
  rentalPrice: z.string().default("Rp 250.000 / hari"),
  retailPrice: z.string().default("Rp 36.500.000"),
  savingsPercent: z.string().default("Hemat 98%"),
  vendorName: z.string().default("Rentify Official Store"),
  vendorCity: z.string().default("Semarang"),
  rating: z.number().default(4.9),
  reviewCount: z.number().default(128),
  badgeText: z.string().default("⚡ Terlaris Minggu Ini"),
  features: z.array(z.string()).default([
    "Sensor 33MP Full-Frame Exmor R",
    "Perekaman Video 4K 60p 10-Bit",
    "Real-time Eye AF & 5-Axis In-Body Stabilization",
    "Termasuk 2 Baterai + Charger Ganda + SD Card",
  ]),
});

export type ProductCatalogProps = z.infer<typeof productCatalogSchema>;

export const ProductCatalogPromo: React.FC<ProductCatalogProps> = ({
  productName,
  category,
  rentalPrice,
  retailPrice,
  savingsPercent,
  vendorName,
  vendorCity,
  rating,
  reviewCount,
  badgeText,
  features,
}) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  // BGM audio volume
  const audioVolume = interpolate(
    frame,
    [0, 20, 330, 360],
    [0, 0.75, 0.75, 0],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  const entrance = spring({
    frame,
    fps,
    config: { damping: 14, stiffness: 85 },
  });

  const photoProgress = spring({
    frame: frame - 10,
    fps,
    config: { damping: 13, stiffness: 90 },
  });

  const priceProgress = spring({
    frame: frame - 25,
    fps,
    config: { damping: 14, stiffness: 85 },
  });

  const featuresProgress = spring({
    frame: frame - 40,
    fps,
    config: { damping: 15, stiffness: 80 },
  });

  const ctaProgress = spring({
    frame: frame - 55,
    fps,
    config: { damping: 12, stiffness: 90 },
  });

  // Floating breathing motion for camera photo
  const floatY = Math.sin(frame / 20) * 12;
  const pulse = Math.sin(frame / 12) * 0.03;

  return (
    <AbsoluteFill
      style={{
        background: RENTIFY_THEME.colors.bgGradient,
        fontFamily: RENTIFY_THEME.typography.fontFamily,
        color: RENTIFY_THEME.colors.textWhite,
        padding: "70px 50px",
        display: "flex",
        flexDirection: "column",
        justifyContent: "space-between",
        boxSizing: "border-box",
        overflow: "hidden",
      }}
    >
      {/* Background BGM */}
      <Audio src={staticFile("assets/bgm-promo.mp3")} volume={audioVolume} />

      {/* Ambient Neon Cyan & Amber Glows */}
      <div
        style={{
          position: "absolute",
          top: 150,
          left: -100,
          width: 700,
          height: 700,
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
          bottom: 100,
          right: -100,
          width: 750,
          height: 750,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(245, 158, 11, 0.2) 0%, transparent 70%)",
          filter: "blur(90px)",
          pointerEvents: "none",
        }}
      />

      {/* Top Header / Brand Bar */}
      <div
        style={{
          transform: `translateY(${interpolate(entrance, [0, 1], [-40, 0])}px)`,
          opacity: entrance,
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
            background: "rgba(245, 158, 11, 0.2)",
            color: "#fbbf24",
            border: "1.5px solid rgba(245, 158, 11, 0.5)",
            padding: "8px 22px",
            borderRadius: 999,
            fontSize: 18,
            fontWeight: 800,
            display: "flex",
            alignItems: "center",
            gap: 8,
            boxShadow: "0 0 20px rgba(245, 158, 11, 0.3)",
          }}
        >
          <Zap size={20} color="#fbbf24" fill="#fbbf24" />
          <span>{badgeText}</span>
        </div>
      </div>

      {/* Central Product Hero Section (REAL IMAGE) */}
      <div
        style={{
          transform: `translateY(${interpolate(photoProgress, [0, 1], [60, 0]) + floatY}px) scale(${photoProgress})`,
          opacity: photoProgress,
          position: "relative",
          width: "100%",
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          margin: "10px 0",
        }}
      >
        {/* Large Cinematic Product Photo Card */}
        <div
          style={{
            width: "100%",
            height: 480,
            borderRadius: 36,
            overflow: "hidden",
            position: "relative",
            background: "linear-gradient(145deg, #0f172a 0%, #070a12 100%)",
            border: "2px solid rgba(0, 242, 254, 0.4)",
            boxShadow:
              "0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 35px rgba(0, 242, 254, 0.25)",
          }}
        >
          <Img
            src={staticFile("assets/camera-sony.jpg")}
            style={{
              width: "100%",
              height: "100%",
              objectFit: "cover",
            }}
          />

          {/* Bottom Dark Vignette Overlay */}
          <div
            style={{
              position: "absolute",
              inset: 0,
              background:
                "linear-gradient(180deg, rgba(7,10,18,0) 40%, rgba(7,10,18,0.92) 100%)",
            }}
          />

          {/* Floating Badges over the photo */}
          <div
            style={{
              position: "absolute",
              top: 24,
              left: 24,
              display: "flex",
              gap: 12,
            }}
          >
            <span
              style={{
                background: "rgba(15, 23, 42, 0.85)",
                backdropFilter: "blur(12px)",
                color: "#38bdf8",
                border: "1px solid rgba(56, 189, 248, 0.4)",
                padding: "8px 18px",
                borderRadius: 999,
                fontSize: 16,
                fontWeight: 800,
                textTransform: "uppercase",
              }}
            >
              {category}
            </span>
            <div
              style={{
                background: "rgba(15, 23, 42, 0.85)",
                backdropFilter: "blur(12px)",
                border: "1px solid rgba(255, 255, 255, 0.15)",
                padding: "8px 18px",
                borderRadius: 999,
                display: "flex",
                alignItems: "center",
                gap: 8,
              }}
            >
              <Star size={20} color="#f59e0b" fill="#f59e0b" />
              <span style={{ fontSize: 18, fontWeight: 900, color: "#ffffff" }}>
                {rating}
              </span>
              <span style={{ fontSize: 15, color: "#94a3b8" }}>
                ({reviewCount} review)
              </span>
            </div>
          </div>

          {/* Location & Verified Vendor on bottom of photo */}
          <div
            style={{
              position: "absolute",
              bottom: 24,
              left: 24,
              right: 24,
              display: "flex",
              justifyContent: "space-between",
              alignItems: "center",
            }}
          >
            <div>
              <div style={{ display: "flex", alignItems: "center", gap: 8 }}>
                <BadgeCheck size={22} color="#10b981" />
                <span style={{ fontSize: 22, fontWeight: 900, color: "#ffffff" }}>
                  {vendorName}
                </span>
              </div>
              <div style={{ display: "flex", alignItems: "center", gap: 6, marginTop: 4 }}>
                <MapPin size={16} color="#38bdf8" />
                <span style={{ fontSize: 16, color: "#cbd5e1", fontWeight: 600 }}>
                  {vendorCity} • Ready COD / Pengiriman Cepat
                </span>
              </div>
            </div>

            <span
              style={{
                background: "rgba(16, 185, 129, 0.25)",
                color: "#34d399",
                border: "1.5px solid rgba(16, 185, 129, 0.5)",
                padding: "6px 16px",
                borderRadius: 999,
                fontSize: 15,
                fontWeight: 800,
              }}
            >
              ✓ Terverifikasi KYC
            </span>
          </div>
        </div>

        {/* Big Bold Product Title */}
        <h2
          style={{
            fontSize: 48,
            fontWeight: 900,
            color: "#ffffff",
            margin: "24px 0 10px 0",
            lineHeight: 1.15,
            letterSpacing: -1.5,
            textAlign: "center",
          }}
        >
          {productName}
        </h2>
      </div>

      {/* Pricing Battle Card (Hemat 98%) */}
      <div
        style={{
          transform: `scale(${priceProgress})`,
          opacity: priceProgress,
          background: "rgba(15, 23, 42, 0.85)",
          backdropFilter: "blur(20px)",
          borderRadius: 28,
          padding: "24px 32px",
          border: "2px solid rgba(0, 242, 254, 0.35)",
          boxShadow: "0 15px 40px rgba(0, 0, 0, 0.4)",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
        }}
      >
        <div>
          <span style={{ fontSize: 16, color: "#94a3b8", display: "block", fontWeight: 700 }}>
            Harga Sewa di Rentify:
          </span>
          <div style={{ display: "flex", alignItems: "baseline", gap: 10 }}>
            <span
              style={{
                fontSize: 44,
                fontWeight: 900,
                color: "#00f2fe",
                letterSpacing: -1,
                lineHeight: 1.1,
                filter: "drop-shadow(0 0 15px rgba(0, 242, 254, 0.4))",
              }}
            >
              {rentalPrice}
            </span>
          </div>
        </div>

        <div style={{ textAlign: "right" }}>
          <span
            style={{
              fontSize: 17,
              color: "#64748b",
              textDecoration: "line-through",
              display: "block",
              fontWeight: 600,
            }}
          >
            Beli Baru: {retailPrice}
          </span>
          <div
            style={{
              background: "linear-gradient(90deg, #10b981 0%, #059669 100%)",
              color: "#ffffff",
              fontSize: 18,
              fontWeight: 900,
              padding: "6px 18px",
              borderRadius: 999,
              display: "inline-block",
              marginTop: 6,
              boxShadow: "0 0 20px rgba(16, 185, 129, 0.4)",
            }}
          >
            🔥 {savingsPercent}
          </div>
        </div>
      </div>

      {/* Included Specifications & Equipment List */}
      <div
        style={{
          transform: `translateY(${interpolate(featuresProgress, [0, 1], [40, 0])}px)`,
          opacity: featuresProgress,
          background: "rgba(15, 23, 42, 0.65)",
          backdropFilter: "blur(16px)",
          borderRadius: 24,
          padding: "20px 28px",
          border: "1.5px solid rgba(255, 255, 255, 0.1)",
        }}
      >
        <span
          style={{
            fontSize: 14,
            fontWeight: 800,
            color: "#38bdf8",
            textTransform: "uppercase",
            letterSpacing: 1,
            display: "block",
            marginBottom: 12,
          }}
        >
          Kelengkapan Paket (Siap Pakai Langsung):
        </span>
        <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 12 }}>
          {features.map((feat, idx) => (
            <div key={idx} style={{ display: "flex", alignItems: "center", gap: 10 }}>
              <div
                style={{
                  width: 22,
                  height: 22,
                  borderRadius: 6,
                  background: "rgba(16, 185, 129, 0.2)",
                  display: "flex",
                  alignItems: "center",
                  justifyContent: "center",
                  flexShrink: 0,
                }}
              >
                <Check size={16} color="#34d399" strokeWidth={3} />
              </div>
              <span style={{ fontSize: 16, color: "#e2e8f0", fontWeight: 600 }}>
                {feat}
              </span>
            </div>
          ))}
        </div>
      </div>

      {/* Main Animated Glowing CTA Button */}
      <div
        style={{
          transform: `scale(${ctaProgress * (1 + pulse)})`,
          opacity: ctaProgress,
          background: "linear-gradient(135deg, #0284c7 0%, #00f2fe 100%)",
          padding: "22px 40px",
          borderRadius: 999,
          boxShadow: "0 15px 40px rgba(0, 242, 254, 0.5)",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          zIndex: 50,
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 14 }}>
          <Tag size={28} color="#ffffff" />
          <span style={{ fontSize: 24, fontWeight: 900, color: "#ffffff" }}>
            Booking Sekarang di rentify21.my.id
          </span>
        </div>
        <ArrowRight size={32} color="#ffffff" strokeWidth={3} />
      </div>
    </AbsoluteFill>
  );
};
