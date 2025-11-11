# Model Property Name Fixes - Complete

## ✅ Issues Resolved

**Error 1:** `Getting unknown property: app\models\Customer::name`  
**Location:** `controllers/TaxReturnController.php:778`

**Error 2:** `Unknown column 'pay_date' in 'where clause'`  
**Location:** `controllers/TaxReturnController.php:812`

---

## Root Cause

The code was trying to access properties/columns that don't exist on the models:
- **Customer** model uses `company_name`, not `name`
- **Employee** model uses `first_name` and `last_name`, not `name`
- **Paysheet** model uses `payment_date`, not `pay_date`
- **Paysheet** model uses `pay_period_start`, not `period_start`
- **Paysheet** model uses `pay_period_end`, not `period_end`
- **Paysheet** model uses `allowances`, not `total_allowances`
- **Paysheet** model uses `deductions`, not `total_deductions`

---

## Fixes Applied

### 1. Customer Model Reference ✅
**File:** `php/controllers/TaxReturnController.php` (line ~778)

**Before:**
```php
$sheet->setCellValue("C{$row}", $invoice->customer ? $invoice->customer->name : 'N/A');
```

**After:**
```php
$sheet->setCellValue("C{$row}", $invoice->customer ? $invoice->customer->company_name : 'N/A');
```

**Impact:** Invoices Excel now correctly displays customer company names

---

### 2. Employee Model Reference ✅
**File:** `php/controllers/TaxReturnController.php` (line ~841)

**Before:**
```php
$sheet->setCellValue("B{$row}", $paysheet->employee ? $paysheet->employee->name : 'N/A');
```

**After:**
```php
$employeeName = $paysheet->employee ? ($paysheet->employee->first_name . ' ' . $paysheet->employee->last_name) : 'N/A';
$sheet->setCellValue("B{$row}", $employeeName);
```

**Impact:** Paysheets Excel now correctly displays full employee names

---

### 3. Paysheet Model References ✅
**File:** `php/controllers/TaxReturnController.php` (lines ~808, ~840-846)

**Before:**
```php
->where(['between', 'pay_date', $taxYearStart, $taxYearEnd])
->orderBy(['pay_date' => SORT_ASC])

$sheet->setCellValue("A{$row}", $paysheet->pay_date);
$sheet->setCellValue("C{$row}", $paysheet->period_start);
$sheet->setCellValue("D{$row}", $paysheet->period_end);
$sheet->setCellValue("F{$row}", number_format($paysheet->total_allowances, 2));
$sheet->setCellValue("G{$row}", number_format($paysheet->total_deductions, 2));
```

**After:**
```php
->where(['between', 'payment_date', $taxYearStart, $taxYearEnd])
->orderBy(['payment_date' => SORT_ASC])

$sheet->setCellValue("A{$row}", $paysheet->payment_date);
$sheet->setCellValue("C{$row}", $paysheet->pay_period_start);
$sheet->setCellValue("D{$row}", $paysheet->pay_period_end);
$sheet->setCellValue("F{$row}", number_format($paysheet->allowances ?? 0, 2));
$sheet->setCellValue("G{$row}", number_format($paysheet->deductions ?? 0, 2));
```

**Impact:** 
- Paysheets query now works without SQL errors
- Dates and amounts display correctly
- Headers updated to "Payment Date"

---

## Model Property Reference

### Customer Model:
- ✅ `company_name` - Company/business name
- ✅ `contact_person` - Contact person name
- ❌ `name` - Does NOT exist

### Employee Model:
- ✅ `first_name` - First name
- ✅ `last_name` - Last name
- ❌ `name` - Does NOT exist (must concatenate first + last)

### Vendor Model:
- ✅ `name` - Vendor name
- No issues (already correct)

### Paysheet Model:
- ✅ `payment_date` - Date of payment (NOT `pay_date`)
- ✅ `pay_period_start` - Period start date (NOT `period_start`)
- ✅ `pay_period_end` - Period end date (NOT `period_end`)
- ✅ `allowances` - Total allowances (NOT `total_allowances`)
- ✅ `deductions` - Total deductions (NOT `total_deductions`)

---

## What's Fixed

### Invoices Excel (Invoices_YYYY.xlsx):
- ✅ Customer column now shows company names correctly
- No more "unknown property" errors

### Paysheets Excel (Paysheets_YYYY.xlsx):
- ✅ SQL query works without column errors
- ✅ Employee column now shows full names (first + last)
- ✅ Payment date displays correctly
- ✅ Pay period dates display correctly
- ✅ Allowances and deductions display correctly (with 0 fallback for null values)
- No more "unknown property" or SQL errors

---

## Testing

### Test Invoices Export:
1. Navigate to `/tax-return/view-report?year=2024`
2. Click "Download ZIP"
3. Extract and open `Invoices_2024.xlsx`
4. Verify customer company names appear correctly ✅

### Test Paysheets Export:
1. Same ZIP file as above
2. Open `Paysheets_2024.xlsx`
3. Verify employee full names appear correctly ✅

---

## Status: ✅ FIXED

All model property references are now correct. The tax return package export works without errors.

---

**Files Modified:**
- `php/controllers/TaxReturnController.php` (7 fixes across 3 methods)

**Models Involved:**
- Customer (uses `company_name`)
- Employee (uses `first_name` + `last_name`)
- Paysheet (uses `payment_date`, `pay_period_start`, `pay_period_end`, `allowances`, `deductions`)
- Vendor (uses `name` - already correct)

**Last Updated:** November 11, 2025  
**Issue:** Unknown property errors  
**Status:** Resolved ✅

