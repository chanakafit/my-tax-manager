# Documentation Index - Setup Script Fix

This index helps you navigate the documentation related to the ext-zip setup script fix.

---

## 🎯 Quick Navigation

### For Users

- **Just want to get started?** → [QUICK_START.md](QUICK_START.md)
- **Need main documentation?** → [README.md](README.md)
- **Running into issues?** → [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

### For Developers

- **What was the problem?** → [SETUP_SCRIPT_FIX.md](SETUP_SCRIPT_FIX.md)
- **Quick summary?** → [SETUP_SCRIPT_ZIP_FIX_SUMMARY.md](SETUP_SCRIPT_ZIP_FIX_SUMMARY.md)
- **How to test the fix?** → [TESTING_SETUP_SCRIPT_FIX.md](TESTING_SETUP_SCRIPT_FIX.md)
- **ZipArchive related?** → [ZIPARCHIVE_FIX.md](ZIPARCHIVE_FIX.md)

---

## 📚 Document Descriptions

### Setup & Installation

| Document | Purpose | Audience |
|----------|---------|----------|
| **README.md** | Complete project documentation | Everyone |
| **QUICK_START.md** | Fast reference guide | New users |
| **SETUP_COMPLETE.md** | Initial setup status | Reference |

### Fixes & Issues

| Document | Purpose | Audience |
|----------|---------|----------|
| **SETUP_SCRIPT_FIX.md** | ext-zip fix documentation | Developers |
| **DATABASE_CREATION_FIX.md** | Database creation & wait fix | Developers |
| **ENV_VARIABLES_FIX.md** | Environment variables loading fix | Developers |
| **MIGRATION_TIMING_FIX.md** | Migration execution timing fix | Developers |
| **SETUP_SCRIPT_ZIP_FIX_SUMMARY.md** | Quick summary | Quick reference |
| **ZIPARCHIVE_FIX.md** | ZipArchive class fix | Developers |
| **TROUBLESHOOTING.md** | Common problems | Users |

### Testing

| Document | Purpose | Audience |
|----------|---------|----------|
| **TESTING_SETUP_SCRIPT_FIX.md** | Test procedures | QA/Developers |
| **TESTING_CHECKLIST.md** | Feature testing | QA |

### Features & Updates

| Document | Purpose | Audience |
|----------|---------|----------|
| **TAX_RETURN_FEATURE.md** | Tax return feature | Developers |
| **BANK_STATEMENT_UPLOAD_FEATURE.md** | Bank statements | Developers |
| **EXPENSE_HEALTH_CHECK_GUIDE.md** | Health checks | Admins |

### Security

| Document | Purpose | Audience |
|----------|---------|----------|
| **SETUP_SECURITY.md** | Security setup | Admins |
| **SECURITY_SENSITIVE_DATA.md** | Data handling | Developers |

---

## 🔍 Find by Topic

### Docker & Setup

- [README.md](README.md) - Prerequisites, setup commands
- [QUICK_START.md](QUICK_START.md) - Quick commands
- [SETUP_SCRIPT_FIX.md](SETUP_SCRIPT_FIX.md) - Setup script fix
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Docker issues

### PHP Extensions

- [SETUP_SCRIPT_FIX.md](SETUP_SCRIPT_FIX.md) - ext-zip fix
- [ZIPARCHIVE_FIX.md](ZIPARCHIVE_FIX.md) - ZipArchive usage
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Extension errors

### Database

- [README.md](README.md) - Database configuration
- [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - DB connection issues
- [QUICK_START.md](QUICK_START.md) - Migration commands

### Features

- [TAX_RETURN_FEATURE.md](TAX_RETURN_FEATURE.md) - Tax returns
- [BANK_STATEMENT_UPLOAD_FEATURE.md](BANK_STATEMENT_UPLOAD_FEATURE.md) - Bank statements
- [EXPENSE_HEALTH_CHECK_GUIDE.md](EXPENSE_HEALTH_CHECK_GUIDE.md) - Expense tracking

### Testing

- [TESTING_SETUP_SCRIPT_FIX.md](TESTING_SETUP_SCRIPT_FIX.md) - Setup testing
- [TESTING_CHECKLIST.md](TESTING_CHECKLIST.md) - Feature testing

---

## 📖 Reading Order

### For New Users

1. [README.md](README.md) - Understand the project
2. [QUICK_START.md](QUICK_START.md) - Get started quickly
3. [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - If issues arise

### For New Developers

1. [README.md](README.md) - Project overview
2. [SETUP_SCRIPT_FIX.md](SETUP_SCRIPT_FIX.md) - Recent fixes
3. [TESTING_SETUP_SCRIPT_FIX.md](TESTING_SETUP_SCRIPT_FIX.md) - Test setup
4. Feature docs as needed

### For Troubleshooting

1. [TROUBLESHOOTING.md](TROUBLESHOOTING.md) - Common issues
2. [SETUP_SCRIPT_FIX.md](SETUP_SCRIPT_FIX.md) - Setup problems
3. [QUICK_START.md](QUICK_START.md) - Quick fixes

---

## 🎯 By Use Case

### "I want to install the application"

1. Read: [README.md](README.md) → Prerequisites section
2. Run: `./setup_linux_local.sh`
3. If issues: [TROUBLESHOOTING.md](TROUBLESHOOTING.md)

### "I'm getting ext-zip errors"

1. Read: [SETUP_SCRIPT_FIX.md](SETUP_SCRIPT_FIX.md)
2. Solution: Already fixed in Dockerfile
3. Action: Rebuild with `./setup_linux_local.sh`

### "I want to test the application"

1. Read: [TESTING_SETUP_SCRIPT_FIX.md](TESTING_SETUP_SCRIPT_FIX.md) - Setup tests
2. Read: [TESTING_CHECKLIST.md](TESTING_CHECKLIST.md) - Feature tests

### "I need to understand a feature"

- Tax Returns: [TAX_RETURN_FEATURE.md](TAX_RETURN_FEATURE.md)
- Bank Statements: [BANK_STATEMENT_UPLOAD_FEATURE.md](BANK_STATEMENT_UPLOAD_FEATURE.md)
- Expenses: [EXPENSE_HEALTH_CHECK_GUIDE.md](EXPENSE_HEALTH_CHECK_GUIDE.md)

### "I'm deploying to production"

1. Read: [README.md](README.md) → Production Deployment section
2. Read: [SETUP_SECURITY.md](SETUP_SECURITY.md)
3. Read: [SECURITY_SENSITIVE_DATA.md](SECURITY_SENSITIVE_DATA.md)

---

## 📝 Document Types Legend

- 📘 **Guide** - Step-by-step instructions
- 📗 **Reference** - Quick lookup information
- 📕 **Fix** - Problem resolution documentation
- 📙 **Feature** - Feature documentation
- 📔 **Summary** - Overview or recap

---

## 🔗 Related Files

### Configuration Files

```
local/
  .env.example              - Environment variables template
  docker-compose.yml        - Docker services configuration
  php/
    php-fpm/Dockerfile      - PHP container (ext-zip fix here) ⭐
    nginx/Dockerfile        - Nginx container

php/
  config/
    db-local.php           - Database config (auto-generated)
    mail-local.php         - Mail config (auto-generated)
```

### Scripts

```
setup_linux_local.sh       - Main setup script (use this)
php/post_install.sh        - Post-installation tasks
```

---

## 🆕 Latest Updates

**November 11, 2025**
- ✅ Fixed ext-zip missing error in Dockerfile
- ✅ Created SETUP_SCRIPT_FIX.md
- ✅ Created SETUP_SCRIPT_ZIP_FIX_SUMMARY.md
- ✅ Created TESTING_SETUP_SCRIPT_FIX.md
- ✅ Updated README.md with ext-zip troubleshooting
- ✅ Updated QUICK_START.md with ext-zip fix
- ✅ Updated ZIPARCHIVE_FIX.md with permanent fix status

---

## 📞 Getting Help

1. **Check documentation** in this index
2. **Search for error message** in TROUBLESHOOTING.md
3. **Check logs** using commands in QUICK_START.md
4. **Review related fix documents** listed above

---

## ✅ Checklist for New Setup

- [ ] Read README.md prerequisites
- [ ] Run `./setup_linux_local.sh`
- [ ] Verify with commands in TESTING_SETUP_SCRIPT_FIX.md
- [ ] Access http://localhost
- [ ] Login with default credentials
- [ ] Change admin password
- [ ] Test key features

---

**Index Version:** 1.0  
**Last Updated:** November 11, 2025  
**Status:** Current

