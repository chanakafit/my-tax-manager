# Expense Health Check System - Complete Guide

## 📋 Table of Contents
1. [Overview](#overview)
2. [Quick Start](#quick-start)
3. [How It Works](#how-it-works)
4. [Installation](#installation)
5. [Usage](#usage)
6. [Console Commands](#console-commands)
7. [Features & Logic](#features--logic)
8. [Troubleshooting](#troubleshooting)
9. [Files & Structure](#files--structure)

---

## Overview

**Expense Health Check** automatically detects missing recurring expenses and alerts you on the dashboard.

### What It Does
- 🔍 Analyzes your expense history (last 6 months)
- 🎯 Detects patterns (same category + vendor appearing 2-3+ consecutive months)
- ⚠️ Alerts you when a recurring expense is missing
- ✅ Suggests missing expenses for **current and past months only** (no future months)
- 🔄 Auto-resets permanent ignores when you add expenses again

### Example
```
Your History:
├── Sep 2025: Office Rent → ABC Properties (LKR 50,000)
├── Oct 2025: Office Rent → ABC Properties (LKR 50,000)
└── Nov 2025: MISSING ❌

Dashboard Alert:
"⚠️ 1 missing expense - Office Rent - ABC Properties (Nov 2025)"

Your Actions:
[Add Expense] [Ignore (Temp)] [Ignore (Permanent)]
```

---

## Quick Start

### 1. Install (Run Migration)
```bash
# Using Docker
docker exec <container-id> php /var/www/html/yii migrate --interactive=0

# Without Docker
cd /Users/chana/Bee48/my-business
php php/yii migrate
```

### 2. Generate Suggestions
```bash
# Current month only
docker exec <container-id> php /var/www/html/yii expense-health-check/generate

# All past 6 months
docker exec <container-id> php /var/www/html/yii expense-health-check/generate-all 6
```

### 3. View Dashboard
Open: `http://your-domain/site/dashboard`

Widget appears at top showing pending suggestions.

### 4. Setup Cron (Optional - for automation)
```bash
crontab -e
# Add this line (runs 1st of each month at 1 AM):
0 1 1 * * docker exec <container-id> php /var/www/html/yii expense-health-check/generate
```

---

## How It Works

### Pattern Detection Logic

**Requirements for a Pattern:**
- ✅ Same `expense_category_id` + `vendor_id`
- ✅ Appears in **2-3 consecutive months** minimum
- ✅ Analyzes **last 6 months** of data
- ✅ Allows **1 month gap** (e.g., Jan, Mar, Apr = valid pattern)
- ✅ Tracks **average amount** from history
- ❌ Excludes **payroll** (vendor_id = NULL)

**Important:** Only suggests for **current month and past months** (never future).

### Suggestion Lifecycle

```
1. GENERATED → System detects missing expense
2. PENDING → Awaits your action
3. ACTIONS:
   ├─ ADD → Create expense (status = "added")
   ├─ IGNORE (TEMP) → Hidden for 2 months, then reappears
   └─ IGNORE (PERMANENT) → Hidden forever
4. AUTO-RESET → If permanent ignore + you add same expense later = reactivated
```

### Ignore Behavior

| Type | Duration | When to Use | Auto-Reset |
|------|----------|-------------|------------|
| **Temporary** | 2 months | One-time gap | After 2 months |
| **Permanent** | Forever | Discontinued expense | When you add same category+vendor expense |

---

## Installation

### Database Setup

**Option A: Yii Migration**
```bash
docker exec <container-id> php /var/www/html/yii migrate --interactive=0
```

**Option B: Manual SQL**
```bash
mysql -u user -p database < sql/create_expense_health_check_tables.sql
```

### Database Schema Created
```sql
mb_expense_suggestion
├── id (PK)
├── expense_category_id (FK → mb_expense_category)
├── vendor_id (FK → mb_vendor)
├── suggested_month (DATE, YYYY-MM-01)
├── pattern_months (JSON array)
├── avg_amount_lkr (DECIMAL)
├── last_expense_id (FK → mb_expense)
├── status (VARCHAR: pending/added/ignored_temporary/ignored_permanent)
├── ignored_reason (TEXT)
├── generated_at (INT timestamp)
├── actioned_at (INT timestamp)
├── actioned_by (INT user_id)
├── created_at, updated_at, created_by, updated_by
```

### Verify Installation
```bash
docker exec <container-id> php /var/www/html/yii expense-health-check/count
# Output: "Pending expense suggestions: 0" (or number)
```

---

## Usage

### Web Interface

#### Navigation Flow
```
Dashboard Widget
├─ [View All] → Active Suggestions (/expense-suggestion/index)
│   └─ [View Ignored] → Ignored Suggestions (/expense-suggestion/ignored)
│       └─ [Active Suggestions] → Back to active list
└─ [Ignored] → Ignored Suggestions (direct link)
```

**Dashboard Widget** (`/site/dashboard`)
- Shows pending count with badge
- Displays top 5 suggestions
- Quick actions: Add, Ignore, Details
- Buttons: "View All" (active list) and "Ignored" (ignored list)

**Active Suggestions Page** (`/expense-suggestion/index`)
- **Shows ONLY**: Pending and added suggestions
- **Does NOT show**: Ignored suggestions (they're in separate list)
- Full grid with filters (Pending/Added only)
- Status, category, vendor, amount, pattern months
- Action buttons: Add, Ignore, View
- Header button: "View Ignored" to switch to ignored list
- **Purpose**: Focus on actionable items without distraction

**Ignored Suggestions Page** (`/expense-suggestion/ignored`)
- **Shows ONLY**: Ignored suggestions (temporary and permanent)
- **Does NOT show**: Pending or added suggestions
- Separate list to avoid confusion with actionable items
- Columns: Status, Month, Category, Vendor, Amount, Pattern, Reason, Ignored At
- Action buttons: View, Delete
- Filter: Temporary vs Permanent
- Header button: "Active Suggestions" to switch back
- **Purpose**: Review and manage ignored items

**View Suggestion** (`/expense-suggestion/view?id=X`)
- Full details of one suggestion
- Pattern history (months where expense appeared)
- Last expense reference with details
- Ignored reason (if ignored)

**Create Expense** (`/expense-suggestion/create-expense?id=X`)
- Pre-filled form (category, vendor, date, amount)
- Supports file upload for receipts
- Editable before saving
- Creates financial transaction automatically
- Marks suggestion as "added" when saved
- Auto-resets permanent ignores for same category+vendor

### User Actions

**1. Add Expense**
```
Click [Add] → Review pre-filled form → Modify if needed → Save
Result: Expense created, suggestion status = "added"
```

**2. Ignore (Modal appears)**
```
Click [Ignore] → Choose type:
  ├─ Temporary: Hidden for 2 months
  └─ Permanent: Hidden forever (unless you add expense later)
Add reason (optional) → Click [Ignore Suggestion]
```

**3. View Details**
```
Click [Details] → See:
  ├─ Pattern months (where expense appeared)
  ├─ Average amount calculation
  ├─ Last recorded expense details
  └─ Ignore reason (if ignored)
```

---

## Console Commands

### Available Commands

| Command | Description | Example |
|---------|-------------|---------|
| `generate` | Current month only | `php yii expense-health-check/generate` |
| `generate-all` | All past N months | `php yii expense-health-check/generate-all 6` |
| `generate-for-month` | Specific month | `php yii expense-health-check/generate-for-month 2025-10` |
| `count` | Show pending count | `php yii expense-health-check/count` |
| `cleanup` | Reset old temp ignores | `php yii expense-health-check/cleanup` |

### Command Examples (Docker)

```bash
CONTAINER=<your-container-id>

# Generate for current month (November 2025)
docker exec $CONTAINER php /var/www/html/yii expense-health-check/generate
# Output: Created 7 suggestions

# Scan all past 6 months + current
docker exec $CONTAINER php /var/www/html/yii expense-health-check/generate-all 6
# Output: Months scanned: 2025-11, 2025-10, ... 2025-05
#         Created: 17, Skipped: 26

# Try future month (will be blocked)
docker exec $CONTAINER php /var/www/html/yii expense-health-check/generate-for-month 2025-12
# Output: Warning - Cannot generate for future month

# Check count
docker exec $CONTAINER php /var/www/html/yii expense-health-check/count
# Output: Pending expense suggestions: 24
```

### Cron Automation

**Recommended Setup:**
```bash
# Run on 1st of each month at 1 AM
0 1 1 * * docker exec <container-id> php /var/www/html/yii expense-health-check/generate >> /var/log/expense-health-check.log 2>&1

# Optional: Weekly cleanup of old temp ignores (Sunday 2 AM)
0 2 * * 0 docker exec <container-id> php /var/www/html/yii expense-health-check/cleanup >> /var/log/expense-health-check-cleanup.log 2>&1
```

---

## Features & Logic

### UI Organization

**Separated Lists for Clarity:**

The system uses **two separate lists** to avoid confusion:

1. **Active Suggestions** (`/expense-suggestion/index`)
   - Shows: Pending (actionable) and Added (completed)
   - Purpose: Focus on items that need attention
   - Actions: Add Expense, Ignore, View Details
   - Filter: Pending / Added status only

2. **Ignored Suggestions** (`/expense-suggestion/ignored`)
   - Shows: Temporary Ignored and Permanent Ignored
   - Purpose: Review and manage ignored items
   - Actions: View Details, Delete
   - Filter: Temporary / Permanent status
   - Shows: Ignored reason and date

**Why Separated?**
- ✅ Reduces confusion (actionable vs. ignored)
- ✅ Better focus on pending tasks
- ✅ Easy to review what was ignored and why
- ✅ Clear distinction between temporary and permanent ignores

**Navigation:**
- Dashboard widget has buttons for both lists
- Each list has a button to switch to the other
- Breadcrumbs show current location

### Key Features

1. **Pattern Detection**
   - Analyzes 6 months of historical data
   - Groups by category + vendor
   - Requires 2-3 consecutive months
   - Allows 1 month gap tolerance

2. **Smart Suggestions**
   - Only for current and past months (no future)
   - Calculates average amount from pattern
   - References last recorded expense
   - Checks if expense already exists before suggesting

3. **Flexible Ignore System**
   - Temporary (2 months) or Permanent
   - Optional reason tracking
   - Auto-reset when pattern resumes

4. **Auto-Reset Mechanism**
   - When you add expense for category+vendor
   - System detects and resets permanent ignores
   - Pattern becomes active again automatically

5. **Dashboard Integration**
   - Widget shows pending count
   - Top 5 suggestions displayed
   - AJAX-powered ignore functionality
   - Real-time updates
   - Quick links to both active and ignored lists

6. **Complete Expense Creation**
   - Pre-fills form with suggestion data
   - Supports receipt file uploads
   - Creates financial transaction automatically
   - Uses database transaction for data integrity
   - Auto-resets permanent ignores when expense added
   - Matches ExpenseController behavior exactly

### Date Logic Details

**Month Range Check:**
```php
// Checks if expense exists for ENTIRE month (including last day)
WHERE expense_date >= '2025-11-01' 
  AND expense_date <= '2025-11-30'  // Uses Y-m-t format (last day of month)
```

**Future Month Prevention:**
```php
if ($targetMonth > $currentMonth) {
    // Skip - don't generate for future
    return;
}
```

### Pattern Examples

**Valid Pattern:**
```
May:  Office Rent → ABC (LKR 50,000)
Jun:  Office Rent → ABC (LKR 50,000)
Jul:  Office Rent → ABC (LKR 52,000)
Aug:  MISSING ← Suggestion created
```

**Invalid (Not a Pattern):**
```
May:  Office Rent → ABC (LKR 50,000)
Jun:  MISSING
Jul:  Office Rent → ABC (LKR 52,000)
Aug:  MISSING
Result: No suggestion (not consecutive enough)
```

---

## Troubleshooting

### Common Issues

**1. No Suggestions Generated**
- **Cause**: Need 2-3 months of recurring expenses
- **Solution**: Add expenses with same category+vendor for 2-3 months, then run generate

**2. Widget Not Showing on Dashboard**
- **Cause**: Cache or autoload issue
- **Solution**: 
  ```bash
  docker exec $CONTAINER composer dump-autoload -d /var/www/html
  php yii cache/flush-all
  ```

**3. "Namespace Missing" Error**
- **Cause**: Empty PHP files or autoload not rebuilt
- **Solution**: Check file exists and has content, run `composer dump-autoload`

**4. Cron Not Running**
- **Cause**: Wrong path, permissions, or syntax
- **Solution**: 
  ```bash
  # Check crontab
  crontab -l
  
  # Test command manually
  docker exec $CONTAINER php /var/www/html/yii expense-health-check/generate
  
  # Check logs
  tail -f /var/log/cron
  ```

**5. Suggestions for Future Months**
- **Cause**: Old logic before fix
- **Solution**: Clear old suggestions and regenerate:
  ```bash
  # Delete all suggestions
  # Then regenerate
  docker exec $CONTAINER php /var/www/html/yii expense-health-check/generate-all 6
  ```

**6. Permanent Ignore Not Resetting**
- **Cause**: May need to verify expense was actually added
- **Solution**: Check database:
  ```sql
  SELECT * FROM mb_expense_suggestion 
  WHERE status = 'ignored_permanent' 
  AND expense_category_id = X AND vendor_id = Y;
  ```

**7. "Method Not Allowed (#405)" Error When Adding Expense**
- **Cause**: `create-expense` action was restricted to POST only
- **Solution**: Already fixed - action now accepts both GET (show form) and POST (submit)
- **Verification**: Click "Add" button should now work without errors

**8. Receipt File Upload Not Working**
- **Cause**: Missing `enctype='multipart/form-data'` in form
- **Solution**: Already fixed - form now has proper enctype for file uploads
- **Verification**: Try uploading a receipt file when creating expense from suggestion

**9. "Setting unknown property: FinancialTransaction::reference_id"**
- **Cause**: Used incorrect property name (should be `related_expense_id`)
- **Solution**: Already fixed - now uses correct property names
- **Properties used**: `related_expense_id`, `reference_number`, `payment_method`, `status`
- **Verification**: Create expense from suggestion should work without errors

**10. Confused by Ignored Items Mixed with Active Suggestions**
- **Issue**: All suggestions (pending, added, ignored) were in one list
- **Solution**: Now separated into two lists:
  - Active list (`/expense-suggestion/index`) - Only pending and added
  - Ignored list (`/expense-suggestion/ignored`) - Only ignored items
- **Navigation**: Use buttons to switch between lists

### Debug Commands

```bash
# Check file syntax
docker exec $CONTAINER php -l /var/www/html/widgets/ExpenseHealthCheckWidget.php

# View logs
docker exec $CONTAINER tail -f /var/www/html/runtime/logs/app.log

# Count by status
# Run SQL: SELECT status, COUNT(*) FROM mb_expense_suggestion GROUP BY status;
```

---

## Files & Structure

### Core Files

```
php/
├── models/
│   └── ExpenseSuggestion.php              # ActiveRecord model
├── components/
│   └── ExpenseHealthCheckService.php      # Pattern detection logic
├── controllers/
│   ├── ExpenseSuggestionController.php    # Web CRUD
│   └── ExpenseController.php              # Updated with auto-reset
├── commands/
│   └── ExpenseHealthCheckController.php   # Console commands
├── widgets/
│   ├── ExpenseHealthCheckWidget.php       # Dashboard widget
│   └── views/
│       └── expense-health-check.php       # Widget view
├── views/
│   ├── site/
│   │   └── dashboard.php                  # Updated with widget
│   └── expense-suggestion/
│       ├── index.php                      # Active suggestions grid (pending/added only)
│       ├── ignored.php                    # Ignored suggestions grid (separate list)
│       ├── view.php                       # Details view
│       └── create-expense.php             # Create form with file upload
├── migrations/
│   ├── m250901_000001_create_expense_health_check_tables.php
│   └── m251108_135520_add_created_by_updated_by_to_expense_suggestion.php
└── tests/manual/
    └── test-expense-health-check.php      # Test script
```

### Configuration Files

```
php/crontab-expense-health-check           # Cron template
sql/create_expense_health_check_tables.sql # Manual SQL
```

### Documentation Files (Consolidated into this file)
- ~~EXPENSE_HEALTH_CHECK_INDEX.md~~ (replaced)
- ~~EXPENSE_HEALTH_CHECK_README.md~~ (replaced)
- ~~EXPENSE_HEALTH_CHECK_QUICK_REF.md~~ (replaced)
- ~~EXPENSE_HEALTH_CHECK_IMPLEMENTATION.md~~ (replaced)
- ~~EXPENSE_HEALTH_CHECK_DIAGRAMS.md~~ (replaced)

---

## Quick Reference

### URLs
| Page | URL |
|------|-----|
| Dashboard | `/site/dashboard` |
| Active Suggestions (Pending/Added) | `/expense-suggestion/index` |
| Ignored Suggestions | `/expense-suggestion/ignored` |
| View One | `/expense-suggestion/view?id=X` |
| Create Expense | `/expense-suggestion/create-expense?id=X` |

### Status Types
| Status | Meaning |
|--------|---------|
| `pending` | Awaiting action |
| `added` | Expense created |
| `ignored_temporary` | Hidden for 2 months |
| `ignored_permanent` | Hidden forever (unless reset) |

### Pattern Detection Settings
```php
MIN_PATTERN_MONTHS = 2     // Minimum consecutive months
LOOKBACK_MONTHS = 6        // How far back to analyze
```

### Best Practices
1. ✅ Review suggestions monthly
2. ✅ Use temporary ignore for gaps
3. ✅ Use permanent ignore for discontinued expenses
4. ✅ Add reasons when ignoring
5. ✅ Monitor cron logs
6. ✅ Run cleanup weekly

---

## Support & Logs

**Application Logs:**
```bash
docker exec $CONTAINER tail -f /var/www/html/runtime/logs/app.log
```

**Cron Logs:**
```bash
tail -f /var/log/expense-health-check.log
```

**Test System:**
```bash
php php/tests/manual/test-expense-health-check.php
```

---

## Changelog

**November 8, 2025 - Initial Implementation:**
- ✅ Initial implementation complete
- ✅ Fixed future month logic (only current/past months)
- ✅ Fixed date range bug (now includes last day of month)
- ✅ Added `generate-all` command for batch processing
- ✅ All files verified and tested with Docker

**November 8, 2025 - UI & Functionality Improvements:**
- ✅ **Separated Lists**: Split active and ignored suggestions into separate pages
  - Active suggestions page shows only pending and added items
  - Ignored suggestions page shows only temporary and permanent ignored items
  - Clear navigation buttons to switch between lists
  - Reduces confusion and improves focus on actionable items

- ✅ **Fixed Method Not Allowed Error**: 
  - Removed `create-expense` from POST-only restriction
  - Action now accepts both GET (show form) and POST (submit form)
  - Users can now click "Add Expense" button without errors

- ✅ **Fixed File Upload**:
  - Added `enctype='multipart/form-data'` to form
  - Implemented file upload handling with `UploadedFile::getInstance()`
  - Added `uploadReceipt()` call to save receipt files
  - Receipt uploads now work correctly when creating expenses from suggestions

- ✅ **Fixed FinancialTransaction Creation**:
  - Changed `reference_id` to correct property name `related_expense_id`
  - Added missing properties: `reference_number`, `payment_method`, `status`
  - Added database transaction wrapper for data integrity
  - Added auto-reset of permanently ignored suggestions
  - Financial transactions now created correctly matching ExpenseController behavior

---

**Status**: ✅ Production Ready  
**Version**: 1.0  
**Last Updated**: November 8, 2025

For issues or questions, check application logs or run the test script.

