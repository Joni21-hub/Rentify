---
name: harvey
description: Autonomous outbound lead generation and acquisition skill adapted from EthanPlusAI Harvey for Rentify. Orchestrates multi-channel prospecting (Google Maps, Instagram, Web Dorking), ICP scoring (1-100), personalized Indonesian copywriting (WhatsApp, Instagram DM, Email), objection handling, and lead pipeline management for both vendor onboarding and customer acquisition.
---

# Harvey Lead Generation & Acquisition Skill (Rentify Edition)

Adapted from the open-source autonomous sales agent architecture [ethanplusai/harvey](https://github.com/ethanplusai/harvey), this skill equips the AI agent with end-to-end outbound acquisition capabilities specifically tailored to **Rentify** ([rentify21.my.id](https://rentify21.my.id)).

It solves two primary growth engines for Rentify:
1. **Vendor Acquisition**: Identifying, qualifying, and onboarding local rental shop owners (kamera, outdoor/camping, gaun/kebaya/jas, sound system, rental PS/konsol, alat proyek, kendaraan).
2. **Customer Acquisition**: Identifying high-intent local demand (event organizers, wedding planners, production houses, mahasiswa/komunitas kampus, fotografer freelance) and driving rental bookings.

---

## 1. Core Operating Philosophy

Harvey operates under strict principles of precision outbound:

> [!IMPORTANT]
> **Deterministic First, AI for Judgment Only**:
> - Never waste LLM tokens scraping or parsing regexes when deterministic scripts can do it.
> - **Signal-Driven Outreach**: Outbound is never cold spam. Outreach is triggered by real, observable business signals (e.g., manual WhatsApp catalog, double-booking complaints, active Instagram bio, hiring staff, peak event seasons).
> - **Zero AI Fluff / 100% Indonesian Natural Phrasing**: No em dashes, no marketing buzzwords (*"solusi mutakhir"*, *"revolusioner"*), no generic compliments. Speak like a busy, helpful local operator.
> - **Approval-Gated Sending**: High-volume messaging without human review damages brand reputation. Harvey proposes, drafts, scores, and lines up outreach in an approval queue.

---

## 2. Multi-Agent Pipeline Architecture

Harvey decomposes the acquisition workflow into 5 autonomous sub-roles:

```
[ Prospect Source ]
(Google Maps / IG / Directories)
       │
       ▼
 ┌───────────┐      1-100 ICP Scoring &
 │   SCOUT   │ ───► Signal Detection
 └─────┬─────┘      (Manual WA, stock idle, ratings)
       │
       ▼
 ┌───────────┐      Contextual Message Drafting
 │  WRITER   │ ───► 1 Observation + 1 Conversational Hook
 └─────┬─────┘      (WhatsApp / IG DM / Email)
       │
       ▼
 ┌───────────┐      Safety Checks & Daily Caps
 │  SENDER   │ ───► Anti-ban pacing (WA: 25/day, IG: 20/day)
 └─────┬─────┘      Send window: 09:00 - 17:00 WIB
       │
       ▼
 ┌───────────┐      Intent Classification &
 │  HANDLER  │ ───► LAARC Objection Handling
 └─────┬─────┘      (Biaya 5%, jaminan barang, verifikasi KTP)
       │
       ▼
 ┌───────────┐      Conversion Metrics &
 │  ANALYST  │ ───► Cohort Performance Analysis
 └───────────┘
```

| Agent | Responsibility | Primary Deliverable |
| :--- | :--- | :--- |
| **Scout** | Scrapes directories, extracts contact info, validates numbers, scores ICP (1-100), and flags signals. | Qualified prospect cohorts with verified WhatsApp / IG / Email. |
| **Writer** | Crafts high-converting Indonesian messages tailored to channel (WA / IG / Email). Strictly follows anti-AI rules. | Approved message drafts for Touch 1, Touch 2, and Touch 3. |
| **Sender** | Enforces rate limits, quiet hours, domain/account warmup, and human approval queue. | Safe dispatch with audit trail. |
| **Handler** | Listens for replies, classifies intent (Interested, Objection, Question, Unsubscribe), handles objections. | Rapid qualification and onboarding / booking link dispatch. |
| **Analyst** | Measures reply rates, intent distribution, and onboarding conversions per category/city. | Weekly optimization recommendations. |

---

## 3. Dual-Target Strategy: Vendors vs Customers

### 3.1 Target A: Vendor Acquisition (Sisi Pemilik Rental)
* **Ideal Vendor Profile (IVP)**:
  * Bisnis rental skala mikro hingga menengah (1-10 staf, 10-500 item sewa).
  * Kategori prioritas:
    1. **Kamera & Multimedia** (Sony Alpha, Canon, lensa, gimbal, lighting).
    2. **Outdoor & Camping Gear** (tenda, carrier, matras, kompor portabel).
    3. **Busana & Pesta** (kebaya wisuda, jas formal, baju adat, kostum cosplay).
    4. **Sound System & Alat Event** (speaker portable, mic wireless, proyektor).
    5. **Gaming & Konsol** (PlayStation 5, Nintendo Switch, VR).
  * **Pain Points Utama Vendor**:
    * Chat WhatsApp menumpuk, rawan bentrok jadwal (*double booking*).
    * Khawatir barang sewa dibawa kabur atau dirusak (*trust & security issue*).
    * Pembayaran manual, repot cek mutasi bank / QRIS manual satu per satu.
    * Stok menganggur di hari kerja (*low weekday utilization*).
  * **Nilai Tawar Rentify**:
    * Biaya platform hanya 5% saat transaksi berhasil (tanpa biaya langganan bulanan).
    * Otomatisasi booking kalender & invoice digital.
    * Verifikasi KTP & identitas penyewa terintegrasi.
    * Fitur iklan lelang (Ad Auction) untuk mendongkrak visibilitas barang sewa.

### 3.2 Target B: Customer Acquisition (Sisi Penyewa)
* **Ideal Customer Profile (ICP)**:
  * **Event Organizers & Wedding Planners**: Butuh proyektor, sound, HT, tenda dekorasi.
  * **Mahasiswa & Organisasi Kampus**: Butuh kamera wisuda, sound acara perpisahan, jas.
  * **Travelers & Pendaki Gunung**: Butuh alat camping lengkap tanpa perlu beli mahal.
  * **Content Creators & Indie Filmmakers**: Butuh kamera sinematik, lighting, mic wireless.
* **Pain Points Utama Customer**:
  * Sulit mencari vendor rental terpercaya yang dekat dengan lokasi.
  * Prosedur jaminan KTP fisik berisiko dan ribet.
  * Harga dan ketersediaan barang tidak transparan (harus tanya WA satu per satu).
* **Nilai Tawar Rentify**:
  * Marketplace terpusat: cari dan bandingkan harga sewa sekitar dalam 1 klik.
  * Pilihan pengiriman fleksibel & opsi bayar di tempat (COD) atau QRIS instan.
  * Transparansi ulasan penyewa sebelumnya.

---

## 4. Multi-Channel Execution Rules (Indonesia Context)

### 4.1 WhatsApp Outreach (Primary Channel)
* **Pacing**: Jeda acak 3–7 menit antar pesan. Maksimal **25 kontak baru/hari** per nomor untuk mencegah blokir WhatsApp.
* **Format**: Maksimal 50–70 kata. Pendek, santai, profesional, tidak pakai format bold berlebihan.
* **First Line**: Langsung sebut nama toko atau nama owner + observasi nyata (misal: "Halo kak, tadi lihat katalog rental kamera di IG @tokorental...").
* **Call to Action (CTA)**: Pertanyaan santai, bukan ajakan daftar langsung. Contoh: *"Kira-kira sering kendala jadwal bentrok pas weekend nggak kak?"*

### 4.2 Instagram Direct Message (Secondary Channel)
* **Pacing**: Maksimal **15–20 DM baru/hari** per akun.
* **Targeting**: Akun rental yang aktif upload story/reels namun memuat nomor WA di bio tanpa website otomatis.
* **Hook**: Puji portofolio spesifik atau tanyakan ketersediaan produk tertentu sebelum memperkenalkan kemudahan katalog Rentify.

### 4.3 Cold Email (B2B / Formal Channel)
* **Target**: Rental corporate, vendor sound system skala besar, Event Organizer, production house.
* **Format**: Plain text, subjek huruf kecil (2-4 kata), di bawah 75 kata, tanpa gambar atau attachment.
* **Compliance**: Sertakan opsi berhenti (*"Kalau kurang berkenan, balas saja 'tidak' dan saya tidak akan email lagi"*).

---

## 5. Directory Structure of the Harvey Skill

```text
.agents/skills/harvey/
├── SKILL.md                                 # Master operational guide (this file)
├── references/
│   ├── prospecting-and-scraping.md          # Google Maps, Overpass, IG dorking queries
│   ├── signal-and-scoring.md                # 1-100 ICP scoring formula & 23 signals
│   ├── copywriting-and-messaging.md         # WhatsApp, IG DM & Email scripts (Indonesian)
│   ├── objection-handling.md                # LAARC framework for 5% fee, trust, security
│   └── pipeline-and-workflow.md             # Rate limits, quiet hours, state transitions
├── examples/
│   ├── harvey.yaml                          # Full Rentify-tuned configuration
│   └── prompts/
│       ├── scout.md                         # Scoring & signal extraction prompt
│       ├── writer.md                        # Anti-AI copy generator prompt
│       └── handler.md                       # Intent classifier & reply handler prompt
└── scripts/
    └── lead_scorer.py                       # CLI tool to clean, score, and cohort leads
```

---

## 6. Sub-References

1. [Prospecting & Scraping Guide](./references/prospecting-and-scraping.md): Step-by-step queries to discover 100+ local rental vendors without paid tools.
2. [Signal Detection & Scoring Matrix](./references/signal-and-scoring.md): How to assign 1-100 ICP scores and prioritize high-value prospects.
3. [Copywriting & Messaging Playbook](./references/copywriting-and-messaging.md): Indonesian templates that earn 15-30% reply rates on WhatsApp and IG.
4. [Objection Handling Guide](./references/objection-handling.md): Bulletproof responses for platform fees, item safety, and onboarding inertia.
5. [Pipeline & Workflow Operations](./references/pipeline-and-workflow.md): Managing lead stages, daily send pacing, and safety gates.
6. [Rentify Config (`harvey.yaml`)](./examples/harvey.yaml): Plug-and-play configuration file for Rentify.
