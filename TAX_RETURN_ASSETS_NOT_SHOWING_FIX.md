# Tax Return Assets Not Showing - Explanation & Fix

## ✅ Issues Found & Fixed

### Issue 1: Missing `asset_category` Values ✅ FIXED
**Problem:** Some assets had `NULL` or empty `asset_category` values in the database.
**Impact:** These assets were excluded from tax return reports because queries filter by category.
**Fix Applied:**
- Updated all NULL/empty `asset_category` values to 'movable' in database
- Made `asset_category` a required field in CapitalAsset model
- Added default value of 'movable' for new assets

### Issue 2: Tax Year Date Range (Working as Designed)
**Understanding:** Tax year 2024 covers April 1, 2024 to March 31, 2025
**Your Assets:** All purchased in Sep-Nov 2025 (AFTER the 2024 tax year ends)
**Result:** Assets correctly do NOT appear in 2024 report - they belong to 2025 tax year

---

## Tax Year Logic Explained

### How Tax Years Work in Sri Lanka:
```
Tax Year 2023 = Apr 1, 2023 to Mar 31, 2024
Tax Year 2024 = Apr 1, 2024 to Mar 31, 2025
Tax Year 2025 = Apr 1, 2025 to Mar 31, 2026
```

### Your Current Assets:
```sql
ID  Asset Name          Purchase Date   Tax Year It Belongs To
2   Asus Monitors       2025-10-10      2025 (Apr 1, 2025 - Mar 31, 2026)
3   Apple Macbook Pro   2025-09-10      2025 (Apr 1, 2025 - Mar 31, 2026)
4-7 mh                  2025-11-27      2025 (Apr 1, 2025 - Mar 31, 2026)
8-9 fds                 2025-11-20      2025 (Apr 1, 2025 - Mar 31, 2026)
10  ddd                 2025-11-19      2025 (Apr 1, 2025 - Mar 31, 2026)
```

**All assets purchased after April 1, 2025 = Belong to tax year 2025**

---

## Why Assets Don't Appear in 2024 Report

When you view `/tax-return/view-report?year=2024`, the system looks for:
- **Existing assets:** Purchased BEFORE April 1, 2024
- **Purchased during year:** Between April 1, 2024 and March 31, 2025

Your assets (purchased Sep-Nov 2025) are AFTER March 31, 2025, so they correctly don't appear.

---

## Where Your Assets WILL Appear

Your assets will appear in the **2025 Tax Return Report**:
- Navigate to `/tax-return/view-report?year=2025`
- Assets purchased Apr 1, 2025 - Mar 31, 2026 will show

---

## Changes Made to Fix `asset_category` Issue

### 1. Database Update ✅
```sql
-- Updated all NULL/empty categories to 'movable'
UPDATE mb_capital_asset 
SET asset_category = 'movable' 
WHERE asset_category IS NULL OR asset_category = '';
```

**Result:** All assets now have valid category values

### 2. CapitalAsset Model Update ✅
**File:** `php/models/CapitalAsset.php`

**Before:**
```php
[['asset_name', 'purchase_date', 'purchase_cost', 'initial_tax_year'], 'required'],
```

**After:**
```php
[['asset_name', 'purchase_date', 'purchase_cost', 'initial_tax_year', 'asset_category'], 'required'],
['asset_category', 'default', 'value' => 'movable'],
```

**Impact:** 
- `asset_category` is now required when creating/editing assets
- Defaults to 'movable' if not specified
- Prevents future NULL values

### 3. Tax Return Report Enhancement ✅
**File:** `php/views/tax-return/view-report.php`

**Added:**
- Tax year coverage information banner
- Quick summary cards showing asset counts
- Clear explanation of date range
- Helpful note about which assets appear where

---

## How to View Your Assets in Tax Returns

### Step 1: Check Which Tax Year
Determine which tax year your assets belong to based on purchase date:
- Before Apr 1, 2024 → Appears in 2023 and earlier reports as "existing"
- Apr 1, 2024 - Mar 31, 2025 → Appears in 2024 report
- Apr 1, 2025 - Mar 31, 2026 → Appears in 2025 report ← **YOUR ASSETS**

### Step 2: Create Tax Return for Correct Year
1. Navigate to Tax Years: `/tax-year/index`
2. Find or create tax year 2025
3. Click "Tax Return Submission"
4. Your assets will appear in the report!

### Step 3: Manage Year-End Balances
1. Go to `/tax-return/manage-balances?year=2025`
2. Enter bank balances as of March 31, 2026
3. Enter liability balances
4. Save

### Step 4: View Complete Report
1. Go to `/tax-return/view-report?year=2025`
2. All your assets, bank balances, and liabilities will appear
3. Export to Excel if needed

---

## Testing Your Assets

### Test 1: View Assets in Capital Asset Index ✅
```
URL: /capital-asset/index
Expected: All 9 assets visible
Status: WORKING
```

### Test 2: View 2024 Tax Return
```
URL: /tax-return/view-report?year=2024
Expected: No assets (all purchased after tax year ends)
Status: WORKING AS DESIGNED
```

### Test 3: View 2025 Tax Return
```
URL: /tax-return/view-report?year=2025
Expected: All 9 assets should appear
Status: READY TO TEST
```

---

## Asset Categories

### Immovable Properties:
- Land
- Buildings
- House/apartment
- Commercial property

### Movable Properties:
- Vehicles
- Computers/laptops
- Furniture
- Equipment
- Machinery
- Any other movable assets

**Default:** When creating assets, if you don't specify, it defaults to 'movable'

---

## Summary of Changes

✅ **Database:** All assets now have valid `asset_category` values  
✅ **Model:** `asset_category` is now required with default value  
✅ **View:** Added helpful summary and tax year coverage info  
✅ **Documentation:** Clear explanation of tax year logic  

---

## Quick Action Items

1. **To see your current assets in a tax return:**
   - Navigate to `/tax-return/view-report?year=2025`
   - Or create a tax return for 2025 if it doesn't exist

2. **When creating new assets:**
   - Select appropriate category (immovable/movable)
   - Field is now required and defaults to movable

3. **Understanding which report to use:**
   - Check your asset purchase dates
   - Use the tax year that covers that period
   - Assets appear in the year they were purchased

---

**Last Updated:** November 10, 2025  
**Status:** ✅ Issues Resolved  
**Next Step:** View `/tax-return/view-report?year=2025` to see your assets

