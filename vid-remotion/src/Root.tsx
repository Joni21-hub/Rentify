import "./index.css";
import { Composition, staticFile } from "remotion";
import {
  CaptionedVideo,
  calculateCaptionedVideoMetadata,
  captionedVideoSchema,
} from "./CaptionedVideo";
import { RentifyBrandPromo } from "./MarketingPromo/RentifyBrandPromo";
import {
  ProductCatalogPromo,
  productCatalogSchema,
} from "./MarketingPromo/ProductCatalogPromo";
import {
  OwnerYieldPromo,
  ownerYieldSchema,
} from "./MarketingPromo/OwnerYieldPromo";
import {
  FlashDealPromo,
  flashDealSchema,
} from "./MarketingPromo/FlashDealPromo";
import {
  RentifyUIShowcase,
  rentifyUIShowcaseSchema,
} from "./MarketingPromo/RentifyUIShowcase";
import { HowItWorksPromo } from "./MarketingPromo/HowItWorksPromo";
import {
  OutdoorCampingPromo,
  outdoorCampingSchema,
} from "./MarketingPromo/OutdoorCampingPromo";

// Each <Composition> is an entry in the Remotion sidebar & CLI render target!

export const RemotionRoot: React.FC = () => {
  return (
    <>
      {/* 0. Flagship Interactive UI Showcase Reel (All Marketing Aspects: Search, Catalog, PDP, 3 Trust Pillars, Checkout, QRIS, Owner Yield, CTA) */}
      <Composition
        id="RentifyUIShowcase"
        component={RentifyUIShowcase}
        durationInFrames={600} // 20 seconds @ 30fps
        fps={30}
        width={1080}
        height={1920}
        schema={rentifyUIShowcaseSchema}
        defaultProps={{
          brandName: "Rentify",
          domainUrl: "rentify21.my.id",
          userCity: "Semarang",
          searchKeyword: "Sewa Sony A7 IV & Tenda",
          featuredProductName: "Sony Alpha 7 IV Mirrorless",
          rentalDailyPrice: "Rp 250.000 / hari",
          retailPrice: "Rp 36.500.000",
          savingsPercentage: "Hemat 98%",
          voucherCode: "RENTIFYWEEKEND",
          voucherDiscountAmount: "-Rp 50.000",
          ownerSimulatedMonthlyIncome: "Rp 3.850.000",
          ctaButtonText: "Mulai Sewa / Pasang Iklan",
        }}
      />
      {/* 1. Main 9:16 Dual-Sided Brand Reel (Penyewa vs Pemilik, 3 Pilar Keamanan) */}
      <Composition
        id="RentifyBrandPromo"
        component={RentifyBrandPromo}
        durationInFrames={450} // 15 seconds @ 30fps
        fps={30}
        width={1080}
        height={1920}
      />

      {/* 2. Dynamic Parameterized Product Catalog Promo */}
      <Composition
        id="ProductCatalogPromo"
        component={ProductCatalogPromo}
        durationInFrames={360} // 12 seconds @ 30fps
        fps={30}
        width={1080}
        height={1920}
        schema={productCatalogSchema}
        defaultProps={{
          productName: "Sony Alpha 7 IV Mirrorless Camera",
          category: "Kamera & Lensa",
          rentalPrice: "Rp 250.000 / hari",
          retailPrice: "Rp 36.500.000",
          savingsPercent: "Hemat 98%",
          vendorName: "Rentify Official Store",
          vendorCity: "Semarang",
          rating: 4.9,
          reviewCount: 128,
          badgeText: "⚡ Terlaris Minggu Ini",
          features: [
            "Sensor 33MP Full-Frame Exmor R",
            "Perekaman Video 4K 60p 10-Bit",
            "Real-time Eye AF & 5-Axis In-Body Stabilization",
            "Termasuk 2 Baterai + Charger Ganda",
          ],
        }}
      />

      {/* 3. Owner Yield / Vendor Acquisition Reel */}
      <Composition
        id="OwnerYieldPromo"
        component={OwnerYieldPromo}
        durationInFrames={360} // 12 seconds @ 30fps
        fps={30}
        width={1080}
        height={1920}
        schema={ownerYieldSchema}
        defaultProps={{
          headline: "Punya Alat Outdoor, Kamera, atau Drone?",
          subheadline: "Jangan Biarkan Menganggur Numpuk Debu di Lemari!",
          sampleCategory: "Kamera Full-Frame / Drone",
          itemValue: "Rp 15.000.000",
          estimatedMonthly: 2400000,
          platformFee: "0% (100% Milikmu)",
          trustPoints: [
            "0% Potongan Komisi Pemilik Barang",
            "Penyewa Wajib Verifikasi KTP & Biometrik",
            "Pencairan Saldo Otomatis ke Rekening Bank",
            "Digital Manifest Dokumentasi Fisik Barang",
          ],
        }}
      />

      {/* 4. Flash Deal / Weekend Promo Reel */}
      <Composition
        id="FlashDealPromo"
        component={FlashDealPromo}
        durationInFrames={300} // 10 seconds @ 30fps
        fps={30}
        width={1080}
        height={1920}
        schema={flashDealSchema}
        defaultProps={{
          promoTitle: "FLASH RENTAL WEEKEND",
          discountHighlight: "DISKON 30% + CASHBACK KOIN",
          validUntil: "Hanya Berlaku Hingga Minggu Ini!",
          voucherCode: "RENTIFYWEEKEND",
          highlightItems: [
            "📸 Kamera Sony & Canon Diskon s/d 30%",
            "⛺ Tenda & Cooking Set Camping Hemat 40%",
            "🚁 Drone DJI Mini Series Free Baterai Cadangan",
          ],
        }}
      />

      {/* 5. How It Works (4-Step Rental Flow Educational Reel) */}
      <Composition
        id="HowItWorksPromo"
        component={HowItWorksPromo}
        durationInFrames={360} // 12 seconds @ 30fps
        fps={30}
        width={1080}
        height={1920}
      />

      {/* 6. Outdoor & Camping Gear Catalog Reel */}
      <Composition
        id="OutdoorCampingPromo"
        component={OutdoorCampingPromo}
        durationInFrames={360} // 12 seconds @ 30fps
        fps={30}
        width={1080}
        height={1920}
        schema={outdoorCampingSchema}
        defaultProps={{
          productName: "Paket Camping Gunung 4 Orang (Tenda + Cooking Set)",
          category: "Peralatan Outdoor & Camping",
          rentalPrice: "Rp 85.000 / hari",
          retailPrice: "Rp 3.250.000",
          savingsPercent: "Hemat 97%",
          vendorName: "Semarang Adventure Outdoor",
          vendorCity: "Semarang",
          rating: 4.95,
          reviewCount: 94,
          badgeText: "🌲 Rekomendasi Naik Gunung",
          features: [
            "Tenda Dome Double Layer Waterproof 4P",
            "4 Sleeping Bag Polar Thermal + Matras Spon",
            "Cooking Set Nesting DS-308 + Kompor Windproof",
            "Lampu Tenda LED + Gas Kaleng Siap Pakai",
          ],
        }}
      />

      {/* 7. Captioned Video Template */}
      <Composition
        id="CaptionedVideo"
        component={CaptionedVideo}
        calculateMetadata={calculateCaptionedVideoMetadata}
        schema={captionedVideoSchema}
        width={1080}
        height={1920}
        defaultProps={{
          src: staticFile("sample-video.mp4"),
        }}
      />
    </>
  );
};
