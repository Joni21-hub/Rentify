import React from "react";
import {
  AbsoluteFill,
  interpolate,
  spring,
  useCurrentFrame,
  useVideoConfig,
  staticFile,
  Audio,
} from "remotion";
import { z } from "zod";
import {
  ArrowRight,
  TrendingUp,
  Sparkles,
  Wallet,
  CheckCircle2,
} from "lucide-react";
import { RENTIFY_THEME } from "./theme";

export const ownerYieldSchema = z.object({
  headline: z.string().default("Punya Alat Outdoor, Kamera, atau Drone?"),
  subheadline: z.string().default("Jangan Biarkan Menganggur Numpuk Debu di Lemari!"),
  sampleCategory: z.string().default("Kamera Full-Frame / Drone"),
  itemValue: z.string().default("Rp 15.000.000"),
  estimatedMonthly: z.number().default(3850000),
  platformFee: z.string().default("0% (100% Milikmu)"),
  trustPoints: z.array(z.string()).default([
    "0% Potongan Komisi Pemilik Barang",
    "Penyewa Wajib Verifikasi KTP & Biometrik",
    "Pencairan Saldo Otomatis ke Semua Bank",
    "Digital Manifest Dokumentasi Fisik Barang",
  ]),
});

export type OwnerYieldProps = z.infer<typeof ownerYieldSchema>;

export const OwnerYieldPromo: React.FC<OwnerYieldProps> = ({
  headline,
  subheadline,
  sampleCategory,
  itemValue,
  estimatedMonthly,
  platformFee,
  trustPoints,
}) => {
  const frame = useCurrentFrame();
  const { fps } = useVideoConfig();

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

  const counterProgress = spring({
    frame: frame - 15,
    fps,
    config: { damping: 16, stiffness: 60 },
  });

  const currentYield = Math.round(
    interpolate(counterProgress, [0, 1], [0, estimatedMonthly])
  );

  const pointsProgress = spring({
    frame: frame - 30,
    fps,
    config: { damping: 14, stiffness: 80 },
  });

  const ctaProgress = spring({
    frame: frame - 45,
    fps,
    config: { damping: 12, stiffness: 90 },
  });

  const pulse = Math.sin(frame / 10) * 0.04;

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
      <Audio src={staticFile("assets/bgm-promo.mp3")} volume={audioVolume} />

      {/* Cyber Gold & Amber Ambient Glows */}
      <div
        style={{
          position: "absolute",
          top: 150,
          left: -80,
          width: 750,
          height: 750,
          borderRadius: "50%",
          background:
            "radial-gradient(circle, rgba(245, 158, 11, 0.28) 0%, transparent 70%)",
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
            "radial-gradient(circle, rgba(16, 185, 129, 0.22) 0%, transparent 70%)",
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
              background: "linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%)",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              boxShadow: "0 0 25px rgba(245, 158, 11, 0.5)",
            }}
          >
            <TrendingUp size={30} color="#0f172a" strokeWidth={2.5} />
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
              Rentify Mitra
            </h1>
            <span style={{ fontSize: 16, color: "#cbd5e1", fontWeight: 600 }}>
              rentify21.my.id
            </span>
          </div>
        </div>

        <div
          style={{
            background: "rgba(16, 185, 129, 0.2)",
            color: "#34d399",
            border: "1.5px solid rgba(16, 185, 129, 0.5)",
            padding: "8px 22px",
            borderRadius: 999,
            fontSize: 18,
            fontWeight: 800,
            display: "flex",
            alignItems: "center",
            gap: 8,
          }}
        >
          <Sparkles size={20} color="#34d399" />
          <span>Passive Income</span>
        </div>
      </div>

      {/* Main Headline & Hook */}
      <div style={{ textAlign: "center", margin: "10px 0" }}>
        <div
          style={{
            display: "inline-flex",
            alignItems: "center",
            gap: 8,
            background: "rgba(245, 158, 11, 0.15)",
            border: "1px solid rgba(245, 158, 11, 0.4)",
            color: "#fbbf24",
            padding: "8px 20px",
            borderRadius: 999,
            fontSize: 18,
            fontWeight: 800,
            marginBottom: 16,
          }}
        >
          <span>PUNYA ALAT HOBI / KERJA DI RUMAH?</span>
        </div>

        <h2
          style={{
            fontSize: 52,
            fontWeight: 900,
            color: "#ffffff",
            margin: "0 0 14px 0",
            lineHeight: 1.15,
            letterSpacing: -1.8,
          }}
        >
          Jangan Biarkan Nganggur!
          <br />
          <span
            style={{
              background: "linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%)",
              WebkitBackgroundClip: "text",
              WebkitTextFillColor: "transparent",
              filter: "drop-shadow(0 0 25px rgba(245, 158, 11, 0.4))",
            }}
          >
            Ubah Jadi Mesin Uang
          </span>
        </h2>

        <p
          style={{
            fontSize: 22,
            color: "#94a3b8",
            maxWidth: 800,
            margin: "0 auto",
            lineHeight: 1.35,
            fontWeight: 600,
          }}
        >
          Daftarkan kamera, drone, tenda, atau PlayStation kamu dan nikmati transferan sewa tiap minggu!
        </p>
      </div>

      {/* Yield Simulator Box */}
      <div
        style={{
          background: "rgba(15, 23, 42, 0.85)",
          backdropFilter: "blur(24px)",
          borderRadius: 32,
          padding: "36px",
          border: "2px solid rgba(251, 191, 36, 0.4)",
          boxShadow: "0 20px 50px rgba(0, 0, 0, 0.5)",
          textAlign: "center",
        }}
      >
        <span style={{ fontSize: 18, color: "#94a3b8", fontWeight: 700, display: "block" }}>
          Estimasi Pendapatan Pasif Pemilik Barang:
        </span>
        <h3
          style={{
            fontSize: 66,
            fontWeight: 900,
            color: "#fbbf24",
            margin: "8px 0 12px 0",
            letterSpacing: -1.5,
            filter: "drop-shadow(0 0 25px rgba(245, 158, 11, 0.5))",
          }}
        >
          Rp {currentYield.toLocaleString("id-ID")}
          <span style={{ fontSize: 28, color: "#94a3b8", fontWeight: 700 }}> / bulan</span>
        </h3>

        {/* 0% Commission Badge */}
        <div
          style={{
            background: "linear-gradient(90deg, #10b981 0%, #059669 100%)",
            color: "#ffffff",
            padding: "8px 24px",
            borderRadius: 999,
            fontSize: 18,
            fontWeight: 900,
            boxShadow: "0 8px 25px rgba(16, 185, 129, 0.4)",
            display: "inline-flex",
            alignItems: "center",
            gap: 10,
            marginBottom: 20,
          }}
        >
          <Sparkles size={18} color="#ffffff" />
          <span>0% KOMISI PEMILIK (100% Hasil Sewa Milikmu!)</span>
        </div>

        {/* 4 Trust Points */}
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "1fr 1fr",
            gap: 14,
            textAlign: "left",
            opacity: pointsProgress,
            transform: `translateY(${interpolate(pointsProgress, [0, 1], [30, 0])}px)`,
          }}
        >
          {trustPoints.map((pt, idx) => (
            <div
              key={idx}
              style={{
                background: "rgba(255, 255, 255, 0.05)",
                padding: "14px 16px",
                borderRadius: 16,
                display: "flex",
                alignItems: "center",
                gap: 10,
              }}
            >
              <div style={{ flexShrink: 0 }}>
                <CheckCircle2 size={22} color="#10b981" />
              </div>
              <span style={{ fontSize: 16, color: "#e2e8f0", fontWeight: 700 }}>
                {pt}
              </span>
            </div>
          ))}
        </div>
      </div>

      {/* CTA Button */}
      <div
        style={{
          transform: `scale(${ctaProgress * (1 + pulse)})`,
          opacity: ctaProgress,
          background: "linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%)",
          padding: "22px 40px",
          borderRadius: 999,
          boxShadow: "0 15px 40px rgba(245, 158, 11, 0.45)",
          display: "flex",
          justifyContent: "space-between",
          alignItems: "center",
          zIndex: 50,
        }}
      >
        <div style={{ display: "flex", alignItems: "center", gap: 14 }}>
          <Wallet size={28} color="#0f172a" />
          <span style={{ fontSize: 24, fontWeight: 900, color: "#0f172a" }}>
            Daftarkan Barangmu di rentify21.my.id
          </span>
        </div>
        <ArrowRight size={32} color="#0f172a" strokeWidth={3} />
      </div>
    </AbsoluteFill>
  );
};
