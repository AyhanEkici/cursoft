# 🧪 Test Locally First - Step by Step Guide

## Why Test Locally First?

✅ **Faster feedback** - See errors immediately  
✅ **No deployment wait** - Test instantly  
✅ **Catch issues early** - Fix before pushing  
✅ **Save time** - One big push instead of many small ones  

## Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** ✅
3. Start **MySQL** ✅

## Step 2: Setup Database (If Not Done)

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create database `cursoft` (if not exists)
3. Run `database/schema.sql` in SQL tab
4. Verify tables exist: `users`, `user_preferences`, etc.

## Step 3: Test Health Check

Open in browser:
```
http://localhost/cursoft/public/health.php
```

**Expected**: JSON response with `{"status":"healthy",...}`

**If Error**: Check file exists and paths are correct

## Step 4: Test Login Page

Open in browser:
```
http://localhost/cursoft/pages/login.php
```

**Expected**: 
- Login form displays
- No PHP errors in browser console (F12)
- No "headers already sent" warnings

**If Error**: Check for syntax errors, missing includes

## Step 5: Test Signup Page

Open in browser:
```
http://localhost/cursoft/pages/signup.php
```

**Expected**:
- Signup form displays
- No PHP errors
- Can submit form

**If Error**: Check database connection, table exists

## Step 6: Test Create Account

1. Fill signup form:
   - Name: Test User
   - Email: test@local.com
   - Password: test123456
   - Confirm: test123456

2. Click "Create Account"

**Expected**:
- Redirects to login page
- No database errors
- User created in database

**If Error**: Check database, table structure

## Step 7: Test Login

1. Use credentials from Step 6
2. Login

**Expected**:
- Redirects to dashboard
- Session created
- No errors

## Step 8: Check for Errors

Open browser console (F12) and check:
- ❌ No PHP warnings
- ❌ No "headers already sent" errors
- ❌ No database errors
- ❌ No 404 errors for assets

## Step 9: Fix Any Issues

If you find errors:
1. Fix them locally
2. Test again
3. Repeat until everything works

## Step 10: Ready to Push?

When everything works locally:

✅ All pages load without errors  
✅ Can create accounts  
✅ Can login  
✅ No PHP warnings  
✅ Database works  

**Then**: Push to GitHub Desktop!

---

## 🚀 After Local Testing

1. **Open GitHub Desktop**
2. **Review all changes**
3. **Write commit message**: "Complete local testing - all features working"
4. **Push to GitHub**
5. **Render will auto-deploy**
6. **Run PostgreSQL schema on Render** (different from MySQL)

---

**Let's test locally first, then push everything together!**

