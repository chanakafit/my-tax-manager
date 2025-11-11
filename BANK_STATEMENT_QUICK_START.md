# Bank Statement Upload - Quick Start Guide

## ✅ Feature Complete!

You can now upload bank statement documents and download them with the Excel report in a ZIP file.

---

## How to Use

### 1. Upload Bank Statements

**Navigate to:** `/tax-return/manage-balances?year=2024`

**Steps:**
1. Enter bank balance amounts for each account
2. Click "Choose File" in the "Bank Statement" column
3. Select your PDF, JPG, or PNG file (max 10MB)
4. Click "Save Balances"
5. ✅ Files uploaded to server!

**Tip:** You can see existing statements with the "View" button

---

### 2. Download Complete Package

**Navigate to:** `/tax-return/view-report?year=2024`

**Steps:**
1. Click "Download ZIP (Excel + Bank Statements)"
2. ZIP file downloads containing:
   - Excel report with tax return data
   - All uploaded bank statements in Bank_Statements folder
3. ✅ Ready to submit!

---

## What's Included in ZIP

```
Tax_Return_2024_2025-11-11.zip
├── Tax_Return_2024_2025-11-11.xlsx    ← Main report
└── Bank_Statements/
    ├── Nations_Trust_Bank_270400001394.pdf
    ├── Commercial_Bank_123456789.pdf
    └── Seylan_Bank_987654321.pdf
```

---

## File Requirements

✅ **Formats:** PDF, PNG, JPG, JPEG  
✅ **Max Size:** 10 MB per file  
✅ **Recommended:** PDF format for best quality  

---

## Key Features

✅ Upload supporting documents for each bank account  
✅ View uploaded statements anytime  
✅ Replace statements by uploading new files  
✅ Download everything in one ZIP  
✅ Files named clearly: `{BankName}_{AccountNumber}.pdf`  
✅ Ready for tax department submission  

---

## Quick Test

1. Go to `/tax-return/manage-balances?year=2024`
2. Upload a sample PDF for one bank account
3. Click "Save Balances"
4. Go to `/tax-return/view-report?year=2024`
5. Click "Download ZIP"
6. Extract and verify both Excel and PDF are there!

---

## Benefits

🎯 **Organized** - Everything in one package  
🎯 **Professional** - Clean file structure  
🎯 **Complete** - Excel + Supporting documents  
🎯 **Convenient** - One download, ready to submit  
🎯 **Documented** - Bank statements linked to balances  

---

**Status:** ✅ Ready to Use!  
**Documentation:** See BANK_STATEMENT_UPLOAD_FEATURE.md for full details

