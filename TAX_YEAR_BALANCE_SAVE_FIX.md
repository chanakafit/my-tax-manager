# Tax Year Balance Save Error - Fixed

## ✅ Problem Solved

**Error:** `Failed to save balances: Setting unknown property: app\models\TaxYearBankBalance::created_by`

**Root Cause:** The `TaxYearBankBalance` and `TaxYearLiabilityBalance` models extend `BaseModel`, which includes `BlameableBehavior`. This behavior tries to automatically set `created_by` and `updated_by` fields, but these tables don't have those columns.

---

## Database Schema

The tables only have timestamp columns, not blameable columns:

### tax_year_bank_balance
```
- id
- tax_year_snapshot_id
- bank_account_id
- balance
- balance_lkr
- created_at  ✅
- updated_at  ✅
(No created_by/updated_by)
```

### tax_year_liability_balance
```
- id
- tax_year_snapshot_id
- liability_id
- outstanding_balance
- created_at  ✅
- updated_at  ✅
(No created_by/updated_by)
```

---

## Solution Applied

Overrode the `behaviors()` method in both models to exclude `BlameableBehavior` while keeping `TimestampBehavior`.

### Files Fixed:

#### 1. TaxYearBankBalance Model ✅
**File:** `php/models/TaxYearBankBalance.php`

```php
public function behaviors(): array
{
    return [
        \yii\behaviors\TimestampBehavior::class,
        // BlameableBehavior excluded - table doesn't have created_by/updated_by columns
    ];
}
```

#### 2. TaxYearLiabilityBalance Model ✅
**File:** `php/models/TaxYearLiabilityBalance.php`

```php
public function behaviors(): array
{
    return [
        \yii\behaviors\TimestampBehavior::class,
        // BlameableBehavior excluded - table doesn't have created_by/updated_by columns
    ];
}
```

---

## What This Fixes

✅ **Saving Year-End Bank Balances** - Can now save without error  
✅ **Saving Year-End Liability Balances** - Can now save without error  
✅ **TimestampBehavior Still Works** - created_at and updated_at are still auto-filled  
✅ **No Database Changes Needed** - Fixed at model level  

---

## Why This Approach

### Alternative 1: Add Columns to Database ❌
```sql
ALTER TABLE tax_year_bank_balance 
ADD COLUMN created_by INT,
ADD COLUMN updated_by INT;
```
**Why Not:** These are junction/snapshot tables that don't need user tracking. Adding unnecessary columns would bloat the database.

### Alternative 2: Override Behaviors ✅ (Chosen)
```php
public function behaviors(): array
{
    return [
        \yii\behaviors\TimestampBehavior::class,
    ];
}
```
**Why Yes:** Clean, doesn't require migration, appropriate for these types of tables.

---

## Testing

Now you can:
1. Navigate to `/tax-return/manage-balances?year=2023`
2. Enter bank account balances
3. Enter liability outstanding balances
4. Click "Save Balances"
5. ✅ Should save successfully without errors

---

## Other Models for Reference

### TaxYearSnapshot - Has User Tracking ✅
This model DOES have `created_by` and `updated_by` columns, so it keeps the default `BaseModel` behaviors unchanged.

### Why Different?
- `TaxYearSnapshot` is the main snapshot record - needs to track WHO created it
- `TaxYearBankBalance` and `TaxYearLiabilityBalance` are detail records - only need timestamps

---

## Status: ✅ FIXED

The year-end balance saving functionality is now working correctly. Both bank balances and liability balances can be saved without encountering the "unknown property" error.

**Last Updated:** November 10, 2025  
**Issue:** BlameableBehavior trying to set non-existent columns  
**Solution:** Overrode behaviors() to exclude BlameableBehavior

