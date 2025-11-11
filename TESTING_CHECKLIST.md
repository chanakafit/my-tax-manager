# Tax Return Feature - Testing Checklist

## Pre-Testing Setup ✅
- [x] Database migrations applied successfully
- [x] PhpSpreadsheet library installed  
- [x] All models created
- [x] All controllers created
- [x] All views created
- [x] Application running on http://localhost

---

## Testing Sequence

### Phase 1: Basic Navigation (5 min)

#### Test 1.1: Access Tax Return from Tax Year
```
□ Go to http://localhost
□ Navigate to Tax Years
□ Click on any year (e.g., 2023)
□ Verify "Tax Return Submission" button appears (green button)
□ Click the button
□ Should land on /tax-return/index?year=2023
□ Page shows 3 steps clearly
```

#### Test 1.2: Navigation Links Work
```
□ From Tax Return index, click "Manage Assets"
□ Should go to /capital-asset/index
□ Go back to Tax Return index
□ Click "Manage Liabilities"
□ Should go to /liability/index
□ Go back to Tax Return index
□ Click "Manage Bank Accounts"
□ Should go to /bank-account/index
```

---

### Phase 2: Create Personal Assets (10 min)

#### Test 2.1: Personal Immovable Property
```
□ Go to /capital-asset/create
□ Fill in:
   Asset Name: "Residential House - Colombo"
   Asset Type: Personal
   Asset Category: Immovable Property
   Description: "Main residence"
   Purchase Date: 2018-03-15
   Purchase Cost: 8500000
   Initial Tax Year: 2018
□ Click Save
□ Should redirect to asset list
□ Verify asset appears with "Personal" badge
```

#### Test 2.2: Personal Movable Property  
```
□ Create another asset:
   Asset Name: "Toyota Prius Car"
   Asset Type: Personal
   Asset Category: Movable Property
   Purchase Date: 2023-07-20
   Purchase Cost: 4500000
   Initial Tax Year: 2023
□ Save
□ Verify appears in list
```

---

### Phase 3: Create Business Assets (10 min)

#### Test 3.1: Business Immovable Property
```
□ Create asset:
   Asset Name: "Office Space - Nugegoda"
   Asset Type: Business
   Asset Category: Immovable Property
   Purchase Date: 2020-01-10
   Purchase Cost: 3500000
   Initial Tax Year: 2020
□ Save
□ Verify "Business" badge shows
```

#### Test 3.2: Business Movable Property
```
□ Create asset:
   Asset Name: "Dell Laptop"
   Asset Type: Business
   Asset Category: Movable Property
   Purchase Date: 2023-05-15
   Purchase Cost: 185000
   Initial Tax Year: 2023
□ Save
```

---

### Phase 4: Create Liabilities (10 min)

#### Test 4.1: Personal Loan
```
□ Go to /liability/create
□ Fill in:
   Lender Name: "Bank of Ceylon"
   Type: Personal
   Category: Loan
   Description: "Housing loan"
   Original Amount: 5000000
   Start Date: 2018-04-01
   End Date: 2028-03-31
   Interest Rate: 11.5
   Monthly Payment: 55000
   Status: Active
□ Save
□ Verify appears with "Personal" badge
```

#### Test 4.2: Business Leasing
```
□ Create liability:
   Lender Name: "Commercial Bank"
   Type: Business
   Category: Leasing
   Description: "Vehicle leasing"
   Original Amount: 2500000
   Start Date: 2023-08-01
   End Date: 2028-07-31
   Interest Rate: 13.0
   Monthly Payment: 48000
   Status: Active
□ Save
```

---

### Phase 5: Configure Bank Accounts (5 min)

#### Test 5.1: Mark Personal Account
```
□ Go to /bank-account/index
□ Click on an existing account or create new
□ Edit:
   Account Holder Type: Personal
□ Save
□ Verify updated
```

#### Test 5.2: Mark Business Account
```
□ Edit another account:
   Account Holder Type: Business
□ Save
```

---

### Phase 6: Enter Year-End Balances (10 min)

#### Test 6.1: Access Balance Management
```
□ Go to /tax-return/index?year=2023
□ Click "Manage Year-End Balances"
□ Should show /tax-return/manage-balances?year=2023
□ Page shows:
   - Snapshot date field (should default to 2024-03-31)
   - Bank accounts table
   - Liabilities table
```

#### Test 6.2: Enter Bank Balances
```
□ For each bank account, enter:
   Balance: (original currency amount)
   Balance LKR: (LKR equivalent)
   Example:
   - Personal Savings: 250000 / 250000
   - Business Current: 580000 / 580000
```

#### Test 6.3: Enter Liability Balances
```
□ For each liability, enter Outstanding Balance:
   - Personal Loan (BOC): 4200000
   - Business Leasing: 2100000
```

#### Test 6.4: Save Balances
```
□ Add notes (optional): "Year-end balances as of March 31, 2024"
□ Click "Save Balances"
□ Should redirect to view-report
□ Success message should appear
```

---

### Phase 7: View Report (10 min)

#### Test 7.1: Report Display
```
□ Page shows /tax-return/view-report?year=2023
□ Header shows correct year: 2023-2024
□ "Edit Balances" button present
□ "Export Excel" button present
```

#### Test 7.2: Verify Report Sections
```
□ Section 1: Personal Immovable Properties
   □ Shows "Existing" subsection
   □ Shows "Purchased during tax year" subsection
   □ House appears in correct section
   
□ Section 2: Personal Movable Properties
   □ Car appears in correct section
   
□ Section 3: Business Immovable Properties
   □ Office appears in correct section
   
□ Section 4: Business Movable Properties
   □ Laptop appears in correct section
   
□ Section 5: Bank Balances
   □ Shows all accounts
   □ Personal/Business badges show
   □ Totals calculated correctly
   
□ Section 6: Personal Liabilities
   □ BOC loan appears
   □ Outstanding balance shows: 4,200,000.00
   
□ Section 7: Business Liabilities
   □ Commercial Bank leasing appears
   □ Outstanding balance shows: 2,100,000.00
```

#### Test 7.3: Verify Calculations
```
□ Bank balances:
   □ Personal total calculated
   □ Business total calculated
   □ Grand total = Personal + Business
   
□ All currency values formatted with commas
□ No PHP errors on page
```

---

### Phase 8: Excel Export (5 min)

#### Test 8.1: Export Functionality
```
□ Click "Export Excel" button
□ File download should start
□ Filename format: Tax_Return_2023_YYYY-MM-DD.xlsx
□ File size > 10KB (should have data)
```

#### Test 8.2: Excel Content Verification
```
□ Open downloaded Excel file
□ Check spreadsheet has:
   □ Title row: "TAX RETURN SUBMISSION - ASSESSMENT YEAR 2023-2024"
   □ All sections present
   □ Data matches web report
   □ Formatting applied (bold headers, etc.)
   □ Numbers formatted correctly
```

---

### Phase 9: Edge Cases (10 min)

#### Test 9.1: Asset Disposal
```
□ Go to an existing asset
□ Edit:
   Status: Disposed
   Disposal Date: 2023-12-15
   Disposal Value: (some value)
□ Save
□ Go back to tax return report
□ Verify appears in "Assets Disposed During Year" section
□ Profit/Loss calculated correctly
```

#### Test 9.2: Empty Sections
```
□ View report for a year with no data
□ Verify sections with no data don't show or show "No data" message
□ No PHP errors
```

#### Test 9.3: Multiple Tax Years
```
□ Create data for year 2022
□ View 2022 report
□ Verify shows only 2022 data
□ Switch to 2023 report
□ Verify shows only 2023 data
```

---

### Phase 10: Integration with Existing Features (5 min)

#### Test 10.1: Capital Allowance Calculation
```
□ Go to tax year view
□ Verify capital allowances only show for business assets
□ Personal assets should not appear in allowance calculations
```

#### Test 10.2: Navigation Consistency
```
□ Tax Year view has Tax Return button
□ Breadcrumbs work correctly
□ Can navigate back to dashboard
```

---

## Expected Results Summary

✅ **All 10 phases should pass without errors**

### Success Criteria:
- [ ] All navigation links work
- [ ] Can create assets with personal/business types
- [ ] Can create liabilities with personal/business types
- [ ] Can mark bank accounts as personal/business
- [ ] Can enter year-end balances
- [ ] Report displays all data correctly
- [ ] Report sections show appropriate data
- [ ] Excel export works
- [ ] Downloaded Excel has correct data
- [ ] Capital allowances only for business assets
- [ ] No PHP errors anywhere
- [ ] Mobile responsive (bonus)

---

## Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| "Tax Return button not visible" | Clear browser cache, check you're on tax-year/view page |
| "No data in report" | Make sure you've entered year-end balances |
| "Excel download fails" | Check phpoffice/phpspreadsheet is installed |
| "Bank accounts not showing" | Check account is marked as active (is_active=1) |
| "Wrong tax year data" | Verify URL has correct year parameter |

---

## Performance Checklist

- [ ] Report loads in < 3 seconds
- [ ] Excel export completes in < 5 seconds
- [ ] No memory errors with 50+ assets
- [ ] Forms validate properly
- [ ] Database queries optimized

---

## Browser Testing

Test in:
- [ ] Chrome/Edge
- [ ] Firefox
- [ ] Safari
- [ ] Mobile browser

---

**Testing Estimated Time:** 90 minutes
**Last Updated:** November 9, 2025
**Status:** Ready for Testing

---

## Quick Test (15 min version)

If time is limited, test this minimum:

1. ✅ Access tax return from tax year page
2. ✅ Create 1 personal asset
3. ✅ Create 1 business asset
4. ✅ Create 1 liability
5. ✅ Mark 1 bank account as personal
6. ✅ Enter year-end balances
7. ✅ View report
8. ✅ Export to Excel
9. ✅ Verify Excel content

Done! Feature working ✅

