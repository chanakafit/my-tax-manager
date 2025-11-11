# Quick Start Guide - Tax Return Submission Feature

## ✅ Feature is Live and Ready!

Your Tax Return Submission feature is now live at **http://localhost**

---

## 🚀 Quick Start (5 Minutes)

### Step 1: Access the Feature
```
1. Open browser: http://localhost
2. Login with your credentials
3. Go to: Site → Tax Years (or navigate to /tax-year/view?year=2023)
4. Click the green "Tax Return Submission" button
```

### Step 2: Add Sample Data (First Time)

#### A. Add a Personal Asset
```
1. Click "Manage Assets"
2. Click "Create Capital Asset"
3. Fill in:
   - Asset Name: "My House"
   - Asset Type: Personal
   - Asset Category: Immovable Property
   - Purchase Date: 2020-01-15
   - Purchase Cost: 5000000
   - Initial Tax Year: 2020
4. Save
```

#### B. Add a Business Asset
```
1. Click "Create Capital Asset"
2. Fill in:
   - Asset Name: "Office Computer"
   - Asset Type: Business
   - Asset Category: Movable Property
   - Purchase Date: 2023-06-10
   - Purchase Cost: 150000
   - Initial Tax Year: 2023
3. Save
```

#### C. Add a Personal Liability
```
1. Go back to Tax Return page
2. Click "Manage Liabilities"
3. Click "Create Liability"
4. Fill in:
   - Lender Name: "Commercial Bank"
   - Type: Personal
   - Category: Loan
   - Original Amount: 2000000
   - Start Date: 2022-05-01
   - Interest Rate: 12.5
   - Status: Active
5. Save
```

#### D. Mark a Bank Account
```
1. Go back to Tax Return page
2. Click "Manage Bank Accounts"
3. Click on an existing account or create new
4. Edit and set:
   - Account Holder Type: Personal (or Business)
5. Save
```

### Step 3: Enter Year-End Balances
```
1. Return to Tax Return main page
2. Click "Manage Year-End Balances"
3. Enter balances for March 31, 2024:
   - Bank balances: Enter amounts in Balance and Balance (LKR) columns
   - Liability balances: Enter outstanding amounts
4. Click "Save Balances"
```

### Step 4: View & Export Report
```
1. Click "View Report" (or you'll be redirected after saving)
2. Review all sections
3. Click "Export Excel" to download
```

---

## 📋 Main URLs

| Action | URL |
|--------|-----|
| **Main Dashboard** | http://localhost |
| **Tax Return Home** | http://localhost/tax-return/index?year=2023 |
| **Manage Balances** | http://localhost/tax-return/manage-balances?year=2023 |
| **View Report** | http://localhost/tax-return/view-report?year=2023 |
| **Liabilities** | http://localhost/liability/index |
| **Assets** | http://localhost/capital-asset/index |
| **Bank Accounts** | http://localhost/bank-account/index |

---

## 💡 Pro Tips

### For Current Tax Year 2023-2024:
```
- Start Date: April 1, 2023
- End Date: March 31, 2024
- Use year=2023 in URLs
```

### Asset Categories:
```
Personal Immovable: Land, House, Building
Personal Movable: Vehicle, Jewelry, Electronics
Business Immovable: Office, Warehouse, Shop
Business Movable: Computer, Machinery, Furniture
```

### Liability Types:
```
Loan: Bank loan, Personal loan, Housing loan
Leasing: Vehicle leasing, Equipment leasing
```

---

## 🔍 What You'll See in the Report

The report automatically organizes data into:

1. **Personal Immovable Properties**
   - Owned before Apr 1, 2023
   - Purchased between Apr 1, 2023 - Mar 31, 2024

2. **Personal Movable Properties**
   - Owned before tax year
   - Purchased during tax year

3. **Business Properties** (Same structure)

4. **Disposed Assets** (Any sold during the year)

5. **Bank Balances** (As of Mar 31)
   - Personal accounts
   - Business accounts
   - Totals

6. **Personal Liabilities**
   - Started before tax year
   - Started during tax year
   - Outstanding balances

7. **Business Liabilities** (Same structure)

---

## ✅ Verification Checklist

After setup, verify:
- [ ] Can create personal asset
- [ ] Can create business asset  
- [ ] Can create liability
- [ ] Can mark bank account as personal/business
- [ ] Can enter year-end balances
- [ ] Can view report
- [ ] Can export to Excel
- [ ] Excel file downloads successfully
- [ ] Report shows correct data

---

## 📱 Screenshots Locations

When viewing the report:
- Clean, printable format
- All sections clearly labeled
- Personal/Business badges in color
- Totals calculated automatically
- Export button at top right

---

## 🆘 Troubleshooting

**Can't see Tax Return button?**
- Make sure you're on a Tax Year view page
- URL should be: /tax-year/view?year=2023

**No data showing in report?**
- Add at least one asset or liability first
- Enter year-end balances
- Make sure you're viewing the correct tax year

**Excel export not working?**
- PhpSpreadsheet library installed ✅
- Click "Export Excel" button
- File will download automatically

**Need to edit data?**
- Assets: /capital-asset/index
- Liabilities: /liability/index  
- Bank Accounts: /bank-account/index
- Year-end balances: Manage Balances button

---

## 📞 Need Help?

1. Check `/IMPLEMENTATION_SUMMARY.md` for full documentation
2. Check `/TAX_RETURN_FEATURE.md` for technical details
3. All forms have helpful labels and hints
4. Error messages will guide you

---

## 🎉 You're All Set!

The feature is complete and ready to use. Navigate to:

**http://localhost/tax-return/index?year=2023**

And start preparing your tax return submission!

---

**Last Updated:** November 9, 2025
**Status:** ✅ Production Ready

