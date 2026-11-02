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
  Flame,
  Clock,
  ArrowRight,
  Tag,
  Zap,
  Sparkles,
} from "lucide-react";
import { RENTIFY_THEME } from "./theme";

export const flashDealSchema = z.object({
  promoTitle: z.string().default("FLASH RENTAL WEEKEND"),
  discountHighlight: z.string().default("DISKON 30% + CASHBACK KOIN"),
  validUntil: z.string().default("Hanya Berlaku Hingga Minggu Ini!"),
  voucherCode: z.string().default("RENTIFYWEEKEND"),
  highlightItems: z.array(z.string()).default([
    "📸 Kamera Sony & Canon Diskon s/d 30%",
    "⛺ Tenda & Cooking Set Camping Hemat 40%",
    "🚁 Drone DJI Mini Series Free Baterai Cadangan",
  ]),
});

export type FlashDealProps = z.infer<typeof flashDealSchema>;

export const FlashDealPromo: React.FC<FlashDealProps> = ({
  promoTitle,
  discountHighlight,
  validUntil,
  voucherCode,
}) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

  const audioVolume = interpolate(
    frame,
    [0, 20, 270, 300],
    [0, 0.75, 0.75, 0],
    { extrapolateLeft: "clamp", extrapolateRight: "clamp" }
  );

  const entrance = spring({
    frame,
    fps,
    config: { damping: 13, stiffness: 90 },
  });

  const bannerScale = spring({
    frame: frame - 10,
    fps,
    config: { damping: 14, stiffness: 85 },
  });

  const voucherSpring = spring({
    frame: frame - 25,
    fps,
    config: { damping: 12, stiffness: 95 },
  });

  const pulse = Math.sin(frame / 8) * 0.05;

  const deals = [
    {
      name: "Sony Alpha 7 IV Mirrorless",
      img: staticFile("assets/camera-sony.jpg"),
      discount: "Diskon 30%",
      price: "Rp 175rb/hari",
      cutPrice: "Rp 250rb",
    },
    {
      name: "Paket Tenda Camping 4P Waterproof",
      img: staticFile("assets/camping-tent.jpg"),
      discount: "Diskon 40%",
      price: "Rp 50rb/hari",
      cutPrice: "Rp 85rb",
    },
    {
      name: "DJI Mini 4 Pro Drone 4K",
      img: staticFile("assets/drone-tech.jpg"),
      discount: "Free Baterai",
      price: "Rp 120rb/hari",
      cutPrice: "Rp 180rb",
    },
  ];

  return (
    <AbsoluteFill
      style={{
        background:
          "radial-gradient(circle at 50% 25%, #450a0a 0%, #1c0505 50%, #0a0303 100%)",
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
      <Audio src={staticFile("assets/bgm-promo.mp3")} volume={audioVolume} />

      {/* Crimson / Red Flame Ambient Glows */}
      <div
        style={{
          position: "absolute",
          top: 150,
          left: -80,
          width: 750,
          height: 750,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(239, 68, 68, 0.3) 0%, transparent 70%)",
          filter: "blur(90px)",
          pointerEvents: "none",
        }}
      />
      <div
        style={{
          position: "absolute",
          bottom: 150,
          right: -80,
          width: 750,
          height: 750,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(245, 158, 11, 0.25) 0%, transparent 70%)",
          filter: "blur(90px)",
          pointerEvents: "none",
        }}
      />

      {/* Top Header */}
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
              background: "linear-gradient(135deg, #ef4444 0%, #f59e0b 100%)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              boxShadow: "0 0 25px rgba(239, 68, 68, 0.5)",
            }}
          >
            <Flame size={32} color="#ffffff" fill="#ffffff" />
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
              Rentify Flash
            </h1>
            <span style={{ fontSize: 16, color: "#fca5a5", fontWeight: 600 }}>
              rentify21.my.id
            </span>
          </div>
        </div>

        <div
          style={{
            background: "rgba(239, 68, 68, 0.25)",
            color: "#fca5a5",
            border: "1.5px solid rgba(239, 68, 68, 0.5)",
            padding: "8px 22px",
            borderRadius: 999,
            fontSize: 18,
            fontWeight: 800,
            display: "flex",
            alignItems: "center",
            gap: 8,
          }}
        >
          <Clock size={20} color="#fca5a5" />
          <span>{validUntil}</span>
        </div>
      </div>

      {/* Promo Banner Headline */}
      <div
        style={{
          transform: `scale(${bannerScale})`,
          textAlign: "center",
          margin: "10px 0",
        }}
      >
        <div
          style={{
            display: "inline-flex",
            alignItems: "center",
            gap: 8,
            background: "rgba(239, 68, 68, 0.2)",
            border: "1.5px solid rgba(239, 68, 68, 0.5)",
            color: "#f87171",
            padding: "8px 22px",
            borderRadius: 999,
            fontSize: 20,
            fontWeight: 800,
            marginBottom: 16,
          }}
        >
          <Zap size={22} color="#f87171" fill="#f87171" />
          <span>PROMO SPESIAL LIBURAN & AKHIR PEKAN</span>
        </div>

        <h2
          style={{
            fontSize: 56,
            fontWeight: 900,
            color: "#ffffff",
            margin: "0 0 10px 0",
            letterSpacing: -2,
            lineHeight: 1.15,
          }}
        >
          {promoTitle}
          <br />
          <span
            style={{
              background: "linear-gradient(135deg, #f87171 0%, #fbbf24 100%)",
              WebkitBackgroundClip: "text",
              WebkitTextFillColor: "transparent",
              filter: "drop-shadow(0 0 25px rgba(239, 68, 68, 0.5))",
            }}
          >
            {discountHighlight}
          </span>
        </h2>
      </div>

      {/* 3 Flash Deal Real Product Cards */}
      <div style={{ display: "flex", flexDirection: "column", gap: 16 }}>
        {deals.map((item, idx) => {
          const itemSpring = spring({
            frame: frame - 15 - idx * 8,
            fps,
            config: { damping: 14, stiffness: 90 },
          });

          return (
            <div
              key={idx}
              style={{
                transform: `translateX(${interpolate(itemSpring, [0, 1], [-60, 0])}px)`,
                opacity: itemSpring,
                background: "rgba(28, 5, 5, 0.85)",
                backdropFilter: "blur(20px)",
                borderRadius: 24,
                padding: "14px 20px",
                border: "1.5px solid rgba(239, 68, 68, 0.3)",
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between",
                gap: 16,
              }}
            >
              <div
                style={{
                  width: 84,
                  height: 84,
                  borderRadius: 16,
                  overflow: "hidden",
                  flexShrink: 0,
                  border: "1.5px solid rgba(239, 68, 68, 0.4)",
                }}
              >
                <Img
                  src={item.img}
                  style={{ width: "100%", height: "100%", objectFit: "cover" }}
                />
              </div>

              <div style={{ flex: 1 }}>
                <span
                  style={{
                    background: "rgba(239, 68, 68, 0.25)",
                    color: "#fca5a5",
                    fontSize: 13,
                    fontWeight: 800,
                    padding: "2px 8px",
                    borderRadius: 6,
                  }}
                >
                  {item.discount}
                </span>
                <h4
                  style={{
                    fontSize: 22,
                    fontWeight: 900,
                    color: "#ffffff",
                    margin: "4px 0",
                  }}
                >
                  {item.name}
                </h4>
                <span
                  style={{
                    fontSize: 15,
                    color: "#991b1b",
                    textDecoration: "line-through",
                    fontWeight: 700,
                  }}
                >
                  {item.cutPrice}
                </span>
              </div>

              <div
                style={{
                  textAlign: "right",
                  background: "rgba(239, 68, 68, 0.15)",
                  padding: "10px 16px",
                  borderRadius: 16,
                  border: "1px solid rgba(239, 68, 68, 0.35)",
                }}
              >
                <span style={{ fontSize: 12, color: "#fca5a5", display: "block" }}>
                  Mulai Dari
                </span>
                <span style={{ fontSize: 22, fontWeight: 900, color: "#fbbf24" }}>
                  {item.price}
                </span>
              </div>
            </div>
          );
        })}
      </div>

      {/* Big Glowing Voucher Code Box */}
      <div
        style={{
          transform: `scale(${voucherSpring})`,
          opacity: voucherSpring,
          background: "rgba(245, 158, 11, 0.12)",
          border: "2px dashed #f59e0b",
          borderRadius: 24,
          padding: "18px 28px",
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          boxShadow: "0 0 30px rgba(245, 158, 11, 0.25)",
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 14 }}>
          <Tag size={32} color="#fbbf24" />
          <div>
            <span style={{ fontSize: 15, color: "#cbd5e1", fontWeight: 700, display: "block" }}>
              Gunakan Voucher Saat Checkout:
            </span>
            <span
              style={{
                fontSize: 34,
                fontWeight: 900,
                color: "#fbbf24",
                letterSpacing: 2,
              }}
            >
              {voucherCode}
            </span>
          </div>
        </div>

        <span
          style={{
            background: "#f59e0b",
            color: "#0f172a",
            fontWeight: 900,
            fontSize: 16,
            padding: "8px 18px",
            borderRadius: 12,
          }}
        >
          Klaim Diskon
        </span>
      </div>

      {/* CTA Button */}
      <div
        style={{
          transform: `scale(1 + ${pulse})`,
          background: "linear-gradient(135deg, #ef4444 0%, #f59e0b 100%)",
          padding: "22px 40px",
          borderRadius: 999,
          boxShadow: "0 15px 40px rgba(239, 68, 68, 0.5)",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          zIndex: 50,
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 14 }}>
          <Sparkles size={28} color="#ffffff" />
          <span style={{ fontSize: 24, fontWeight: 900, color: "#ffffff" }}>
            Booking Promo di rentify21.my.id
          </span>
        </div>
        <ArrowRight size={32} color="#ffffff" strokeWidth={3} />
      </div>
    </AbsoluteFill>
  );
};
