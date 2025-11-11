# Credit Card Added as Liability Category

## ✅ Changes Made

Added "Credit Card" as a new liability category option alongside Loan and Leasing.

---

## Files Modified

### Liability Model
**File:** `php/models/Liability.php`

#### 1. Added Constant ✅
```php
const CATEGORY_CREDIT_CARD = 'credit_card';
```

#### 2. Updated Validation Rules ✅
```php
['liability_category', 'in', 'range' => [
    self::CATEGORY_LOAN, 
    self::CATEGORY_LEASING, 
    self::CATEGORY_CREDIT_CARD  // Added
]],
```

#### 3. Updated Category Options Method ✅
```php
public static function getLiabilityCategoryOptions()
{
    return [
        self::CATEGORY_LOAN => 'Loan',
        self::CATEGORY_LEASING => 'Leasing',
        self::CATEGORY_CREDIT_CARD => 'Credit Card',  // Added
    ];
}
```

---

## Available Liability Categories

Now when creating or editing a liability, users can choose from:
1. **Loan** - Traditional loans
2. **Leasing** - Vehicle or equipment leasing
3. **Credit Card** - Credit card debt/balance

---

## Where This Appears

### Forms:
- `/liability/create` - Create new liability form
- `/liability/update?id=X` - Update existing liability form

### Display:
- `/liability/index` - List view shows category
- `/liability/view?id=X` - Detail view shows category
- Tax return reports - Credit card liabilities included

---

## Usage Example

### Creating a Credit Card Liability:

1. Navigate to `/liability/create`
2. Fill in the form:
   - **Lender Name**: "Commercial Bank Visa"
   - **Type**: Personal or Business
   - **Category**: **Credit Card** ← New option!
   - **Original Amount**: Credit limit amount
   - **Start Date**: Card issue date
   - **Interest Rate**: Annual interest rate (e.g., 18%)
   - **Status**: Active
3. Click Save

### Tax Return Reporting:

Credit card liabilities will be automatically included in:
- Personal Liabilities section (if marked as personal)
- Business Liabilities section (if marked as business)
- Outstanding balance tracking at year-end

---

## Benefits

✅ **Complete Liability Tracking** - Now covers all common liability types  
✅ **Personal & Business** - Can track both personal and business credit cards  
✅ **Tax Compliance** - Credit card balances properly reported in tax returns  
✅ **Better Financial Picture** - More accurate representation of total liabilities  

---

## Database

No migration needed! The liability_category column already accepts string values, so "credit_card" will work immediately.

---

## Status: ✅ COMPLETE

Credit Card has been successfully added as a liability category and is immediately available in all forms and reports.

**Last Updated:** November 10, 2025  
**Changes:** Added CATEGORY_CREDIT_CARD constant and updated dropdown options

