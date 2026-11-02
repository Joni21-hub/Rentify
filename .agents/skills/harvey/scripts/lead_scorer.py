#!/usr/bin/env python3
"""
Lead Scorer & Pipeline Processor (Rentify Edition)
Adapted from EthanPlusAI Harvey for Indonesian Rental Ecosystem.

Usage:
    python lead_scorer.py --input leads.json --output scored_leads.json --min-score 60
"""

import argparse
import json
import re
import sys
from typing import Dict, List, Any, Optional

TIER_1_CATEGORIES = {
    "kamera": 35,
    "camera": 35,
    "outdoor": 35,
    "camping": 35,
    "kebaya": 30,
    "gaun": 30,
    "jas": 30,
    "sound": 30,
    "sound system": 30,
    "ps5": 25,
    "playstation": 25,
    "genset": 25,
    "proyektor": 25
}

MAJOR_CITIES = {
    "jakarta", "surabaya", "bandung", "malang", "yogyakarta",
    "jogja", "semarang", "tangerang", "bekasi", "depok", "bali", "medan"
}

def clean_phone_number(raw_phone: str) -> Optional[str]:
    """Normalize Indonesian phone numbers to 628... format."""
    if not raw_phone:
        return None
    digits = re.sub(r"\D", "", raw_phone)
    if digits.startswith("08"):
        digits = "62" + digits[1:]
    elif digits.startswith("8"):
        digits = "62" + digits
    elif digits.startswith("6208"):
        digits = "628" + digits[4:]
    
    # Indonesian mobile numbers typically have 10-14 digits starting with 628
    if digits.startswith("628") and 11 <= len(digits) <= 15:
        return digits
    return None

def calculate_icp_score(prospect: Dict[str, Any]) -> int:
    """Calculate 1-100 ICP fit score for Rentify vendor/customer."""
    score = 0
    category = str(prospect.get("category", "")).lower()
    city = str(prospect.get("city", "")).lower()
    signals = prospect.get("signals", [])
    raw_phone = prospect.get("phone", "")
    instagram = prospect.get("instagram", "")
    email = prospect.get("email", "")

    # 1. Category Fit (Max 35 pts)
    cat_points = 15
    for key, pts in TIER_1_CATEGORIES.items():
        if key in category:
            cat_points = max(cat_points, pts)
    score += cat_points

    # 2. Location Fit (Max 15 pts)
    if any(c in city for c in MAJOR_CITIES):
        score += 15
    elif city:
        score += 8

    # 3. Contact Availability & Quality (Max 25 pts)
    clean_wa = clean_phone_number(raw_phone)
    if clean_wa:
        score += 20
    if instagram:
        score += 5
    if email and not clean_wa:
        score += 10

    # 4. Trigger Signals (Max 25 pts)
    signal_weights = {
        "MANUAL_BOOKING_SHEET": 15,
        "SLOW_RESPONSE_REVIEWS": 15,
        "DOUBLE_BOOKING_RISK": 12,
        "WEEKDAY_IDLE_STOCK": 10,
        "SEASONAL_SURGE": 10,
        "NO_BOOKING_WEBSITE": 8,
        "EXPANDING_CATALOG": 7
    }
    applied_signal_points = 0
    for s in signals:
        applied_signal_points += signal_weights.get(s, 5)
    score += min(applied_signal_points, 25)

    # Disqualification guardrails
    if prospect.get("is_closed", False):
        return 0
    if not clean_wa and not instagram and not email:
        return 0
    if prospect.get("is_competitor_platform", False):
        return 5

    return min(score, 100)

def process_leads(leads: List[Dict[str, Any]], min_score: int = 40) -> List[Dict[str, Any]]:
    scored_leads = []
    for lead in leads:
        clean_phone = clean_phone_number(lead.get("phone", ""))
        score = calculate_icp_score(lead)
        
        status = "DISQUALIFIED"
        if score >= 80:
            status = "HOT"
        elif score >= 60:
            status = "WARM"
        elif score >= 40:
            status = "COLD"
            
        if score >= min_score:
            lead_result = dict(lead)
            lead_result["normalized_phone"] = clean_phone
            lead_result["icp_score"] = score
            lead_result["lead_tier"] = status
            lead_result["ready_for_outbox"] = bool(clean_phone or lead.get("instagram"))
            scored_leads.append(lead_result)

    # Sort descending by score
    scored_leads.sort(key=lambda x: x["icp_score"], reverse=True)
    return scored_leads

def main():
    parser = argparse.ArgumentParser(description="Rentify Lead Scorer (Harvey Engine)")
    parser.add_argument("--input", "-i", help="Path to input JSON file", required=False)
    parser.add_argument("--output", "-o", help="Path to output JSON file", required=False)
    parser.add_argument("--min-score", "-m", type=int, default=40, help="Minimum ICP score threshold")
    parser.add_argument("--demo", action="store_true", help="Run with demo Indonesian rental leads")
    args = parser.parse_args()

    if args.demo or not args.input:
        print("[*] Running Harvey Lead Scorer Demo with sample Indonesian rental prospects...")
        demo_leads = [
            {
                "business_name": "Lensa Malang Rental Kamera",
                "category": "Rental Kamera & Lensa",
                "city": "Malang",
                "phone": "081234567890",
                "instagram": "@lensamalang",
                "signals": ["MANUAL_BOOKING_SHEET", "SLOW_RESPONSE_REVIEWS"],
                "notes": "Bio ada format WA teks manual, ulasan Google Maps bintang 4.2 keluhan chat slow respon."
            },
            {
                "business_name": "Bromo Camp Outdoor Gear",
                "category": "Sewa Alat Camping & Outdoor",
                "city": "Surabaya",
                "phone": "085711223344",
                "instagram": "@bromocamp_sby",
                "signals": ["WEEKDAY_IDLE_STOCK", "NO_BOOKING_WEBSITE"],
                "notes": "Ada promo paket sewa tenda weekday, belum ada link web booking."
            },
            {
                "business_name": "Kencana Kebaya Wisuda",
                "category": "Sewa Kebaya & Busana",
                "city": "Yogyakarta",
                "phone": "087899001122",
                "instagram": "@kencanakebaya_jogja",
                "signals": ["SEASONAL_SURGE", "DOUBLE_BOOKING_RISK"],
                "notes": "Musim wisuda kampus UGM/UNY ramai, katalog 40+ kebaya."
            },
            {
                "business_name": "Toko Jual Beli HP Bekas",
                "category": "Elektronik Jual Beli",
                "city": "Jakarta",
                "phone": "089988776655",
                "signals": [],
                "notes": "Hanya toko jual-beli, bukan rental."
            }
        ]
        results = process_leads(demo_leads, min_score=args.min_score)
        print(json.dumps(results, indent=2, ensure_ascii=False))
        return

    with open(args.input, "r", encoding="utf-8") as f:
        data = json.load(f)

    results = process_leads(data, min_score=args.min_score)
    
    if args.output:
        with open(args.output, "w", encoding="utf-8") as f:
            json.dump(results, f, indent=2, ensure_ascii=False)
        print(f"[✓] Scored {len(results)} leads. Saved to {args.output}")
    else:
        print(json.dumps(results, indent=2, ensure_ascii=False))

if __name__ == "__main__":
    main()
